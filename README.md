# Directorio de Personas — Facultad de Humanidades

**Stack:** WordPress headless (CMS) · Astro (front-end estático) · Cloudflare Pages (hosting) · Cloudflare WAF (seguridad)

> Caso de estudio de portafolio — Universidad · Facultad de Humanidades
> Demuestra arquitectura headless CMS, tipos de publicación personalizados, marcado Schema.org y hardening de seguridad en producción.

---

## Mapa del repositorio

```
humanities-web/
├── web/                   # Front-end Astro (Cloudflare Pages)
│   ├── src/
│   │   ├── components/    # Componentes Astro reutilizables
│   │   ├── layouts/       # Layout base + head
│   │   ├── pages/         # index.astro, personas/[slug].astro
│   │   └── lib/           # Cliente API REST de WP + tipos TypeScript
│   ├── public/            # Recursos estáticos (favicon, og-image)
│   └── astro.config.mjs
├── wordpress/
│   ├── plugin/            # humanities-people-cpt — plugin de tipo de publicación personalizado
│   └── theme-child/       # Tema hijo (solo sobreescrituras de seguridad)
├── cms-export/            # Exportaciones JSON de grupos de campos ACF (versionadas)
├── docs/
│   ├── ARCHITECTURE.md
│   ├── ROADMAP.md
│   ├── EDITOR-GUIDE.md    # Español, audiencia no técnica
│   ├── MAINTENANCE-GUIDE.md
│   ├── CHANGELOG.md
│   └── cloudflare-rules.md
├── AGENTS.md              # Contexto para agentes de IA
├── .editorconfig
├── .gitignore
├── .nvmrc                 # Node 20
└── README.md
```

## Inicio rápido

```bash
# 1. Clonar
git clone https://github.com/<tu-org>/humanities-web.git && cd humanities-web

# 2. Front-end
cd web && cp .env.example .env   # configurar WP_API_BASE_URL
npm install && npm run dev

# 3. WordPress
# Despliega wordpress/plugin/ en tu instalación de WP
# Importa cms-export/acf-person-fields.json vía ACF > Herramientas > Importar
```

## Ambientes

| Ambiente | URL | Propósito |
|----------|-----|-----------|
| Producción | `https://humanidades.example.edu` | Sitio en vivo |
| Staging | `https://staging.humanidades.example.edu` | Revisión previa al despliegue |
| WP Admin | `https://cms.humanidades.example.edu/wp-admin` | Solo CMS |

> La URL del administrador de WordPress no es pública. Consulta `docs/ARCHITECTURE.md` para la regla de acceso de Cloudflare.

## Postura de seguridad

Consulta `docs/ARCHITECTURE.md` para el registro completo de decisiones de seguridad.
Resumen: API REST de WP en solo lectura, inicio de sesión protegido por Cloudflare Access (o lista de IPs permitidas), XML-RPC desactivado, `wp-config.php` movido fuera del webroot.

## Hoja de ruta

Ver [`docs/ROADMAP.md`](docs/ROADMAP.md) para el estado del proyecto y las fases pendientes.

## Edición de contenido

Editores no técnicos: ver `docs/EDITOR-GUIDE.md`
Mantenimiento: ver `docs/MAINTENANCE-GUIDE.md`

## Licencia

MIT (código). Contenido (c) Universidad · Facultad de Humanidades — todos los derechos reservados.
