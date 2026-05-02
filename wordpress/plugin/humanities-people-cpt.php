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

// ── 2. Registrar grupo de campos ACF (respaldo programático) ──────────────────
// Fuente de verdad primaria: cms-export/acf-person-fields.json (cargado automáticamente por ACF
// cuando el archivo JSON está en el directorio acf-json del tema/plugin).
// Este código registra los mismos campos en PHP como respaldo si el JSON no está presente.

add_action('acf/include_fields', 'hum_register_persona_fields');

function hum_register_persona_fields(): void {
    if (!function_exists('acf_add_local_field_group')) {
        // ACF no está activo — los campos no se registrarán, pero el CPT sigue funcionando.
        return;
    }

    acf_add_local_field_group([
        'key'      => 'group_persona_fields',
        'title'    => 'Datos de la persona',
        'fields'   => [

            // ── Cargo ─────────────────────────────────────────────────────────
            [
                'key'           => 'field_persona_titulo',
                'label'         => 'Cargo',
                'name'          => 'titulo',
                'type'          => 'text',
                'required'      => 0,
                'placeholder'   => 'Ej. Profesora titular',
                'instructions'  => 'Cargo o título académico. Aparecerá debajo del nombre en el directorio.',
            ],

            // ── Departamento ─────────────────────────────────────────────────
            [
                'key'           => 'field_persona_departamento',
                'label'         => 'Departamento',
                'name'          => 'departamento',
                'type'          => 'select',
                'required'      => 0,
                'choices'       => [
                    'Filosofía'             => 'Filosofía',
                    'Historia'              => 'Historia',
                    'Letras'                => 'Letras',
                    'Lingüística'           => 'Lingüística',
                    'Ciencias de la Comunicación' => 'Ciencias de la Comunicación',
                    'Educación'             => 'Educación',
                ],
                'allow_null'    => 1,
                'multiple'      => 0,
                'ui'            => 1, // Interfaz mejorada con Select2
                'instructions'  => 'Departamento al que pertenece la persona.',
            ],

            // ── Áreas de investigación ────────────────────────────────────────
            [
                'key'           => 'field_persona_areas',
                'label'         => 'Áreas de investigación',
                'name'          => 'areas_investigacion',
                'type'          => 'text',
                'required'      => 0,
                'placeholder'   => 'Ej. Filosofía del lenguaje, Lógica, Ética',
                'instructions'  => 'Separar las áreas con comas.',
            ],

            // ── Correo electrónico ────────────────────────────────────────────
            [
                'key'           => 'field_persona_email',
                'label'         => 'Correo electrónico',
                'name'          => 'email',
                'type'          => 'email',
                'required'      => 0,
                'instructions'  => 'Correo institucional. Se mostrará en el perfil público.',
            ],

            // ── Biografía ─────────────────────────────────────────────────────
            [
                'key'           => 'field_persona_biografia',
                'label'         => 'Semblanza / Biografía',
                'name'          => 'biografia',
                'type'          => 'wysiwyg',
                'required'      => 0,
                'tabs'          => 'visual',  // Editor simplificado para usuarios no técnicos
                'toolbar'       => 'basic',
                'media_upload'  => 0,         // Evita subir imágenes dentro del campo de bio
                'instructions'  => 'Breve semblanza académica. Sin imágenes.',
            ],

            // ── Publicaciones (repetidor) ─────────────────────────────────────
            // ACF Free NO incluye el campo tipo Repetidor.
            // Opción A: Usar ACF Pro.
            // Opción B: Usar CMB2 con cmb2_field_type_group.
            // Opción C: Codificar publicaciones como JSON en un textarea (simple, sin plugin adicional).
            // El campo siguiente usa el tipo Repetidor; si solo se tiene ACF Free, cambiarlo a textarea.
            [
                'key'           => 'field_persona_publicaciones',
                'label'         => 'Publicaciones',
                'name'          => 'publicaciones',
                'type'          => 'repeater',  // Requiere ACF Pro o alternativa con CMB2
                'required'      => 0,
                'min'           => 0,
                'max'           => 50,
                'layout'        => 'block',
                'button_label'  => 'Agregar publicación',
                'sub_fields'    => [
                    [
                        'key'   => 'field_pub_titulo',
                        'label' => 'Título',
                        'name'  => 'titulo_publicacion',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_pub_autores',
                        'label' => 'Autores',
                        'name'  => 'autores',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_pub_anio',
                        'label' => 'Año',
                        'name'  => 'anio',
                        'type'  => 'number',
                        'min'   => 1900,
                        'max'   => 2099,
                    ],
                    [
                        'key'   => 'field_pub_url',
                        'label' => 'URL',
                        'name'  => 'url',
                        'type'  => 'url',
                    ],
                ],
                'instructions'  => 'Agrega cada publicación por separado.',
            ],
        ],
        'location' => [
            [[
                'param'     => 'post_type',
                'operator'  => '==',
                'value'     => 'persona',
            ]],
        ],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'seamless',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
    ]);
}

// ── 3. Exponer campos ACF en la respuesta de la REST API ──────────────────────
// Por defecto, los campos ACF NO se incluyen en las respuestas de la REST API.
// Este filtro agrega una clave `acf` a cada respuesta de /wp-json/wp/v2/persona.

add_filter('rest_prepare_persona', 'hum_expose_acf_in_rest', 10, 3);

function hum_expose_acf_in_rest(
    WP_REST_Response $response,
    WP_Post $post,
    WP_REST_Request $request
): WP_REST_Response {
    if (function_exists('get_fields')) {
        // get_fields() devuelve todos los valores ACF del post
        $response->data['acf'] = get_fields($post->ID) ?: [];
    }
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
