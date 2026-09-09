<?php
if (!defined('ABSPATH')) { exit; }

function minikids_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', ['height' => 80, 'width' => 240, 'flex-height' => true, 'flex-width' => true]);
    register_nav_menus(['primary' => __('Navegación principal', 'minikids-toys')]);
}
add_action('after_setup_theme', 'minikids_setup');

function minikids_assets() {
    wp_enqueue_style('minikids-fonts', 'https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Nunito+Sans:wght@400;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('minikids-style', get_stylesheet_uri(), [], '1.0.0');
    wp_enqueue_script('minikids-cart', get_template_directory_uri() . '/assets/js/cart.js', [], '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'minikids_assets');

function minikids_route_catalog() {
    if (is_front_page() && (isset($_GET['categoria']) || isset($_GET['buscar']))) {
        include get_template_directory() . '/index.php';
        exit;
    }
}
add_action('template_redirect', 'minikids_route_catalog');

function minikids_products() {
    return [
        ['id' => 1, 'name' => 'Peluche osito abrazable', 'category' => 'Peluches', 'slug' => 'peluches', 'price' => 39.90, 'old' => 49.90, 'emoji' => '🧸', 'image' => 'https://images.unsplash.com/photo-1559454403-b8fb88521f11?auto=format&fit=crop&w=700&q=80', 'badge' => 'Top ventas'],
        ['id' => 2, 'name' => 'Bloques creativos 60 piezas', 'category' => 'Didácticos', 'slug' => 'didacticos', 'price' => 29.90, 'old' => 39.90, 'emoji' => '🧱', 'image' => 'https://images.unsplash.com/photo-1594787318286-3d835c1d207f?auto=format&fit=crop&w=700&q=80', 'badge' => 'Nuevo'],
        ['id' => 3, 'name' => 'Set de cocina mini chef', 'category' => 'Juegos de rol', 'slug' => 'juegos-de-rol', 'price' => 34.90, 'old' => '', 'emoji' => '🍳', 'image' => 'https://images.unsplash.com/photo-1596461404969-9ae70f2830c1?auto=format&fit=crop&w=700&q=80', 'badge' => 'Favorito'],
        ['id' => 4, 'name' => 'Pista de carreras turbo', 'category' => 'Vehículos', 'slug' => 'vehiculos', 'price' => 44.90, 'old' => 54.90, 'emoji' => '🚗', 'image' => 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?auto=format&fit=crop&w=700&q=80', 'badge' => 'Oferta'],
        ['id' => 5, 'name' => 'Kit de arte pequeños artistas', 'category' => 'Creatividad', 'slug' => 'creatividad', 'price' => 24.90, 'old' => '', 'emoji' => '🎨', 'image' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=700&q=80', 'badge' => 'Nuevo'],
        ['id' => 6, 'name' => 'Juego de memoria animalitos', 'category' => 'Juegos familiares', 'slug' => 'juegos-familiares', 'price' => 19.90, 'old' => '', 'emoji' => '🦊', 'image' => 'https://images.unsplash.com/photo-1618842676088-c4d48a6a7c9d?auto=format&fit=crop&w=700&q=80', 'badge' => 'Top ventas'],
        ['id' => 7, 'name' => 'Mini karaoke con micrófono', 'category' => 'Tecnología', 'slug' => 'tecnologia', 'price' => 59.90, 'old' => 69.90, 'emoji' => '🎤', 'image' => 'https://images.unsplash.com/photo-1520523839897-bd0b52f945a0?auto=format&fit=crop&w=700&q=80', 'badge' => 'Oferta'],
        ['id' => 8, 'name' => 'Maletín explorador junior', 'category' => 'Aventura', 'slug' => 'aventura', 'price' => 49.90, 'old' => '', 'emoji' => '🔭', 'image' => 'https://images.unsplash.com/photo-1607453998774-d533f65dac99?auto=format&fit=crop&w=700&q=80', 'badge' => 'Nuevo'],
    ];
}

function minikids_money($value) { return 'S/ ' . number_format((float) $value, 2); }

function minikids_product_card($product) {
    $data = esc_attr(wp_json_encode(['id' => $product['id'], 'name' => $product['name'], 'price' => $product['price'], 'emoji' => $product['emoji'], 'image' => $product['image']]));
    ?>
    <article class="product-card">
        <a class="product-image" href="<?php echo esc_url(home_url('/?producto=' . $product['id'])); ?>" aria-label="<?php echo esc_attr($product['name']); ?>">
            <?php if (!empty($product['badge'])) : ?><span class="badge"><?php echo esc_html($product['badge']); ?></span><?php endif; ?>
            <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['name']); ?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
            <span class="product-emoji" style="display:none"><?php echo esc_html($product['emoji']); ?></span>
        </a>
        <div class="product-info">
            <div class="product-category"><?php echo esc_html($product['category']); ?></div>
            <h3 class="product-name"><?php echo esc_html($product['name']); ?></h3>
            <div class="price"><?php echo esc_html(minikids_money($product['price'])); ?><?php if ($product['old']) : ?> <s><?php echo esc_html(minikids_money($product['old'])); ?></s><?php endif; ?></div>
            <button class="add-button js-add-cart" type="button" data-product="<?php echo $data; ?>">Añadir al carrito <span>＋</span></button>
        </div>
    </article>
    <?php
}
