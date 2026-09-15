<?php
/**
 * Plugin Name: MiniKids MCP Abilities
 * Description: Expone abilities de la tienda MiniKids al servidor MCP (MCP Adapter + Abilities API).
 * Version: 1.0.0
 * Author: MiniKids
 *
 * Instalado como mu-plugin: se carga siempre, sin activacion manual.
 * Todas las abilities exigen la capacidad 'manage_options'.
 */

if (!defined('ABSPATH')) { exit; }

const MKP_MCP_CATEGORY = 'minikids';

/* -------------------------------------------------------------------------
 * Categoria
 * ---------------------------------------------------------------------- */

add_action('wp_abilities_api_categories_init', function () {
    if (function_exists('wp_register_ability_category')) {
        wp_register_ability_category(MKP_MCP_CATEGORY, [
            'label'       => 'MiniKids',
            'description' => 'Contenido, opciones y archivos de la tienda MiniKids.',
        ]);
    }
});

/* -------------------------------------------------------------------------
 * Helpers
 * ---------------------------------------------------------------------- */

function mkp_mcp_can() {
    return current_user_can('manage_options');
}

/**
 * Resuelve una ruta relativa dentro de wp-content y verifica que no se escape.
 */
function mkp_mcp_resolve_path($relative) {
    $base = realpath(WP_CONTENT_DIR);
    if (!$base) {
        return new WP_Error('mkp_no_base', 'No se pudo resolver wp-content.');
    }
    $relative = ltrim(str_replace('\\', '/', (string) $relative), '/');
    if ($relative === '' || strpos($relative, '..') !== false) {
        return new WP_Error('mkp_bad_path', 'Ruta invalida.');
    }
    $full  = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $dir   = realpath(dirname($full));
    // Para archivos nuevos, valida el directorio padre mas cercano que exista.
    while ($dir === false && strlen(dirname($full)) > strlen($base)) {
        $full = dirname($full);
        $dir  = realpath($full);
    }
    if ($dir === false || strpos($dir, $base) !== 0) {
        return new WP_Error('mkp_outside', 'La ruta queda fuera de wp-content.');
    }
    return $full;
}

/* -------------------------------------------------------------------------
 * Abilities
 * ---------------------------------------------------------------------- */

add_action('wp_abilities_api_init', function () {

    if (!function_exists('wp_register_ability')) {
        return;
    }

    /* -------- list-content -------- */
    wp_register_ability('minikids/list-content', [
        'label'       => 'Listar contenido',
        'description' => 'Lista entradas y paginas del sitio con id, titulo, slug, tipo y estado.',
        'category'    => MKP_MCP_CATEGORY,
        'input_schema' => [
            'type'       => 'object',
            'properties' => [
                'post_type' => ['type' => 'string', 'description' => 'page, post o any. Por defecto any.'],
                'status'    => ['type' => 'string', 'description' => 'publish, draft, any. Por defecto any.'],
                'limit'     => ['type' => 'integer', 'description' => 'Maximo de resultados (1-200). Por defecto 100.'],
            ],
            'additionalProperties' => false,
        ],
        'execute_callback' => function ($input = []) {
            $input = is_array($input) ? $input : [];
            $q = new WP_Query([
                'post_type'      => !empty($input['post_type']) ? $input['post_type'] : 'any',
                'post_status'    => !empty($input['status']) ? $input['status'] : 'any',
                'posts_per_page' => min(200, max(1, (int) ($input['limit'] ?? 100))),
                'orderby'        => 'ID',
                'order'          => 'ASC',
                'no_found_rows'  => true,
            ]);
            $out = [];
            foreach ($q->posts as $p) {
                $out[] = [
                    'id'     => $p->ID,
                    'title'  => $p->post_title,
                    'slug'   => $p->post_name,
                    'type'   => $p->post_type,
                    'status' => $p->post_status,
                ];
            }
            return ['count' => count($out), 'items' => $out];
        },
        'permission_callback' => 'mkp_mcp_can',
        'meta' => ['annotations' => ['readonly' => true, 'destructive' => false, 'idempotent' => true], 'public' => true],
    ]);

    /* -------- get-content -------- */
    wp_register_ability('minikids/get-content', [
        'label'       => 'Obtener contenido',
        'description' => 'Devuelve el contenido crudo (post_content), titulo y metadatos de una entrada o pagina.',
        'category'    => MKP_MCP_CATEGORY,
        'input_schema' => [
            'type'       => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'ID del post.'],
            ],
            'required'   => ['id'],
            'additionalProperties' => false,
        ],
        'execute_callback' => function ($input = []) {
            $input = is_array($input) ? $input : [];
            $post  = get_post((int) ($input['id'] ?? 0));
            if (!$post) {
                return new WP_Error('mkp_not_found', 'No existe un post con ese ID.');
            }
            return [
                'id'      => $post->ID,
                'title'   => $post->post_title,
                'slug'    => $post->post_name,
                'type'    => $post->post_type,
                'status'  => $post->post_status,
                'content' => $post->post_content,
            ];
        },
        'permission_callback' => 'mkp_mcp_can',
        'meta' => ['annotations' => ['readonly' => true, 'destructive' => false, 'idempotent' => true], 'public' => true],
    ]);

    /* -------- update-content -------- */
    wp_register_ability('minikids/update-content', [
        'label'       => 'Actualizar contenido',
        'description' => 'Actualiza el titulo y/o el contenido de una entrada o pagina existente.',
        'category'    => MKP_MCP_CATEGORY,
        'input_schema' => [
            'type'       => 'object',
            'properties' => [
                'id'      => ['type' => 'integer', 'description' => 'ID del post.'],
                'title'   => ['type' => 'string', 'description' => 'Nuevo titulo (opcional).'],
                'content' => ['type' => 'string', 'description' => 'Nuevo post_content (opcional).'],
                'status'  => ['type' => 'string', 'description' => 'publish, draft, etc. (opcional).'],
            ],
            'required'   => ['id'],
            'additionalProperties' => false,
        ],
        'execute_callback' => function ($input = []) {
            $input = is_array($input) ? $input : [];
            $id    = (int) ($input['id'] ?? 0);
            if (!get_post($id)) {
                return new WP_Error('mkp_not_found', 'No existe un post con ese ID.');
            }
            $data = ['ID' => $id];
            if (isset($input['title']))   { $data['post_title']   = (string) $input['title']; }
            if (isset($input['content'])) { $data['post_content'] = (string) $input['content']; }
            if (isset($input['status']))  { $data['post_status']  = (string) $input['status']; }
            $res = wp_update_post($data, true);
            if (is_wp_error($res)) {
                return $res;
            }
            return ['id' => $res, 'updated' => true];
        },
        'permission_callback' => 'mkp_mcp_can',
        'meta' => ['annotations' => ['readonly' => false, 'destructive' => false, 'idempotent' => false], 'public' => true],
    ]);

    /* -------- get-option -------- */
    wp_register_ability('minikids/get-option', [
        'label'       => 'Leer opcion',
        'description' => 'Lee una opcion de WordPress (wp_options) por su nombre.',
        'category'    => MKP_MCP_CATEGORY,
        'input_schema' => [
            'type'       => 'object',
            'properties' => [
                'name' => ['type' => 'string', 'description' => 'Nombre de la opcion, p.ej. siteurl o active_plugins.'],
            ],
            'required'   => ['name'],
            'additionalProperties' => false,
        ],
        'execute_callback' => function ($input = []) {
            $input = is_array($input) ? $input : [];
            $name  = (string) ($input['name'] ?? '');
            if ($name === '') {
                return new WP_Error('mkp_bad_input', 'Falta el nombre de la opcion.');
            }
            $val = get_option($name, null);
            return ['name' => $name, 'exists' => ($val !== null), 'value' => $val];
        },
        'permission_callback' => 'mkp_mcp_can',
        'meta' => ['annotations' => ['readonly' => true, 'destructive' => false, 'idempotent' => true], 'public' => true],
    ]);

    /* -------- update-option -------- */
    wp_register_ability('minikids/update-option', [
        'label'       => 'Actualizar opcion',
        'description' => 'Crea o actualiza una opcion de WordPress. Acepta strings, numeros, booleanos y arrays/objetos.',
        'category'    => MKP_MCP_CATEGORY,
        'input_schema' => [
            'type'       => 'object',
            'properties' => [
                'name'  => ['type' => 'string', 'description' => 'Nombre de la opcion.'],
                'value' => ['description' => 'Nuevo valor (cualquier tipo JSON).'],
            ],
            'required'   => ['name', 'value'],
            'additionalProperties' => false,
        ],
        'execute_callback' => function ($input = []) {
            $input = is_array($input) ? $input : [];
            $name  = (string) ($input['name'] ?? '');
            if ($name === '') {
                return new WP_Error('mkp_bad_input', 'Falta el nombre de la opcion.');
            }
            $ok = update_option($name, $input['value'] ?? null);
            return ['name' => $name, 'changed' => (bool) $ok];
        },
        'permission_callback' => 'mkp_mcp_can',
        'meta' => ['annotations' => ['readonly' => false, 'destructive' => false, 'idempotent' => true], 'public' => true],
    ]);

    /* -------- list-plugins -------- */
    wp_register_ability('minikids/list-plugins', [
        'label'       => 'Listar plugins',
        'description' => 'Lista los plugins instalados indicando si estan activos y su version.',
        'category'    => MKP_MCP_CATEGORY,
        'input_schema' => [
            'type'       => 'object',
            'properties' => new stdClass(),
            'additionalProperties' => false,
        ],
        'execute_callback' => function () {
            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $active = (array) get_option('active_plugins', []);
            $out    = [];
            foreach (get_plugins() as $file => $data) {
                $out[] = [
                    'file'    => $file,
                    'name'    => $data['Name'],
                    'version' => $data['Version'],
                    'active'  => in_array($file, $active, true),
                ];
            }
            return ['count' => count($out), 'items' => $out];
        },
        'permission_callback' => 'mkp_mcp_can',
        'meta' => ['annotations' => ['readonly' => true, 'destructive' => false, 'idempotent' => true], 'public' => true],
    ]);

    /* -------- read-file -------- */
    wp_register_ability('minikids/read-file', [
        'label'       => 'Leer archivo',
        'description' => 'Lee un archivo dentro de wp-content (ruta relativa, p.ej. plugins/minikids-pages/cart.js).',
        'category'    => MKP_MCP_CATEGORY,
        'input_schema' => [
            'type'       => 'object',
            'properties' => [
                'path' => ['type' => 'string', 'description' => 'Ruta relativa a wp-content.'],
            ],
            'required'   => ['path'],
            'additionalProperties' => false,
        ],
        'execute_callback' => function ($input = []) {
            $input = is_array($input) ? $input : [];
            $path  = mkp_mcp_resolve_path($input['path'] ?? '');
            if (is_wp_error($path)) {
                return $path;
            }
            if (!is_file($path)) {
                return new WP_Error('mkp_no_file', 'No existe el archivo indicado.');
            }
            return ['path' => $input['path'], 'size' => filesize($path), 'content' => file_get_contents($path)];
        },
        'permission_callback' => 'mkp_mcp_can',
        'meta' => ['annotations' => ['readonly' => true, 'destructive' => false, 'idempotent' => true], 'public' => true],
    ]);

    /* -------- write-file -------- */
    wp_register_ability('minikids/write-file', [
        'label'       => 'Escribir archivo',
        'description' => 'Escribe o reemplaza un archivo dentro de wp-content. Herramienta de escritura: usar con cuidado.',
        'category'    => MKP_MCP_CATEGORY,
        'input_schema' => [
            'type'       => 'object',
            'properties' => [
                'path'    => ['type' => 'string', 'description' => 'Ruta relativa a wp-content.'],
                'content' => ['type' => 'string', 'description' => 'Contenido completo del archivo.'],
            ],
            'required'   => ['path', 'content'],
            'additionalProperties' => false,
        ],
        'execute_callback' => function ($input = []) {
            $input = is_array($input) ? $input : [];
            $path  = mkp_mcp_resolve_path($input['path'] ?? '');
            if (is_wp_error($path)) {
                return $path;
            }
            $dir = dirname($path);
            if (!is_dir($dir) && !wp_mkdir_p($dir)) {
                return new WP_Error('mkp_no_dir', 'No se pudo crear el directorio destino.');
            }
            // Respaldo automatico del archivo previo.
            $backup = null;
            if (is_file($path)) {
                $backup = $path . '.bak-' . gmdate('Ymd-His');
                copy($path, $backup);
            }
            $bytes = file_put_contents($path, (string) ($input['content'] ?? ''));
            if ($bytes === false) {
                return new WP_Error('mkp_write_failed', 'No se pudo escribir el archivo.');
            }
            return [
                'path'       => $input['path'],
                'bytes'      => $bytes,
                'backup'     => $backup ? basename($backup) : null,
            ];
        },
        'permission_callback' => 'mkp_mcp_can',
        'meta' => ['annotations' => ['readonly' => false, 'destructive' => true, 'idempotent' => false], 'public' => true],
    ]);
});
