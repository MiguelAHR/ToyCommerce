<?php
/**
 * Plugin Name: MiniKids · Estética
 * Description: Carga la paleta, tipografías Nunito + Baloo 2 + Caveat, y componentes
 *              visuales de MiniKids sobre Blocksy y WooCommerce.
 * Version: 2.0.0
 */

if (!defined('ABSPATH')) { exit; }

// 1. Fuentes Google oficiales del prototipo Next.js (Baloo 2, Nunito, Caveat)
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'minikids-fonts',
        'https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Nunito:wght@400;600;700;800;900&family=Caveat:wght@600;700&display=swap',
        array(),
        null
    );
}, 15);

// 2. Hoja de estilos principal MiniKids
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'minikids-estetica',
        plugins_url('minikids-estetica.css', __FILE__),
        array(),
        filemtime(dirname(__FILE__) . '/minikids-estetica.css')
    );
}, 20);

// 3. Hoja de ajustes de bloques
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'minikids-bloques',
        plugins_url('minikids-bloques.css', __FILE__),
        array('minikids-estetica'),
        filemtime(dirname(__FILE__) . '/minikids-bloques.css')
    );
}, 30);

// 3.1 Script interactivo del carrito flotante
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'minikids-cart-drawer',
        plugins_url('minikids-cart-drawer.js', __FILE__),
        array('jquery'),
        filemtime(dirname(__FILE__) . '/minikids-cart-drawer.js'),
        true
    );
}, 35);

// 3.2 Script interactivo del catálogo y buscador en vivo
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'minikids-catalogo',
        plugins_url('minikids-catalogo.js', __FILE__),
        array('jquery'),
        filemtime(dirname(__FILE__) . '/minikids-catalogo.js'),
        true
    );
}, 36);

// 3.3 Redirección de archivos individuales de categoría y búsquedas generales al catálogo unificado
add_action('template_redirect', function () {
    if (is_product_category()) {
        $cat = get_queried_object();
        if ($cat && isset($cat->slug)) {
            wp_safe_redirect(home_url('/tienda/?cat=' . rawurlencode($cat->slug)), 301);
            exit;
        }
    }
    if (is_search() && !is_admin()) {
        $q = get_search_query();
        if (!empty($q)) {
            wp_safe_redirect(home_url('/tienda/?q=' . rawurlencode($q)), 301);
            exit;
        }
    }
});

// 4. Optimización de peso de estilos en <head>
add_filter('should_load_separate_core_block_assets', '__return_false');

// 5. Inyección global de TopBar en TODAS las páginas (siempre en la cima, antes del header)
add_action('blocksy:header:before', function () {
    ?>
    <div class="minikids-topbar">
        <p>Atendemos de lunes a viernes de 9am a 6pm • Envíos a todo el Perú</p>
    </div>
    <?php
});

// 6. Inyección global de Footer unificado en TODAS las páginas
add_action('blocksy:footer:before', function () {
    ?>
    <footer class="minikids-site-footer">
        <div class="minikids-footer-inner">
            <div class="footer-col brand-col">
                <img src="/wordpress/wp-content/uploads/minikids/logo-minikids.png" alt="Mini Kids" class="footer-logo-img"/>
                <p class="footer-tagline">Jugar hoy,<br><span style="padding-left:14px">un mejor mañana</span> <span style="color:#ef233c">❤️</span></p>
            </div>
            <div class="footer-col">
                <h3 class="footer-heading">Tienda</h3>
                <ul class="footer-links">
                    <li><a href="/wordpress/">Inicio</a></li>
                    <li><a href="/wordpress/tienda/">Juguetes</a></li>
                    <li><a href="/wordpress/#categorias">Categorías</a></li>
                    <li><a href="/wordpress/#productos">Novedades</a></li>
                    <li><a href="/wordpress/#ofertas">Ofertas</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3 class="footer-heading">Ayuda y Empresa</h3>
                <ul class="footer-links">
                    <li><a href="/wordpress/nosotros/">¿Quiénes somos?</a></li>
                    <li><a href="/wordpress/contacto/">Contacto</a></li>
                    <li><a href="/wordpress/libro-de-reclamaciones/">Libro de Reclamaciones</a></li>
                    <li><a href="/wordpress/politica-privacidad/">Política de Privacidad</a></li>
                </ul>
                <?php if (function_exists('has_nav_menu') && has_nav_menu('minikids_atencion')) {
                    wp_nav_menu(['theme_location'=>'minikids_atencion','container'=>false,'menu_class'=>'footer-links mkp-attention-links','fallback_cb'=>false]);
                } ?>
            </div>
            <div class="footer-col">
                <h3 class="footer-heading">Síguenos</h3>
                <div class="footer-socials">
                    <a href="#" aria-label="Instagram"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg></a>
                    <a href="#" aria-label="Facebook"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
                    <a href="#" aria-label="YouTube"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><polygon points="10 15 15 12 10 9 10 15"/></svg></a>
                    <a href="#" aria-label="TikTok"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg></a>
                </div>
            </div>
            <div class="footer-col newsletter-col">
                <h3 class="footer-heading">¡No te pierdas nuestras novedades!</h3>
                <form class="footer-newsletter-form" onsubmit="event.preventDefault()">
                    <input type="email" placeholder="Tu correo electrónico" required/>
                    <button type="submit">Suscribirme</button>
                </form>
                <div class="footer-bottom-copy">
                    <p>© 2026 Mini Kids. Todos los derechos reservados. <span style="color:#ef233c">❤️</span></p>
                </div>
            </div>
        </div>
    </footer>
    <?php
});

// 7. Badges de Edad y Stock en la ficha de producto (Next.js visual parity)
add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!$product) return;
    
    $edad = $product->get_attribute('Edad recomendada');
    if (!$edad) {
        $edad = $product->get_attribute('pa_edad-recomendada');
    }
    if (!$edad) {
        $edad = get_post_meta($product->get_id(), '_minikids_edad', true);
    }
    $stock_qty = $product->get_stock_quantity();
    $stock_label = ($stock_qty !== null && $stock_qty > 0) ? ($stock_qty . ' en stock') : 'En stock';
    ?>
    <div class="minikids-product-meta-badges">
        <?php if (!empty($edad)): ?>
            <span class="meta-badge badge-age"><span class="badge-icon">👶</span> Edad: <?php echo esc_html($edad); ?></span>
        <?php endif; ?>
        <?php if ($product->is_in_stock()): ?>
            <span class="meta-badge badge-stock"><span class="badge-dot">●</span> <?php echo esc_html($stock_label); ?></span>
        <?php endif; ?>
    </div>
    <?php
}, 25);

// 8. Badges de confianza en la ficha individual de producto (debajo del botón de compra)
add_action('woocommerce_single_product_summary', function () {
    ?>
    <div class="minikids-product-trust-badges">
        <div class="trust-badge-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1a73e8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-5l-3-4h-5v10Z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>
            <span>Envío a todo el país</span>
        </div>
        <div class="trust-badge-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1a73e8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
            <span>Compra segura</span>
        </div>
        <div class="trust-badge-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1a73e8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            <span>30 días de cambio</span>
        </div>
    </div>
    <?php
}, 35);

// 8.1 Metadatos enriquecidos de la ficha (Código SKU, Categoría, Edad, Stock, Información de envío)
add_action('woocommerce_product_meta_end', function () {
    global $product;
    if (!$product) return;
    
    $edad = $product->get_attribute('Edad recomendada');
    if (!$edad) {
        $edad = get_post_meta($product->get_id(), '_minikids_edad', true);
    }
    $stock_qty = $product->get_stock_quantity();
    $stock_text = ($stock_qty !== null && $stock_qty > 0) ? ($stock_qty . ' en stock') : 'Disponible en stock';
    ?>
    <div class="minikids-meta-additional">
        <?php if (!empty($edad)): ?>
            <span class="minikids-meta-row meta-edad">
                <span class="meta-label">Edad:</span>
                <span class="meta-value">(<?php echo esc_html($edad); ?>)</span>
            </span>
        <?php endif; ?>
        <span class="minikids-meta-row meta-stock">
            <span class="meta-label">Stock:</span>
            <span class="meta-value"><?php echo esc_html($stock_text); ?></span>
        </span>
        <span class="minikids-meta-row meta-envio">
            <span class="meta-label">Información de envío:</span>
            <span class="meta-value">Envío a todo el país / Compra segura / 30 días de cambio</span>
        </span>
    </div>
    <?php
});

// 8.2 Pestañas personalizadas de la ficha de producto en español amigable
add_filter('woocommerce_product_tabs', function ($tabs) {
    if (isset($tabs['description'])) {
        $tabs['description']['title'] = '✨ Características y Cuidados';
    }
    if (isset($tabs['additional_information'])) {
        $tabs['additional_information']['title'] = '📋 Especificaciones';
    }
    if (isset($tabs['reviews'])) {
        $tabs['reviews']['title'] = '⭐ Opiniones de Padres';
    }
    return $tabs;
}, 98);

// 8.3 Encabezados de productos relacionados, upsells, cross-sells y oferta en español
add_filter('woocommerce_product_upsells_products_heading', function() {
    return 'Productos relacionados';
});
add_filter('woocommerce_product_related_products_heading', function() {
    return 'Productos relacionados';
});
add_filter('woocommerce_product_cross_sells_products_heading', function() {
    return 'También te podría interesar...';
});
add_filter('woocommerce_sale_flash', function() {
    return '<span class="onsale">OFERTA</span>';
});
add_filter('woocommerce_product_single_add_to_cart_text', function() {
    return 'Agregar al carrito';
});
add_filter('woocommerce_product_add_to_cart_text', function() {
    return 'Agregar al carrito';
});

// 8.4 Soporte de zoom y lightbox nativo para galería de productos
add_action('after_setup_theme', function () {
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
}, 50);

// 9. Barra de Envío Gratis en la página de Carrito (Next.js visual parity)
add_filter('the_content', function ($content) {
    if (is_cart() && in_the_loop() && is_main_query()) {
        $threshold = 149;
        $subtotal = 0;
        if (function_exists('WC') && WC()->cart) {
            $subtotal = (float) WC()->cart->get_subtotal();
        }
        $diff = max(0, $threshold - $subtotal);
        $percent = min(100, $threshold > 0 ? ($subtotal / $threshold) * 100 : 100);
        
        $msg = ($subtotal >= $threshold) 
            ? '<span>¡Felicidades! Tienes <strong>Envío Gratis</strong> a todo el Perú 🎉</span>' 
            : '<span>Te faltan <strong>S/ ' . number_format($diff, 2) . '</strong> para tener <strong>Envío Gratis</strong> a todo el Perú</span>';
            
        $bar = '
        <div class="minikids-shipping-progress-banner">
            <div class="shipping-progress-header">
                <span class="shipping-icon">🚚</span>
                <span class="shipping-text">' . $msg . '</span>
            </div>
            <div class="shipping-progress-track">
                <div class="shipping-progress-fill" style="width:' . $percent . '%;"></div>
            </div>
        </div>';
        
        return $bar . $content;
    }
    return $content;
});

// 9.1 Inyección del Buscador y Selector de Categorías en el Catálogo de la Tienda
add_filter('the_content', function ($content) {
    if ((is_page('tienda') || is_shop()) && in_the_loop() && is_main_query()) {
        $search_box = '
        <div class="minikids-catalog-search-container">
          <div class="minikids-catalog-search-box">
            <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" id="minikids-catalog-search" placeholder="Buscar juguetes por nombre o categoría..." autocomplete="off" />
            <button type="button" id="minikids-search-clear" aria-label="Limpiar búsqueda" style="display:none;">✕</button>
          </div>
        </div>
        <div class="minikids-cat-pills-wrap">
          <div class="minikids-category-pills">
            <button type="button" class="minikids-pill minikids-pill-active" data-cat="all">Todos</button>
            <button type="button" class="minikids-pill" data-cat="peluches">Peluches</button>
            <button type="button" class="minikids-pill" data-cat="didacticos">Didácticos y Madera</button>
            <button type="button" class="minikids-pill" data-cat="vehiculos">Vehículos</button>
            <button type="button" class="minikids-pill" data-cat="juegos-de-rol">Juegos de rol</button>
            <button type="button" class="minikids-pill" data-cat="creatividad">Creatividad</button>
            <button type="button" class="minikids-pill" data-cat="juegos-familiares">Juegos en familia</button>
            <button type="button" class="minikids-pill" data-cat="tecnologia">Tecnología</button>
            <button type="button" class="minikids-pill minikids-pill-reset" style="display:none;" title="Restablecer filtros">Limpiar filtro ✕</button>
          </div>
        </div>';
        
        $no_results = '
        <div class="minikids-no-results" style="display:none;">
          <p class="no-results-emoji">🧸</p>
          <h3 class="no-results-title">No encontramos juguetes para esa búsqueda</h3>
          <p class="no-results-desc">Prueba con otra palabra clave o explora todas las categorías.</p>
          <button type="button" class="btn-reset-catalog">Ver todos los juguetes</button>
        </div>';

        if (strpos($content, 'minikids-tienda-grid') !== false) {
            $content = str_replace('<div class="wp-block-group alignwide minikids-section minikids-tienda-grid', $search_box . '<div class="wp-block-group alignwide minikids-section minikids-tienda-grid', $content);
            $content = str_replace('</div><!-- /wp:group -->', $no_results . '</div><!-- /wp:group -->', $content);
        } else {
            $content = $search_box . $content . $no_results;
        }
    }
    return $content;
});

// 9.2 Vista de Contacto Oficial MiniKids (Alineación armónica y formulario 100% funcional)
add_filter('the_content', function ($content) {
    if (is_page('contacto') && in_the_loop() && is_main_query()) {
        $html = '
        <div class="minikids-contacto-hero">
            <h1 class="minikids-section-title">Hablemos con una <span style="color:var(--brand-blue)">sonrisa</span></h1>
            <p class="minikids-hero-cursive">¡Estamos listos para ayudarte! ❤️</p>
            <p class="minikids-contacto-desc">¿Tienes dudas sobre algún juguete, tu pedido o quieres asesoría para elegir el regalo perfecto? Nuestro equipo te responde con todo el cariño.</p>
        </div>

        <div class="minikids-contacto-container">
            <div class="minikids-contacto-grid">
                <!-- Columna Izquierda: Información y Canales Directos -->
                <div class="minikids-contacto-cards">
                    <div class="contacto-info-card card-horario">
                        <div class="contacto-card-icon bg-pastel-blue text-brand-blue">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div class="contacto-card-text">
                            <h3>Horario de Atención</h3>
                            <p><strong>Lunes a Viernes:</strong> 9:00 am – 6:00 pm<br><strong>Sábados:</strong> 9:00 am – 1:00 pm</p>
                        </div>
                    </div>

                    <div class="contacto-info-card card-canales">
                        <div class="contacto-card-icon bg-pastel-green text-brand-green">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#148043" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <div class="contacto-card-text">
                            <h3>Canales Directos</h3>
                            <p><strong>WhatsApp:</strong> +51 991 117 308<br><strong>Email:</strong> hola@minikids.pe</p>
                        </div>
                    </div>

                    <div class="contacto-info-card card-envios">
                        <div class="contacto-card-icon bg-pastel-pink text-brand-red">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef233c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-5l-3-4h-5v10Z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>
                        </div>
                        <div class="contacto-card-text">
                            <h3>Envíos y Entregas</h3>
                            <p>Despacho a todo el Perú en 2 a 5 días hábiles. <strong>Envíos gratis desde S/ 149.</strong></p>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Formulario Interactivo -->
                <div class="minikids-contacto-form-card">
                    <h2 class="form-card-title">Envíanos un mensaje 💌</h2>
                    <p class="form-card-desc">Completa tus datos y te responderemos en menos de 24 horas hábiles.</p>
                    
                    <form id="minikids-contact-form" class="contacto-form">
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="contact-name">Tu nombre *</label>
                                <input type="text" id="contact-name" name="name" placeholder="Ej. Ana García" required />
                            </div>
                            <div class="form-group">
                                <label for="contact-email">Correo electrónico *</label>
                                <input type="email" id="contact-email" name="email" placeholder="ana@ejemplo.com" required />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact-subject">Asunto</label>
                            <input type="text" id="contact-subject" name="subject" placeholder="Consulta sobre juguetes o pedidos" />
                        </div>

                        <div class="form-group">
                            <label for="contact-message">Mensaje *</label>
                            <textarea id="contact-message" name="message" rows="4" placeholder="¿En qué podemos ayudarte hoy?" required></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" id="contact-submit-btn" class="btn-submit-contacto">Enviar mensaje &gt;</button>
                            <a href="https://wa.me/51991117308?text=%C2%A1Hola%20MiniKids!%20Me%20gustar%C3%ADa%20recibir%20informaci%C3%B3n%20y%20asesor%C3%ADa%20sobre%20sus%20juguetes." target="_blank" rel="noopener noreferrer" class="btn-whatsapp-direct">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2z"/></svg>
                                <span>Chatear por WhatsApp</span>
                            </a>
                        </div>

                        <div id="contact-success-msg" style="display:none;" class="contact-feedback-success">
                            ¡Gracias por escribirnos! 💌 Hemos recibido tu mensaje y nos pondremos en contacto muy pronto.
                        </div>
                    </form>
                </div>
            </div>
        </div>';
        return $html;
    }
    return $content;
});

// 9.3 Personalización y Bienvenida en Mi Cuenta (Perfil Propio MiniKids)
add_action('woocommerce_before_account_navigation', function() {
    $current_user = wp_get_current_user();
    $name = $current_user->display_name ? $current_user->display_name : 'Amigo de MiniKids';
    ?>
    <div class="minikids-account-welcome-banner">
        <div class="welcome-badge-icon">🎈</div>
        <div class="welcome-text-wrap">
            <h2 class="welcome-name">¡Hola, <?php echo esc_html($name); ?>!</h2>
            <p class="welcome-tagline">Bienvenido a tu espacio personal en MiniKids · <em>Jugar hoy, un mejor mañana</em> ❤️</p>
        </div>
    </div>
    <?php
});

add_action('woocommerce_before_customer_login_form', function() {
    ?>
    <div class="minikids-account-auth-header">
        <div class="auth-icon">🧸</div>
        <h2 class="auth-title">Accede a tu cuenta MiniKids</h2>
        <p class="auth-subtitle">Inicia sesión o regístrate para revisar el estado de tus compras, cupones y direcciones de envío.</p>
    </div>
    <?php
});

// 10. Drawer de Carrito Flotante (Next.js CartDrawer visual parity)
add_action('wp_footer', function () {
    if (is_cart() || is_checkout()) {
        return;
    }
    $count = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;
    ?>
    <div id="minikids-cart-drawer" class="minikids-cart-drawer" aria-hidden="true">
        <div class="minikids-cart-drawer-backdrop"></div>
        <aside class="minikids-cart-drawer-panel" role="dialog" aria-modal="true" aria-label="Carrito de compras">
            <header class="minikids-cart-drawer-header">
                <h2 class="drawer-title">
                    Tu carrito <span class="cart-count-badge">(<?php echo esc_html($count); ?>)</span>
                </h2>
                <button type="button" class="minikids-cart-close" aria-label="Cerrar carrito">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </header>

            <div class="minikids-cart-drawer-shipping">
                <p class="shipping-notice-text">
                    <span class="shipping-icon">🚚</span> Envíos gratis a todo el Perú en compras desde S/ 149
                </p>
            </div>

            <div class="minikids-cart-drawer-body">
                <div class="widget_shopping_cart_content">
                    <?php if (function_exists('woocommerce_mini_cart')) { woocommerce_mini_cart(); } ?>
                </div>
            </div>

            <footer class="minikids-cart-drawer-footer">
                <a href="<?php echo esc_url( function_exists('wc_get_page_permalink') ? wc_get_page_permalink('cart') : '/wordpress/cart/' ); ?>" class="drawer-viewcart-btn">Ver carrito</a>
                <a href="<?php echo esc_url( function_exists('wc_get_page_permalink') ? wc_get_page_permalink('checkout') : '/wordpress/checkout/' ); ?>" class="drawer-checkout-btn">Finalizar compra &gt;</a>
                <button type="button" class="drawer-continue-btn">Seguir comprando</button>
            </footer>
        </aside>
    </div>
    <?php
}, 50);

// 9.4 Registro del campo 'Número de celular' en Contact Information del Checkout (WooCommerce Blocks & Classic)
add_action('woocommerce_init', function() {
    // Evitar campo de teléfono duplicado en la sección de dirección de envío/facturación
    update_option('woocommerce_checkout_phone_field', 'hidden');

    if (function_exists('woocommerce_register_additional_checkout_field')) {
        try {
            woocommerce_register_additional_checkout_field(array(
                'id'                         => 'minikids/celular',
                'label'                      => __('Número de celular', 'woocommerce'),
                'optionalLabel'              => __('Número de celular (opcional)', 'woocommerce'),
                'location'                   => 'contact',
                'type'                       => 'text',
                'required'                   => true,
                'attributes'                 => array(
                    'autocomplete' => 'tel',
                    'placeholder'  => 'Ej. 991 117 380',
                    'type'         => 'tel',
                    'aria-label'   => 'Número de celular',
                ),
                'validate_callback'          => function($value, $field) {
                    $clean = preg_replace('/[^\d]/', '', (string)$value);
                    if (strlen($clean) < 8 || strlen($clean) > 15) {
                        return new WP_Error('invalid_phone', __('Por favor ingresa un número de celular válido (ej. 991 117 308 o +51 991 117 308).', 'woocommerce'));
                    }
                    return true;
                },
                'show_in_order_confirmation' => true,
            ));
        } catch (\Throwable $e) {
            // Ya registrado previamente
        }
    }
});

// Sincronizar 'minikids/celular' al campo oficial billing_phone del pedido para compatibilidad total
add_action('woocommerce_checkout_order_created', function($order) {
    if (!$order instanceof WC_Order) return;
    $celular = '';
    if (function_exists('woocommerce_get_order_additional_field_value')) {
        $celular = woocommerce_get_order_additional_field_value($order->get_id(), 'minikids/celular');
    }
    if (empty($celular)) {
        $celular = $order->get_meta('_minikids/celular');
    }
    if (!empty($celular)) {
        $order->set_billing_phone(sanitize_text_field($celular));
        $order->save();
    }
});

add_action('woocommerce_store_api_checkout_update_order_from_request', function($order, $request) {
    if (!$order instanceof WC_Order) return;
    $additional_fields = $request->get_param('additional_fields');
    $celular = '';
    if (is_array($additional_fields) && !empty($additional_fields['minikids/celular'])) {
        $celular = sanitize_text_field($additional_fields['minikids/celular']);
    } elseif ($order->get_meta('_minikids/celular')) {
        $celular = $order->get_meta('_minikids/celular');
    }
    if (!empty($celular)) {
        $order->set_billing_phone($celular);
    }
}, 10, 2);

// Compatibilidad con checkout clásico (si se visualiza sin bloques)
add_filter('woocommerce_checkout_fields', function($fields) {
    if (isset($fields['billing']['billing_phone'])) {
        $fields['billing']['billing_phone']['label'] = 'Número de celular';
        $fields['billing']['billing_phone']['placeholder'] = 'Ej. 991 117 380';
        $fields['billing']['billing_phone']['priority'] = 25;
        $fields['billing']['billing_phone']['required'] = true;
    }
    return $fields;
});

// 11. Sincronización automática de códigos SKU estandarizados, stock e imagen de la matriz principal
add_action('woocommerce_init', function() {
    if (get_option('minikids_matrix_sku_stock_v2') === 'done') {
        return;
    }
    if (!function_exists('wc_get_product')) {
        return;
    }

    // Actualizar attachment 49 a kit.webp si existe en uploads/2026/10/kit.webp
    $upload_dir = wp_upload_dir();
    $kit_webp = $upload_dir['basedir'] . '/2026/10/kit.webp';
    if (file_exists($kit_webp)) {
        update_post_meta(49, '_wp_attached_file', '2026/10/kit.webp');
        wp_update_post(array(
            'ID'             => 49,
            'post_mime_type' => 'image/webp',
        ));
    }

    $matrix_updates = array(
        42  => array('sku' => 'MK-CRE-ART-04A-001', 'stock' => 9),  // Kit de arte pequeños artistas
        95  => array('sku' => 'MK-PEL-CON-00A-002', 'stock' => 14), // Peluche Conejita Rosa
        135 => array('sku' => 'MK-DID-ABA-02A-003', 'stock' => 15), // Ábaco Pedagógico y Laberinto de Madera
        89  => array('sku' => 'MK-DID-BLQ-02A-004', 'stock' => 18), // Set de Bloques de Madera 50 Piezas
        133 => array('sku' => 'MK-ROL-MUN-01A-002', 'stock' => 15), // Muñeca de Trapo Dulces Sueños
    );

    foreach ($matrix_updates as $pid => $cfg) {
        $product = wc_get_product($pid);
        if (!$product) {
            continue;
        }
        try {
            $product->set_sku($cfg['sku']);
        } catch (\Throwable $e) {
            update_post_meta($pid, '_sku', $cfg['sku']);
        }
        $product->set_manage_stock(true);
        $product->set_stock_quantity($cfg['stock']);
        $product->set_stock_status('instock');
        $product->save();
    }

    update_option('minikids_matrix_sku_stock_v2', 'done');
}, 20);




