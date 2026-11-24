<?php
/**
 * Plugin Name: MiniKids · Tienda y campañas
 * Description: Presentación comercial sobre bloques nativos, menús de WordPress y catálogo WooCommerce.
 * Version: 5.0.0-a
 */
if (!defined('ABSPATH')) exit;

function mkr_shop_url(){ $page=get_page_by_path('tienda');return $page?get_permalink($page):home_url('/tienda/'); }

add_action('wp_enqueue_scripts',function(){
    wp_enqueue_style('minikids-retail',plugins_url('minikids-retail.css',__FILE__),['minikids-bloques'],filemtime(__DIR__.'/minikids-retail.css'));
    wp_enqueue_script('minikids-retail',plugins_url('minikids-retail.js',__FILE__),['jquery','minikids-cart-drawer','minikids-catalogo'],filemtime(__DIR__.'/minikids-retail.js'),true);
    wp_localize_script('minikids-catalogo','minikidsCatalog',['shopUrl'=>mkr_shop_url()]);
},90);
add_filter('body_class',function($classes){$classes[]='minikids-retail';if(is_page('tienda'))$classes[]='mkr-catalog-page';return $classes;});
add_filter('woocommerce_sale_flash',function($html,$post,$product){
    if(!is_page('tienda')||!$product)return $html;
    $normal=(float)$product->get_regular_price();$sale=(float)$product->get_sale_price();
    if($normal>0&&$sale>0&&$sale<$normal)return '<span class="onsale">−'.esc_html((string)floor(($normal-$sale)/$normal*100)).'%</span>';
    return $html;
},30,3);

function mkr_icon($name){
    $paths=[
        'search'=>'<circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/>',
        'user'=>'<circle cx="12" cy="7" r="4"/><path d="M4 22v-3a8 8 0 0 1 16 0v3"/>',
        'cart'=>'<path d="M2 3h3l2.3 12h12l2-8H6"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>',
        'menu'=>'<path d="M3 6h18M3 12h18M3 18h18"/>',
        'truck'=>'<path d="M1 5h13v12H1zM14 9h4l4 4v4h-8"/><circle cx="5" cy="18" r="3"/><circle cx="18" cy="18" r="3"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name]??'').'</svg>';
}

add_action('customize_register',function($c){
    $c->add_section('minikids_retail',['title'=>'MiniKids · Cabecera de tienda','priority'=>35]);
    foreach(['notice'=>['Aviso superior','Envío gratis desde S/ 149 · Envíos a todo el Perú'],'search'=>['Texto del buscador','¿Qué juguete estás buscando?']]as $key=>$value){
        $c->add_setting('mkr_'.$key,['default'=>$value[1],'sanitize_callback'=>'sanitize_text_field']);
        $c->add_control('mkr_'.$key,['label'=>$value[0],'section'=>'minikids_retail','type'=>'text']);
    }
});

function mkr_cart_count(){return WC()->cart?WC()->cart->get_cart_contents_count():0;}
add_filter('woocommerce_add_to_cart_fragments',function($fragments){
    $n=mkr_cart_count();$fragments['span.mkr-cart-count']='<span class="mkr-cart-count" aria-label="'.esc_attr($n.' productos en el carrito').'">'.esc_html($n).'</span>';return $fragments;
});

add_action('wp_body_open',function(){
    if(is_admin()||!function_exists('WC'))return;
    $shop=mkr_shop_url();$logo=get_theme_mod('custom_logo');
    $logo_url=$logo?wp_get_attachment_image_url($logo,'full'):content_url('/uploads/minikids/logo-minikids.png');
    if(function_exists('mkv5_webp'))$logo_url=mkv5_webp($logo_url);
    if(function_exists('mka_webp_url'))$logo_url=mka_webp_url($logo_url);
    ?>
    <div class="mkr-service"><div class="mkr-wrap"><?php echo mkr_icon('truck');?><span><?php echo esc_html(get_theme_mod('mkr_notice','Envío gratis desde S/ 149 · Envíos a todo el Perú'));?></span><a href="<?php echo esc_url(home_url('/contacto/'));?>">¿Necesitas ayuda?</a></div></div>
    <header class="mkr-header">
      <div class="mkr-wrap mkr-header-main">
        <a class="mkr-brand" href="<?php echo esc_url(home_url('/'));?>" aria-label="MiniKids, ir al inicio"><img src="<?php echo esc_url($logo_url);?>" alt="MiniKids" width="164" height="74" decoding="async" fetchpriority="high"/></a>
        <div class="mkr-category-control"><button type="button" class="mkr-category-toggle" aria-expanded="false" aria-controls="mkr-category-panel"><?php echo mkr_icon('menu');?>Categorías</button>
          <nav id="mkr-category-panel" class="mkr-category-panel" aria-label="Categorías y navegación de MiniKids" hidden>
            <?php foreach(get_terms(['taxonomy'=>'product_cat','hide_empty'=>true,'exclude'=>[get_option('default_product_cat')]])as $term):?>
             <a href="<?php echo esc_url(add_query_arg('cat',$term->slug,$shop));?>"><?php echo esc_html($term->name);?></a>
            <?php endforeach;?><a href="<?php echo esc_url($shop);?>">Ver todos los juguetes</a>
            <div class="mkr-main-nav"><?php wp_nav_menu(['theme_location'=>'menu_1','container'=>false,'depth'=>1,'fallback_cb'=>false,'menu_class'=>'mkr-menu']);?></div>
          </nav>
        </div>
        <form class="mkr-search" role="search" method="get" action="<?php echo esc_url(home_url('/'));?>">
          <label for="mkr-search-input" class="screen-reader-text">Buscar juguetes</label>
          <input id="mkr-search-input" type="search" name="s" placeholder="<?php echo esc_attr(get_theme_mod('mkr_search','¿Qué juguete estás buscando?'));?>" value="<?php echo esc_attr(get_search_query());?>"/>
          <button type="submit" aria-label="Buscar juguetes"><?php echo mkr_icon('search');?></button>
        </form>
        <div class="mkr-actions">
          <a href="<?php echo esc_url(is_user_logged_in()?wc_get_account_endpoint_url('orders'):wc_get_page_permalink('myaccount'));?>" class="mkr-orders"<?php echo is_user_logged_in()?'':' data-requires-login="1"';?>><?php echo mkr_icon('truck');?><span>Mis pedidos</span></a>
          <a href="<?php echo esc_url(wc_get_cart_url());?>" class="mkr-cart" aria-label="Abrir mi carrito"><?php echo mkr_icon('cart');?><span class="mkr-cart-count" aria-label="<?php echo esc_attr(mkr_cart_count().' productos en el carrito');?>"><?php echo esc_html(mkr_cart_count());?></span><span class="mkr-cart-label">Mi carrito</span></a>
          <a href="<?php echo esc_url(mk12_account_url());?>" class="mkr-account"><?php echo mkr_icon('user');?><span>Mi cuenta</span></a>
        </div>
      </div>
    </header>
    <?php
},20);

add_action('wp_footer',function(){
    $id=(int)get_option('mkr_footer_block_id');if(!$id)return;
    $p=get_post($id);if(!$p||$p->post_status!=='publish')return;
    echo '<footer class="mkr-footer" aria-label="Información y atención de MiniKids">'.do_blocks($p->post_content).'</footer>';
},30);

add_action('admin_menu',function(){
    add_theme_page('MiniKids · Diseño','MiniKids · Diseño','edit_theme_options','minikids-retail',function(){
      $footer=(int)get_option('mkr_footer_block_id');$home=(int)get_option('page_on_front');
      echo '<div class="wrap"><h1>Diseño de MiniKids</h1><p>El contenido comercial se mantiene con las herramientas de WordPress y WooCommerce.</p><ul>';
      foreach(['Portada: imágenes publicitarias y sus enlaces'=>get_edit_post_link($home),'Pie de página: enlaces y contenido'=>get_edit_post_link($footer),'Menú principal'=>admin_url('nav-menus.php'),'Aviso y buscador de la cabecera'=>admin_url('customize.php?autofocus[section]=minikids_retail'),'Productos, precios e inventario'=>admin_url('edit.php?post_type=product')]as $label=>$url){
        echo '<li style="margin-bottom:18px"><a class="button" href="'.esc_url($url).'">'.esc_html($label).'</a></li>';
      }
      echo '</ul><p>La portada contiene imágenes enlazadas. Cada campaña se cambia desde el bloque Imagen y Medios; su destino se cambia desde el enlace del mismo bloque. Las versiones de escritorio y móvil están identificadas en la Vista de lista.</p><p>El catálogo utiliza precios y existencias de WooCommerce. Si cambia un precio anunciado, sustituya también la imagen de esa campaña.</p></div>';
    });
});
