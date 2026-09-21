<?php
/**
 * Plugin Name: MiniKids Pages
 * Description: Construye las páginas públicas de la tienda MiniKids desde WordPress, sin depender de un tema personalizado.
 * Version: 1.0.0
 * Author: MiniKids
 */
if (!defined('ABSPATH')) { exit; }

function mkp_assets() {
    wp_enqueue_style('mkp-fonts', 'https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Nunito+Sans:wght@400;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('mkp-style', plugin_dir_url(__FILE__) . 'minikids-pages.css', [], '1.0.1');
    wp_enqueue_script('mkp-cart', plugin_dir_url(__FILE__) . 'cart.js', [], '1.0.1', true);
}
add_action('wp_enqueue_scripts', 'mkp_assets');

function mkp_register_blocks() {
    wp_register_script(
        'mkp-block-editor',
        plugin_dir_url(__FILE__) . 'block-editor.js',
        ['wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-i18n'],
        '1.0.0',
        true
    );
    wp_register_style('mkp-editor-style', plugin_dir_url(__FILE__) . 'minikids-pages.css', [], '1.0.1');
    register_block_type('minikids-pages/site', [
        'api_version' => 3,
        'editor_script' => 'mkp-block-editor',
        'editor_style' => 'mkp-editor-style',
        'attributes' => [
            'page' => ['type' => 'string', 'default' => 'home'],
        ],
        'render_callback' => 'mkp_render_site_block',
    ]);
}
add_action('init', 'mkp_register_blocks');
function mkp_editor_assets() {
    wp_enqueue_style('mkp-editor-public', plugin_dir_url(__FILE__) . 'minikids-pages.css', [], '1.0.1');
    wp_enqueue_style('mkp-editor-toolbar', plugin_dir_url(__FILE__) . 'editor.css', [], '1.0.0');
}
add_action('enqueue_block_editor_assets', 'mkp_editor_assets');

function mkp_products() {
    return [
        ['id'=>1,'name'=>'Peluche osito abrazable','category'=>'Peluches','slug'=>'peluches','price'=>39.90,'old'=>49.90,'emoji'=>'🧸','image'=>'https://images.unsplash.com/photo-1559454403-b8fb88521f11?auto=format&fit=crop&w=700&q=80','badge'=>'Top ventas'],
        ['id'=>2,'name'=>'Bloques creativos 60 piezas','category'=>'Didácticos','slug'=>'didacticos','price'=>29.90,'old'=>39.90,'emoji'=>'🧱','image'=>'https://images.unsplash.com/photo-1594787318286-3d835c1d207f?auto=format&fit=crop&w=700&q=80','badge'=>'Nuevo'],
        ['id'=>3,'name'=>'Set de cocina mini chef','category'=>'Juegos de rol','slug'=>'juegos-de-rol','price'=>34.90,'old'=>'','emoji'=>'🍳','image'=>'https://images.unsplash.com/photo-1596461404969-9ae70f2830c1?auto=format&fit=crop&w=700&q=80','badge'=>'Favorito'],
        ['id'=>4,'name'=>'Pista de carreras turbo','category'=>'Vehículos','slug'=>'vehiculos','price'=>44.90,'old'=>54.90,'emoji'=>'🚗','image'=>'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?auto=format&fit=crop&w=700&q=80','badge'=>'Oferta'],
        ['id'=>5,'name'=>'Kit de arte pequeños artistas','category'=>'Creatividad','slug'=>'creatividad','price'=>24.90,'old'=>'','emoji'=>'🎨','image'=>'https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=700&q=80','badge'=>'Nuevo'],
        ['id'=>6,'name'=>'Juego de memoria animalitos','category'=>'Juegos familiares','slug'=>'juegos-familiares','price'=>19.90,'old'=>'','emoji'=>'🦊','image'=>'https://images.unsplash.com/photo-1618842676088-c4d48a6a7c9d?auto=format&fit=crop&w=700&q=80','badge'=>'Top ventas'],
        ['id'=>7,'name'=>'Mini karaoke con micrófono','category'=>'Tecnología','slug'=>'tecnologia','price'=>59.90,'old'=>69.90,'emoji'=>'🎤','image'=>'https://images.unsplash.com/photo-1520523839897-bd0b52f945a0?auto=format&fit=crop&w=700&q=80','badge'=>'Oferta'],
        ['id'=>8,'name'=>'Maletín explorador junior','category'=>'Aventura','slug'=>'aventura','price'=>49.90,'old'=>'','emoji'=>'🔭','image'=>'https://images.unsplash.com/photo-1607453998774-d533f65dac99?auto=format&fit=crop&w=700&q=80','badge'=>'Nuevo'],
    ];
}
function mkp_money($value) { return 'S/ ' . number_format((float)$value, 2); }
function mkp_url($slug) { $page = get_page_by_path($slug); return $page ? get_permalink($page) : home_url('/'); }
function mkp_card($p) {
    $data = esc_attr(wp_json_encode(['id'=>$p['id'],'name'=>$p['name'],'price'=>$p['price'],'emoji'=>$p['emoji'],'image'=>$p['image']]));
    ob_start(); ?>
    <article class="product-card"><a class="product-image" href="#producto-<?php echo esc_attr($p['id']); ?>"><span class="badge"><?php echo esc_html($p['badge']); ?></span><img src="<?php echo esc_url($p['image']); ?>" alt="<?php echo esc_attr($p['name']); ?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='block';"><span class="product-emoji" style="display:none"><?php echo esc_html($p['emoji']); ?></span></a><div class="product-info"><div class="product-category"><?php echo esc_html($p['category']); ?></div><h3 class="product-name"><?php echo esc_html($p['name']); ?></h3><div class="price"><?php echo esc_html(mkp_money($p['price'])); ?><?php if ($p['old']) : ?> <s><?php echo esc_html(mkp_money($p['old'])); ?></s><?php endif; ?></div><button class="add-button js-add-cart" type="button" data-product="<?php echo $data; ?>">Añadir al carrito <span>＋</span></button></div></article>
    <?php return ob_get_clean();
}
function mkp_header() { ob_start(); ?>
    <div class="top-strip">🚚 Envío gratis por compras mayores a S/ 149 · Compra segura y fácil</div><header class="site-header"><div class="container header-main"><a class="brand" href="<?php echo esc_url(mkp_url('inicio')); ?>"><span class="brand-mark">✦</span><span>minikids<small>juguetes & diversión</small></span></a><form class="header-search" action="<?php echo esc_url(mkp_url('tienda')); ?>" method="get"><input type="search" name="buscar" placeholder="¿Qué estás buscando hoy?" aria-label="Buscar productos"><button type="submit" aria-label="Buscar">⌕</button></form><div class="header-actions"><a class="header-action" href="#"><span>♙</span><label>Mi cuenta</label></a><button class="header-action js-open-cart" type="button"><span>🛒</span><label>Carrito</label><b class="cart-count">0</b></button></div></div><nav class="main-nav"><div class="container"><ul class="nav-list"><li><a href="<?php echo esc_url(mkp_url('inicio')); ?>">Inicio</a></li><li><a href="<?php echo esc_url(mkp_url('tienda')); ?>">Tienda</a></li><li><a href="<?php echo esc_url(mkp_url('tienda')); ?>?categoria=peluches">Peluches</a></li><li><a href="<?php echo esc_url(mkp_url('tienda')); ?>?categoria=didacticos">Didácticos</a></li><li><a href="<?php echo esc_url(mkp_url('tienda')); ?>?categoria=juegos-familiares">Juegos familiares</a></li><li><a href="<?php echo esc_url(mkp_url('tienda')); ?>?categoria=ofertas">Ofertas</a></li><li><a href="<?php echo esc_url(mkp_url('contacto')); ?>">Contáctanos</a></li></ul></div></nav></header>
    <?php return ob_get_clean(); }
function mkp_footer() { ob_start(); ?>
    <footer class="site-footer"><div class="container"><div class="footer-grid"><div class="footer-brand"><a class="brand" href="<?php echo esc_url(mkp_url('inicio')); ?>"><span class="brand-mark">✦</span><span>minikids<small>juguetes & diversión</small></span></a><p>Pequeñas sorpresas para grandes sonrisas. Juguetes, juegos y regalos para cada aventura.</p></div><div><h3 class="footer-title">Ayuda</h3><ul class="footer-list"><li><a href="#">Preguntas frecuentes</a></li><li><a href="#">Formas de pago</a></li><li><a href="#">Cambios y devoluciones</a></li></ul></div><div><h3 class="footer-title">Nosotros</h3><ul class="footer-list"><li><a href="<?php echo esc_url(mkp_url('nosotros')); ?>">Quiénes somos</a></li><li><a href="#">Nuestras tiendas</a></li><li><a href="<?php echo esc_url(mkp_url('contacto')); ?>">Contáctanos</a></li></ul></div><div><h3 class="footer-title">Síguenos</h3><ul class="footer-list"><li><a href="#">Instagram</a></li><li><a href="#">Facebook</a></li><li><a href="#">TikTok</a></li></ul></div></div><div class="footer-bottom"><span>© <?php echo esc_html(date('Y')); ?> MiniKids Peru. Todos los derechos reservados.</span><span>Compra segura · Términos y condiciones</span></div></div></footer><div class="cart-drawer" aria-hidden="true"><aside class="cart-panel" role="dialog" aria-label="Carrito de compras"><div class="cart-head"><h2>Tu carrito <span class="cart-count">0</span></h2><button class="cart-close" type="button" aria-label="Cerrar carrito">×</button></div><div class="cart-items"><div class="cart-empty">Tu carrito está esperando una aventura ✨</div></div><div class="cart-foot"><div class="cart-total"><span>Total</span><span class="cart-total-value">S/ 0.00</span></div><a class="button" href="#checkout">Ir a pagar <span>→</span></a></div></aside></div>
    <?php return ob_get_clean(); }
function mkp_home() {
    $products = mkp_products(); ob_start(); ?>
    <main><section class="hero"><div class="container hero-grid"><div class="hero-copy"><span class="eyebrow">La diversión empieza aquí</span><h1>Juega, imagina y <em>sonríe.</em></h1><p>Descubre juguetes que despiertan la curiosidad y convierten cada día en una nueva aventura.</p><a class="button" href="<?php echo esc_url(mkp_url('tienda')); ?>">Ver juguetes <span>→</span></a></div><div class="hero-art"><div class="toy-illustration"><span class="spark one">✦</span><span class="spark two">✦</span><div class="toy-star">★</div><div class="toy-bear"><div class="bear-face"></div><div class="scarf"></div></div></div></div></div></section><div class="container"><div class="trust-row"><div class="trust-item"><span class="trust-icon">🚚</span><span>Envíos a todo<br>el Perú</span></div><div class="trust-item"><span class="trust-icon">🎁</span><span>Regalos para<br>cada ocasión</span></div><div class="trust-item"><span class="trust-icon">🔒</span><span>Compra<br>segura</span></div><div class="trust-item"><span class="trust-icon">💬</span><span>Estamos para<br>ayudarte</span></div></div></div><section class="section"><div class="container"><div class="section-head"><div><h2 class="section-title">Compra por categoría</h2><p class="section-subtitle">Todo lo que necesitas para llenar sus días de diversión.</p></div></div><div class="category-grid"><a class="category-card" href="<?php echo esc_url(mkp_url('tienda')); ?>?categoria=peluches"><h3>Peluches</h3><span class="emoji">🧸</span></a><a class="category-card" href="<?php echo esc_url(mkp_url('tienda')); ?>?categoria=didacticos"><h3>Aprende jugando</h3><span class="emoji">🧩</span></a><a class="category-card" href="<?php echo esc_url(mkp_url('tienda')); ?>?categoria=juegos-familiares"><h3>Para compartir</h3><span class="emoji">🎲</span></a><a class="category-card" href="<?php echo esc_url(mkp_url('tienda')); ?>?categoria=vehiculos"><h3>Mini aventuras</h3><span class="emoji">🚀</span></a></div></div></section><section class="section" id="destacados"><div class="container"><div class="section-head"><div><h2 class="section-title">Favoritos de la semana</h2><p class="section-subtitle">Los juguetes que todos quieren llevar a casa.</p></div><a class="text-link" href="<?php echo esc_url(mkp_url('tienda')); ?>">Ver todos →</a></div><div class="product-grid"><?php foreach (array_slice($products,0,4) as $p) echo mkp_card($p); ?></div></div></section><section class="section"><div class="container"><div class="promo-band"><div><span class="eyebrow">Pequeños precios, grandes momentos</span><h2>Todo por debajo de S/ 29.90</h2><p>Encuentra detalles divertidos para sorprender sin complicarte.</p><a class="button light" href="<?php echo esc_url(mkp_url('tienda')); ?>?categoria=ofertas">Ver ofertas <span>→</span></a></div><div class="promo-art" aria-hidden="true">🎈 🧸 🎁</div></div></div></section><section class="section"><div class="container"><div class="newsletter"><div><h2>Recibe un poquito de magia</h2><p>Suscríbete y entérate primero de nuestras novedades y promos.</p></div><form class="newsletter-form" action="#" method="post"><input type="email" placeholder="Tu correo electrónico" aria-label="Tu correo electrónico" required><button type="submit">Suscribirme</button></form></div></div></section></main>
    <?php return ob_get_clean();
}
function mkp_shop() {
    $products=mkp_products(); $cat=isset($_GET['categoria'])?sanitize_title(wp_unslash($_GET['categoria'])):''; $search=isset($_GET['buscar'])?strtolower(sanitize_text_field(wp_unslash($_GET['buscar']))):''; $items=array_filter($products,function($p)use($cat,$search){$ok=!$cat||($cat==='ofertas'?(bool)$p['old']:$p['slug']===$cat);$find=!$search||strpos(strtolower($p['name'].' '.$p['category']),$search)!==false;return $ok&&$find;}); ob_start(); ?>
    <main><section class="catalog-heading"><div class="container"><h1><?php echo esc_html($search?'Resultados de búsqueda':($cat?ucwords(str_replace('-',' ',$cat)):'Todos los juguetes')); ?></h1><p>Elige una nueva aventura para cada día.</p></div></section><section class="section"><div class="container"><div class="catalog-toolbar"><div class="filter-pills"><a class="filter-pill <?php echo !$cat?'active':''; ?>" href="<?php echo esc_url(mkp_url('tienda')); ?>">Todos</a><a class="filter-pill <?php echo $cat==='peluches'?'active':''; ?>" href="?categoria=peluches">Peluches</a><a class="filter-pill <?php echo $cat==='didacticos'?'active':''; ?>" href="?categoria=didacticos">Didácticos</a><a class="filter-pill <?php echo $cat==='ofertas'?'active':''; ?>" href="?categoria=ofertas">Ofertas</a></div><span class="section-subtitle"><?php echo count($items); ?> productos</span></div><?php if ($items): ?><div class="product-grid"><?php foreach($items as $p) echo mkp_card($p); ?></div><?php else: ?><div class="empty-state">No encontramos ese juguete. Prueba con “peluche”, “juego” o “bloques”.</div><?php endif; ?></div></section></main>
    <?php return ob_get_clean();
}
function mkp_about() { return '<main><section class="catalog-heading"><div class="container"><h1>Jugar es crecer</h1><p>Conoce la historia detrás de MiniKids.</p></div></section><section class="section"><div class="container"><div class="promo-band"><div><span class="eyebrow">Nuestra historia</span><h2>Pequeñas sorpresas para grandes sonrisas.</h2><p>En MiniKids creemos que el mejor regalo no es solo un juguete: es el momento de descubrir, imaginar y compartir. Seleccionamos productos bonitos, seguros y llenos de posibilidades para cada etapa.</p></div><div class="promo-art" aria-hidden="true">🧸 ✨ 🎨</div></div></div></section><section class="section"><div class="container"><div class="category-grid"><div class="category-card"><h3>Curados con cariño</h3><span class="emoji">💛</span></div><div class="category-card"><h3>Para imaginar</h3><span class="emoji">🚀</span></div><div class="category-card"><h3>Para compartir</h3><span class="emoji">🎲</span></div><div class="category-card"><h3>Para sonreír</h3><span class="emoji">😊</span></div></div></div></section></main>'; }
function mkp_contact() { return '<main><section class="catalog-heading"><div class="container"><h1>Hablemos</h1><p>Estamos aquí para ayudarte a encontrar el regalo perfecto.</p></div></section><section class="section"><div class="container"><div class="newsletter"><div><h2>¿Tienes una pregunta?</h2><p>Escríbenos a hola@minikids.pe y te responderemos pronto.</p></div><a class="button light" href="mailto:hola@minikids.pe">Escribir ahora →</a></div></div></section><section class="section"><div class="container"><div class="category-grid"><div class="category-card"><h3>Atención online</h3><span class="emoji">💬</span></div><div class="category-card"><h3>Envíos a todo el Perú</h3><span class="emoji">🚚</span></div><div class="category-card"><h3>Compra segura</h3><span class="emoji">🔒</span></div><div class="category-card"><h3>Regalos fáciles</h3><span class="emoji">🎁</span></div></div></div></section></main>'; }
function mkp_site_shortcode($atts=[]) { $kind=isset($atts['page'])?$atts['page']:'home'; return $kind==='shop'?mkp_shop():($kind==='about'?mkp_about():($kind==='contact'?mkp_contact():mkp_home())); }
add_shortcode('minikids_site','mkp_site_shortcode');
function mkp_render_site_block($attributes=[]) { return mkp_site_shortcode(['page' => isset($attributes['page']) ? $attributes['page'] : 'home']); }

function mkp_page_shell() {
    if (!is_page()) return;
    $page=get_queried_object(); $map=['inicio'=>'home','tienda'=>'shop','nosotros'=>'about','contacto'=>'contact'];
    if (!$page || empty($map[$page->post_name])) return;
    $content=do_blocks(get_post_field('post_content',$page->ID));
    status_header(200); nocache_headers(); ?><!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?php echo esc_html(get_the_title($page)); ?> · MiniKids Peru</title><style>html,body{width:100%;min-height:100%;margin:0;padding:0;background:#fff}body .site-shell{display:block;width:100%;min-height:100vh;margin:0;padding:0}</style><?php wp_head(); ?></head><body <?php body_class('mkp-page'); ?>><?php wp_body_open(); ?><div class="site-shell"><?php echo mkp_header(); echo $content; echo mkp_footer(); ?></div><?php wp_footer(); ?></body></html><?php exit;
}
add_action('template_redirect','mkp_page_shell',1);
