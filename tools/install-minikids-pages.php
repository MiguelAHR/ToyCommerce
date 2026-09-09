<?php
require 'C:/xampp/htdocs/wordpress/wp-load.php';

$pages = [
    'inicio' => ['title' => 'Inicio', 'content' => '<!-- wp:minikids-pages/site {"page":"home"} /-->'],
    'tienda' => ['title' => 'Tienda', 'content' => '<!-- wp:minikids-pages/site {"page":"shop"} /-->'],
    'nosotros' => ['title' => 'Nosotros', 'content' => '<!-- wp:minikids-pages/site {"page":"about"} /-->'],
    'contacto' => ['title' => 'Contacto', 'content' => '<!-- wp:minikids-pages/site {"page":"contact"} /-->'],
];

$ids = [];
foreach ($pages as $slug => $page) {
    $existing = get_page_by_path($slug);
    $post = [
        'post_title' => $page['title'],
        'post_name' => $slug,
        'post_content' => $page['content'],
        'post_status' => 'publish',
        'post_type' => 'page',
    ];
    $ids[$slug] = $existing ? wp_update_post(array_merge($post, ['ID' => $existing->ID]), true) : wp_insert_post($post, true);
}

$active = get_option('active_plugins', []);
if (!in_array('minikids-pages/minikids-pages.php', $active, true)) {
    $active[] = 'minikids-pages/minikids-pages.php';
    update_option('active_plugins', $active);
}
update_option('template', 'twentytwentyfive');
update_option('stylesheet', 'twentytwentyfive');
update_option('show_on_front', 'page');
update_option('page_on_front', (int) $ids['inicio']);
update_option('blogname', 'MiniKids Peru');
update_option('blogdescription', 'Juguetes, regalos y grandes sonrisas');

echo "PAGES_READY\n";
foreach ($ids as $slug => $id) echo $slug . ':' . $id . ':' . get_permalink($id) . "\n";
