<?php
/**
 * wp-config-security.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Complementos de seguridad para wp-config.php.
 *
 * CÓMO USAR:
 *   Copia estas constantes en tu wp-config.php, ARRIBA de la línea:
 *   "/* ¡Eso es todo, deja de editar! *\/"
 *
 *   NO incluyas wp-config.php en el control de versiones.
 *   Este archivo (wp-config-security.php) se versiona solo como documentación.
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ── Debug ─────────────────────────────────────────────────────────────────────

// POR QUÉ: Los rastros de pila y rutas de archivos en la salida de errores dan a los atacantes
//           un mapa de tu instalación. El debug siempre debe estar desactivado en producción.
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);   // Nunca registrar en /wp-content/debug.log en producción
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', false);   // Usar JS/CSS minificado en producción

// ── Seguridad del sistema de archivos ────────────────────────────────────────

// POR QUÉ: Si un atacante obtiene acceso de administrador, el Editor de Temas/Plugins
//           le permite ejecutar PHP arbitrario. Desactivarlo elimina este vector de ataque.
define('DISALLOW_FILE_EDIT', true);

// POR QUÉ: Evita que el mecanismo de actualización automática escriba archivos.
//           Las actualizaciones deben realizarse mediante un proceso controlado
//           (ver MAINTENANCE-GUIDE.md), no silenciosamente en segundo plano.
define('DISALLOW_FILE_MODS', false); // Cambiar a true solo si las actualizaciones se gestionan externamente
// define('AUTOMATIC_UPDATER_DISABLED', true); // Descomentar para deshabilitar actualizaciones automáticas

// ── Aplicar SSL ───────────────────────────────────────────────────────────────

// POR QUÉ: Obliga a la cookie de sesión de administrador a transmitirse solo por HTTPS,
//           evitando el robo de sesión por HTTP.
define('FORCE_SSL_ADMIN', true);

// ── Límites de memoria ────────────────────────────────────────────────────────

// POR QUÉ: Limita el consumo de recursos por solicitud; reduce el impacto de un DoS o
//           código descontrolado de un plugin. Aumentar si operaciones legítimas alcanzan el límite.
define('WP_MEMORY_LIMIT', '128M');
define('WP_MAX_MEMORY_LIMIT', '256M'); // Solo para operaciones de administrador

// ── Claves y salts de autenticación ──────────────────────────────────────────

// POR QUÉ: Los salts hacen que el forzado bruto de cookies de sesión robadas sea
//           computacionalmente inviable. Generar valores nuevos en: https://api.wordpress.org/secret-key/1.1/salt/
// !! REEMPLAZAR estos valores de marcador antes de ir a producción !!
define('AUTH_KEY',         'REEMPLAZAR_CON_CADENA_ALEATORIA_UNICA');
define('SECURE_AUTH_KEY',  'REEMPLAZAR_CON_CADENA_ALEATORIA_UNICA');
define('LOGGED_IN_KEY',    'REEMPLAZAR_CON_CADENA_ALEATORIA_UNICA');
define('NONCE_KEY',        'REEMPLAZAR_CON_CADENA_ALEATORIA_UNICA');
define('AUTH_SALT',        'REEMPLAZAR_CON_CADENA_ALEATORIA_UNICA');
define('SECURE_AUTH_SALT', 'REEMPLAZAR_CON_CADENA_ALEATORIA_UNICA');
define('LOGGED_IN_SALT',   'REEMPLAZAR_CON_CADENA_ALEATORIA_UNICA');
define('NONCE_SALT',       'REEMPLAZAR_CON_CADENA_ALEATORIA_UNICA');

// ── Prefijo de tablas de base de datos ───────────────────────────────────────

// POR QUÉ: El prefijo por defecto 'wp_' es conocido por todas las herramientas
//           automatizadas de inyección SQL. Cambiarlo no detiene a un atacante hábil,
//           pero elimina los ataques automáticos de script-kiddies.
//           Configurar ANTES de ejecutar el instalador de WP; difícil de cambiar después.
// $table_prefix = 'hum4_'; // Ejemplo — definir en tiempo de instalación

// ── Exposición de la versión ──────────────────────────────────────────────────
// Se maneja en el plugin (llamadas a remove_action). Documentado aquí como referencia.
// POR QUÉ: Reduce la superficie de fingerprinting. Los atacantes usan los números de versión
//           para apuntar a CVEs conocidos. La versión sigue siendo pública en wordpress.org
//           pero difundirla en encabezados y HTML no tiene ningún beneficio legítimo.
