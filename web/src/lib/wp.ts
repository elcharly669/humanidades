/**
 * lib/wp.ts
 * ─────────────────────────────────────────────────────────────────────────────
 * Cliente de la API REST de WordPress para el tipo de publicación "Persona".
 *
 * Uso:
 *   import { getAllPersonas, getPersonaBySlug } from '../lib/wp';
 *
 * Variables de entorno:
 *   WP_API_BASE_URL  — URL base completa del sitio WordPress, sin barra final
 *                      Ejemplo: https://cms.humanidades.example.edu
 *
 * Notas de diseño:
 *   - Todas las solicitudes ocurren en TIEMPO DE CONSTRUCCIÓN (modo estático de Astro).
 *     WordPress nunca es contactado por usuarios finales; un CMS lento o caído
 *     solo afecta las compilaciones, no la disponibilidad del sitio.
 *   - La paginación se maneja automáticamente: se itera hasta que `x-wp-totalpages`
 *     se agota, garantizando que siempre se obtiene el conjunto completo de datos.
 *   - `export const prerender = true` en los archivos de página garantiza salida estática.
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ── URL base ──────────────────────────────────────────────────────────────────

const BASE_URL = (import.meta.env.WP_API_BASE_URL ?? '').replace(/\/$/, '');

if (!BASE_URL) {
  // Advertir en tiempo de compilación para que el desarrollador lo note de inmediato,
  // pero sin interrumpir el proceso para que el entorno local pueda arrancar sin WP real.
  console.warn(
    '[wp.ts] WP_API_BASE_URL no está configurada. ' +
      'Copia web/.env.example a web/.env y define la variable.',
  );
}

// ── Tipos TypeScript ──────────────────────────────────────────────────────────

/** Una entrada del campo repetidor de publicaciones (ACF) */
export interface Publication {
  titulo: string;
  autores?: string;
  anio?: number;
  url?: string;
}

/**
 * Persona — refleja el grupo de campos ACF asociado al CPT `persona`.
 * Todos los campos son opcionales para que registros incompletos no
 * interrumpan la compilación; los componentes deben manejar valores faltantes.
 */
export interface Person {
  id: number;
  slug: string;
  /** Título del post = nombre completo */
  name: string;
  /** ACF: cargo / título */
  titulo?: string;
  /** ACF: departamento */
  departamento?: string;
  /** ACF: areas_investigacion — separadas por coma o array, normalizado abajo */
  areasInvestigacion: string[];
  /** ACF: email */
  email?: string;
  /** ACF: biografia (HTML de WP) */
  bio?: string;
  /** URL de la imagen destacada de WP — tamaño completo, servida por CDN */
  photoUrl?: string;
  /** ACF: repetidor de publicaciones */
  publicaciones: Publication[];
}

// ── Estructura raw de la API REST de WP ──────────────────────────────────────

/** Forma devuelta por /wp-json/wp/v2/persona?_embed */
interface WpPersonaRaw {
  id: number;
  slug: string;
  title: { rendered: string };
  acf: {
    titulo?: string;
    departamento?: string;
    areas_investigacion?: string | string[];
    email?: string;
    biografia?: string;
    publicaciones?: Array<{
      titulo_publicacion: string;
      autores?: string;
      anio?: number;
      url?: string;
    }>;
  };
  _embedded?: {
    'wp:featuredmedia'?: Array<{ source_url: string }>;
  };
}

// ── Función de normalización ──────────────────────────────────────────────────

function normalisePersona(raw: WpPersonaRaw): Person {
  const acf = raw.acf ?? {};

  // areas_investigacion puede llegar como string (separado por comas) o ya como array
  const areasRaw = acf.areas_investigacion ?? [];
  const areasInvestigacion =
    typeof areasRaw === 'string'
      ? areasRaw.split(',').map((s) => s.trim()).filter(Boolean)
      : (areasRaw as string[]);

  const publicaciones: Publication[] = (acf.publicaciones ?? []).map((p) => ({
    titulo: p.titulo_publicacion,
    autores: p.autores,
    anio: p.anio,
    url: p.url,
  }));

  const photoUrl =
    raw._embedded?.['wp:featuredmedia']?.[0]?.source_url ?? undefined;

  return {
    id: raw.id,
    slug: raw.slug,
    name: raw.title.rendered,
    titulo: acf.titulo,
    departamento: acf.departamento,
    areasInvestigacion,
    email: acf.email,
    bio: acf.biografia,
    photoUrl,
    publicaciones,
  };
}

// ── Funciones auxiliares de solicitud ────────────────────────────────────────

/**
 * Obtiene una página de personas desde la API REST de WP.
 * Devuelve el array JSON y el encabezado de páginas totales.
 */
async function fetchPersonasPage(
  page: number,
  perPage = 100,
): Promise<{ data: WpPersonaRaw[]; totalPages: number }> {
  const url = new URL(`${BASE_URL}/wp-json/wp/v2/persona`);
  url.searchParams.set('per_page', String(perPage));
  url.searchParams.set('page', String(page));
  url.searchParams.set('_embed', '1'); // incluye imagen destacada

  const res = await fetch(url.toString(), {
    headers: {
      // Identifica el agente de compilación en los logs de acceso de WP — útil para depuración
      'User-Agent': 'Astro-Build/1.0 (+https://humanidades.example.edu)',
    },
  });

  if (!res.ok) {
    throw new Error(
      `[wp.ts] Error al obtener la página ${page} de personas: ${res.status} ${res.statusText}`,
    );
  }

  const totalPages = Number(res.headers.get('x-wp-totalpages') ?? 1);
  const data: WpPersonaRaw[] = await res.json();
  return { data, totalPages };
}

// ── API pública ───────────────────────────────────────────────────────────────

/**
 * Devuelve TODAS las personas, obteniendo todas las páginas automáticamente.
 * Los resultados se ordenan alfabéticamente por nombre para un renderizado consistente.
 */
export async function getAllPersonas(): Promise<Person[]> {
  if (!BASE_URL) return []; // fallback cuando WP no está configurado

  const firstPage = await fetchPersonasPage(1);
  const results: WpPersonaRaw[] = [...firstPage.data];

  for (let p = 2; p <= firstPage.totalPages; p++) {
    const { data } = await fetchPersonasPage(p);
    results.push(...data);
  }

  return results
    .map(normalisePersona)
    .sort((a, b) => a.name.localeCompare(b.name, 'es'));
}

/**
 * Devuelve una sola persona por slug.
 * Usado en getStaticPaths() — lanza un error si no se encuentra para que la compilación falle visiblemente.
 */
export async function getPersonaBySlug(slug: string): Promise<Person> {
  const all = await getAllPersonas();
  const found = all.find((p) => p.slug === slug);
  if (!found) {
    throw new Error(`[wp.ts] Persona con slug "${slug}" no encontrada en la API de WP`);
  }
  return found;
}
