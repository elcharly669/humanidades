# Hoja de ruta — Directorio de Humanidades

> Proyecto: Directorio headless WordPress + Astro · Cloudflare Pages
> Repositorio: `humanities-web`

---

## ✅ Fase 0 — Scaffold base (completado 2026-05-02)

| Tarea | Archivo(s) |
|-------|-----------|
| Scaffold del repositorio | `.nvmrc`, `.editorconfig`, `.gitignore` |
| README en español | `README.md` |
| Contexto de agentes | `AGENTS.md` |
| Proyecto Astro 4 inicializado | `web/` |
| Configuración de Tailwind + paleta de marca | `web/tailwind.config.mjs` |
| Variables de entorno | `web/.env.example` |
| Layout base (SEO, nav, GTM, skip-nav) | `web/src/layouts/BaseLayout.astro` |
| Componente tarjeta de directorio | `web/src/components/PersonCard.astro` |
| Página de listado con Schema.org ItemList | `web/src/pages/index.astro` |
| Página de perfil con Schema.org Person | `web/src/pages/personas/[slug].astro` |
| Cliente REST API tipado con paginación | `web/src/lib/wp.ts` |
| Plugin WP: CPT + campos ACF + filtro REST | `wordpress/plugin/humanities-people-cpt.php` |
| Hardening wp-config | `wordpress/wp-config-security.php` |
| Reglas base .htaccess | `wordpress/htaccess-security.txt` |
| Documentación completa | `docs/` |
| Comentarios en español en todo el código | Todos los archivos |

---

## 🔲 Fase 1 — Git + entorno de trabajo

> Objetivo: repositorio limpio en GitHub, entorno local funcional.

- [x] `git init` + primer commit con mensaje semántico
- [x] Crear repo en GitHub (público, historial limpio para portafolio)
- [x] Verificar que `npm run dev` arranca sin errores con `WP_API_BASE_URL` vacía
- [ ] Crear `web/public/favicon.svg`
- [ ] Crear `web/public/og-default.png`

---

## 🔲 Fase 2 — WordPress en producción

> Objetivo: CMS operativo con datos reales accesibles vía REST API.

- [ ] Instalar WordPress en InfinityFree / 000webhost
- [ ] Subir y activar `wordpress/plugin/humanities-people-cpt.php`
- [ ] Decidir e instalar: ACF Free + workaround, ACF Pro, o CMB2 (ver decisiones abajo)
- [ ] Crear y exportar `cms-export/acf-person-fields.json`
- [ ] Verificar endpoint: `GET /wp-json/wp/v2/persona` devuelve campos ACF
- [ ] Aplicar snippets de seguridad (`wp-config-security.php`, `htaccess-security.txt`)
- [ ] Configurar UpdraftPlus → Google Drive (respaldo semanal)

---

## 🔲 Fase 3 — Cloudflare + despliegue

> Objetivo: sitio en vivo con CDN y WAF activos.

- [ ] Conectar dominio a Cloudflare (o usar `*.pages.dev`)
- [ ] Crear proyecto en Cloudflare Pages (build: `npm run build`, output: `dist/`)
- [ ] Configurar variables de entorno en CF Pages (`WP_API_BASE_URL`, `SITE_URL`)
- [ ] Implementar las 7 reglas WAF documentadas en `docs/cloudflare-rules.md`
- [ ] Verificar headers de seguridad en [securityheaders.com](https://securityheaders.com)
- [ ] Configurar staging (subdominio + rama `staging` en GitHub)
- [ ] Documentar subdominio de staging en `README.md`

---

## 🔲 Fase 4 — Contenido + calidad

> Objetivo: Lighthouse ≥ 90, datos reales, Schema.org validado.

- [ ] Cargar al menos 5 personas de prueba en WP Admin
- [ ] Verificar build completo con datos reales (`npm run build`)
- [ ] Validar Schema.org en [validator.schema.org](https://validator.schema.org)
- [ ] Lighthouse: Performance, Accessibility, SEO, Best Practices (objetivo ≥ 90)
- [ ] Verificar ofuscación de correo (Cloudflare Email Obfuscation)
- [ ] Configurar GA4 + GTM (reemplazar `GTM-XXXXXXX` en `BaseLayout.astro`)

---

## 🔲 Fase 5 — Portafolio + entrega

> Objetivo: documentación lista para reclutadores y entrega al cliente.

- [ ] Capturas de pantalla para `docs/EDITOR-GUIDE.md` (slots marcados con 📸)
- [ ] Diagrama de arquitectura visual en `docs/ARCHITECTURE.md`
- [ ] Ejecutar WPScan + Sucuri SiteCheck y documentar resultados
- [ ] `docs/CHANGELOG.md` actualizado con todos los hitos
- [ ] Caso de estudio en PDF (para portafolio personal)
- [ ] Métricas reales en `README.md` (Lighthouse, personas cargadas, tiempo de carga)

---

## Decisiones pendientes

| Decisión | Opciones | Afecta |
|----------|---------|--------|
| Campo Repetidor de publicaciones | ACF Pro · CMB2 · JSON en textarea | Fase 2 |
| Dominio | `*.pages.dev` gratuito · dominio propio | Fase 3 |
| Rebuild automático | Manual desde CF Pages · plugin WP de deploy hook | Fase 3 |
| Staging WP | Subdirectorio · subdominio en mismo host | Fase 3 |
