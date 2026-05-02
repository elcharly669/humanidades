# Guía de mantenimiento
## Para el responsable técnico del directorio de personas

**Audiencia:** La persona del departamento más cómoda con tecnología; no requiere ser desarrolladora.
**Nivel requerido:** Saber navegar paneles de administración web, leer instrucciones en inglés básico.

---

## Checklist trimestral

Realiza estas verificaciones **una vez cada tres meses** (enero, abril, julio, octubre).

### WordPress

- [ ] **Actualizaciones pendientes** — Entra a WP Admin → Panel → Actualizaciones. Aplica actualizaciones de WordPress core y plugins. Hazlo en ese orden: primero core, luego plugins.
  - Antes de actualizar, verifica que hay un respaldo reciente (ver sección UpdraftPlus abajo).
- [ ] **Auditoría de usuarios** — WP Admin → Usuarios. Elimina cuentas de personas que ya no trabajan en la facultad. Nadie debe tener rol "Administrador" salvo el responsable técnico y el equipo de sistemas.
- [ ] **WPScan** (opcional, requiere terminal) — Ejecuta `wpscan --url https://cms.humanidades.example.edu --enumerate vp,vt` para buscar plugins vulnerables. Requiere cuenta gratuita en wpscan.com.

### Respaldos (UpdraftPlus)

- [ ] WP Admin → UpdraftPlus → Copias de seguridad existentes. Verifica que la copia más reciente tiene menos de 7 días.
- [ ] Abre Google Drive y confirma que la carpeta `UpdraftPlus / humanidades` tiene archivos con fecha reciente.
- [ ] Si no hay respaldos recientes, haz clic en **"Respaldar ahora"** y espera que el proceso termine.

### Cloudflare

- [ ] Cloudflare Dashboard → SSL/TLS → Verifica que el certificado SSL tiene más de 30 días de vigencia (Cloudflare renueva automáticamente, pero vale la pena verificar).
- [ ] Cloudflare Dashboard → Security → Analiza el panel de amenazas. Si hay un pico inusual de tráfico bloqueado, reporta al equipo de sistemas.

### Sitio Astro (Cloudflare Pages)

- [ ] Abre el sitio en el navegador y verifica que carga correctamente.
- [ ] Cloudflare Pages Dashboard → Deployments → Verifica que el último deploy fue exitoso (estado verde "Success").

---

## Cómo verificar que el sitio Astro se actualiza correctamente

El sitio se reconstruye automáticamente cada vez que se hace un cambio en el código (en GitHub). Pero los cambios de contenido en WordPress **no** disparan una reconstrucción automáticamente (a menos que se configure un webhook).

**Para solicitar una reconstrucción manual:**

1. Abre Cloudflare Pages Dashboard → tu proyecto → pestaña **"Deployments"**.
2. Haz clic en **"Retry deployment"** en el último deploy.
3. Espera ~2 minutos. El estado cambiará a "Success".
4. Recarga el sitio para verificar los cambios.

**Si tienes acceso al Build Hook:**
```bash
curl -X POST "https://api.cloudflare.com/client/v4/pages/webhooks/deploy_hooks/TU_HOOK_ID"
```
El Hook URL está en el administrador de contraseñas del equipo bajo `CF_BUILD_HOOK_URL`.

---

## Qué NO tocar

Estas configuraciones están optimizadas y documentadas. Cambiarlas sin consultar al
equipo técnico puede romper el sitio:

| Lo que no debes cambiar | Por qué |
|-------------------------|---------|
| La estructura de permalinks en WP (Ajustes → Enlaces permanentes) | Las URLs del directorio Astro dependen exactamente de los slugs actuales. Cambiarlas rompe todos los enlaces existentes y el SEO. |
| Instalar plugins sin revisar la lista aprobada | Los plugins son la principal fuente de vulnerabilidades en WordPress. Solo instala plugins con +100,000 instalaciones activas y actualizaciones recientes. |
| Editar `wp-config.php` directamente | Contiene configuración de seguridad crítica. Cualquier error rompe todo el sitio. Consulta al equipo técnico. |
| Cambiar el nombre de usuario "admin" (si existe) sin seguir el procedimiento | Si ya tienes configuradas reglas de Cloudflare basadas en ese usuario, cambiarlos puede romper la protección. Documenta el cambio. |
| Activar o desactivar el plugin "Humanities People CPT" | Desactivarlo elimina el tipo de publicación del directorio; el sitio quedaría vacío en el próximo rebuild. |

---

## Contactos de emergencia y páginas de estado

| Servicio | URL de estado | Qué hacer si está caído |
|----------|--------------|------------------------|
| Cloudflare | https://www.cloudflarestatus.com | Esperar. Los problemas de Cloudflare se resuelven solos. |
| WordPress host (InfinityFree) | https://forum.infinityfree.net/c/announcements | Contactar soporte del host. |
| GitHub | https://www.githubstatus.com | Si GitHub está caído, los deploys automáticos se detienen. Hacer deploy manual. |

**Contacto técnico interno:** [nombre] · [correo] · [teléfono / extensión]
**Contacto técnico externo (agencia / freelancer):** [nombre] · [correo]

---

## Glosario rápido

| Término | Qué significa en términos simples |
|---------|----------------------------------|
| WordPress (WP) | El sistema donde se agregan y editan los datos de las personas. Como el "archivo" del directorio. |
| Astro | El programa que convierte esos datos en el sitio web visible. Se ejecuta automáticamente. |
| Cloudflare | El servicio que protege el sitio de ataques y lo hace cargar rápido. |
| Deploy | El proceso de publicar una nueva versión del sitio. Tarda ~2 minutos. |
| Plugin | Un módulo extra instalado en WordPress. Mantenerlos actualizados es crítico. |
| SSL | El candado verde que aparece en el navegador. Indica que la conexión es segura. |
| Respaldo / Backup | Una copia de toda la información, para poder restaurarla si algo sale mal. |
