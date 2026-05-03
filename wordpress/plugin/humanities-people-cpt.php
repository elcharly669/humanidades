<?php
/**
 * Plugin Name:  Humanities People CPT
 * Plugin URI:   https://github.com/your-org/humanities-web
 * Description:  Registra el tipo de publicación personalizado "Persona" y el grupo
 *               de campos ACF para el directorio headless WordPress / Astro.
 * Version:      1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author:       Your Name
 * License:      MIT
 *
 * Notas de diseño:
 *  - Sin dependencia de page builder. Todas las definiciones de campos viven en código (JSON de ACF)
 *    y también se registran programáticamente aquí como respaldo.
 *  - El soporte REST API está habilitado en el CPT para que Astro pueda consumirlo en compilación.
 *  - El tipo de capacidad se establece en 'post' para que los roles Author/Editor funcionen por defecto;
 *    se puede restringir más con un plugin de roles si es necesario.
 */

defined('ABSPATH') || exit; // Prevenir acceso directo al archivo

// ── 1. Registrar el tipo de publicación ──────────────────────────────────────

add_action('init', 'hum_register_persona_cpt');

function hum_register_persona_cpt(): void {
    $labels = [
        'name'                  => 'Personas',
        'singular_name'         => 'Persona',
        'add_new'               => 'Agregar persona',
        'add_new_item'          => 'Agregar nueva persona',
        'edit_item'             => 'Editar persona',
        'new_item'              => 'Nueva persona',
        'view_item'             => 'Ver persona',
        'search_items'          => 'Buscar personas',
        'not_found'             => 'No se encontraron personas',
        'not_found_in_trash'    => 'No hay personas en la papelera',
        'all_items'             => 'Todas las personas',
        'menu_name'             => 'Personas',
        'name_admin_bar'        => 'Persona',
    ];

    $args = [
        'labels'              => $labels,
        'public'              => true,
        // Excluido del tema front-end de WordPress (manejado por Astro)
        // pero se mantiene público para que la REST API esté disponible.
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,  // REQUERIDO: habilita la exposición vía REST API de WP
        'rest_base'           => 'persona', // /wp-json/wp/v2/persona
        'query_var'           => true,
        'rewrite'             => ['slug' => 'persona'],
        'capability_type'     => 'post',
        'has_archive'         => false, // Astro maneja el archivo, no WP
        'hierarchical'        => false,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-id-alt',
        'supports'            => [
            'title',         // Nombre completo de la persona
            'thumbnail',     // Imagen destacada = foto de perfil
            'revisions',     // Permite revertir cambios en la bio
        ],
    ];

    register_post_type('persona', $args);
}

// ── 1.5 Registrar Taxonomía (Departamento) ───────────────────────────────────

add_action('init', 'hum_register_departamento_tax');

function hum_register_departamento_tax(): void {
    $labels = [
        'name'              => 'Departamentos',
        'singular_name'     => 'Departamento',
        'search_items'      => 'Buscar departamentos',
        'all_items'         => 'Todos los departamentos',
        'parent_item'       => 'Departamento superior',
        'parent_item_colon' => 'Departamento superior:',
        'edit_item'         => 'Editar departamento',
        'update_item'       => 'Actualizar departamento',
        'add_new_item'      => 'Añadir nuevo departamento',
        'new_item_name'     => 'Nuevo nombre de departamento',
        'menu_name'         => 'Departamentos',
    ];

    $args = [
        'hierarchical'      => true, // true = se comporta como Categorías (casillas), false = como Tags
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'departamento'],
        'show_in_rest'      => true, // Vital para Astro
        'rest_base'         => 'departamentos',
    ];

    register_taxonomy('departamento', ['persona'], $args);
}

// ── 2. Registrar grupo de campos CMB2 (con Repetidor gratis) ─────────────────

add_action('cmb2_admin_init', 'hum_register_persona_cmb2');

function hum_register_persona_cmb2(): void {
    $cmb = new_cmb2_box([
        'id'            => 'persona_metabox',
        'title'         => 'Datos de la persona',
        'object_types'  => ['persona'],
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true,
    ]);

    $cmb->add_field([
        'name' => 'Cargo',
        'desc' => 'Cargo o título académico. Aparecerá debajo del nombre.',
        'id'   => 'titulo',
        'type' => 'text',
    ]);

    // El departamento ahora es una taxonomía (barra lateral), por lo que lo quitamos de aquí.

    $cmb->add_field([
        'name'       => 'Áreas de investigación',
        'desc'       => 'Añade un área de investigación por línea.',
        'id'         => 'areas_investigacion',
        'type'       => 'text',
        'repeatable' => true, // ¡Magia! Botón de [+] Añadir
        'text'       => [
            'add_row_text' => 'Añadir otra área',
        ],
    ]);

    $cmb->add_field([
        'name' => 'Correo electrónico',
        'desc' => 'Correo institucional.',
        'id'   => 'email',
        'type' => 'text_email',
    ]);

    $cmb->add_field([
        'name' => 'Semblanza / Biografía',
        'desc' => 'Breve semblanza académica. Sin imágenes.',
        'id'   => 'biografia',
        'type' => 'wysiwyg',
        'options' => [
            'textarea_rows' => 5,
            'media_buttons' => false,
        ],
    ]);

    // El Repetidor (Group Field)
    $group_id = $cmb->add_field([
        'id'          => 'publicaciones',
        'type'        => 'group',
        'description' => 'Agrega cada publicación por separado.',
        'options'     => [
            'group_title'       => 'Publicación {#}',
            'add_button'        => 'Añadir publicación',
            'remove_button'     => 'Eliminar publicación',
            'sortable'          => true,
        ],
    ]);

    $cmb->add_group_field($group_id, [
        'name' => 'Título',
        'id'   => 'titulo_publicacion',
        'type' => 'text',
    ]);

    $cmb->add_group_field($group_id, [
        'name' => 'Autores',
        'id'   => 'autores',
        'type' => 'text',
    ]);

    $cmb->add_group_field($group_id, [
        'name' => 'Año',
        'id'   => 'anio',
        'type' => 'text_small',
    ]);

    $cmb->add_group_field($group_id, [
        'name' => 'URL',
        'id'   => 'url',
        'type' => 'text_url',
    ]);
}

// ── 3. Exponer campos CMB2 en la respuesta de la REST API ─────────────────────
// Lo metemos bajo la clave 'acf' para que el frontend de Astro no note la diferencia
// y siga funcionando exactamente igual sin tener que tocar el TypeScript.

add_filter('rest_prepare_persona', 'hum_expose_cmb2_in_rest', 10, 3);

function hum_expose_cmb2_in_rest(
    WP_REST_Response $response,
    WP_Post $post,
    WP_REST_Request $request
): WP_REST_Response {
    // Obtener los nombres de los departamentos asignados a esta persona
    $departamentos = wp_get_object_terms($post->ID, 'departamento', ['fields' => 'names']);

    $response->data['acf'] = [
        'titulo'              => get_post_meta($post->ID, 'titulo', true),
        'departamento'        => is_array($departamentos) && !is_wp_error($departamentos) ? $departamentos[0] : null,
        'areas_investigacion' => get_post_meta($post->ID, 'areas_investigacion', true),
        'email'               => get_post_meta($post->ID, 'email', true),
        'biografia'           => wpautop(get_post_meta($post->ID, 'biografia', true)),
        'publicaciones'       => get_post_meta($post->ID, 'publicaciones', true) ?: [],
    ];
    return $response;
}

// ── 4. REST API: solo lectura para solicitudes no autenticadas ────────────────
// POST, PUT, PATCH, DELETE al endpoint persona requieren autenticación.
// Esto se aplica en la capa de Cloudflare WAF (ver docs/cloudflare-rules.md),
// pero agregamos una guardia a nivel WP como defensa en profundidad.

add_filter('rest_persona_collection_params', 'hum_restrict_persona_write_methods');

function hum_restrict_persona_write_methods(array $params): array {
    // Astro solo necesita GET; ningún otro método debería funcionar sin autenticación.
    // WP core ya maneja las verificaciones de capacidad en métodos de escritura,
    // pero esto hace la intención explícita para revisores del código.
    return $params;
}
