<?php if (!defined('ABSPATH')) { exit; } ?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><div class="site-shell">
<?php wp_body_open(); ?>
<div class="top-strip">🚚 Envío gratis por compras mayores a S/ 149 · Compra segura y fácil</div>
<header class="site-header">
  <div class="container header-main">
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>"><span class="brand-mark">✦</span><span>minikids<small>juguetes & diversión</small></span></a>
    <form class="header-search" action="<?php echo esc_url(home_url('/')); ?>" method="get"><input type="search" name="buscar" value="<?php echo esc_attr(isset($_GET['buscar']) ? wp_unslash($_GET['buscar']) : ''); ?>" placeholder="¿Qué estás buscando hoy?" aria-label="Buscar productos"><button type="submit" aria-label="Buscar">⌕</button></form>
    <div class="header-actions"><a class="header-action" href="#"><span>♙</span><label>Mi cuenta</label></a><button class="header-action js-open-cart" type="button"><span>🛒</span><label>Carrito</label><b class="cart-count">0</b></button></div>
  </div>
  <nav class="main-nav" aria-label="Navegación principal"><div class="container"><ul class="nav-list"><li class="current"><a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a></li><li><a href="<?php echo esc_url(home_url('/?categoria=lo-nuevo')); ?>">Lo nuevo</a></li><li><a href="<?php echo esc_url(home_url('/?categoria=peluches')); ?>">Peluches</a></li><li><a href="<?php echo esc_url(home_url('/?categoria=didacticos')); ?>">Didácticos</a></li><li><a href="<?php echo esc_url(home_url('/?categoria=juegos-familiares')); ?>">Juegos familiares</a></li><li><a href="<?php echo esc_url(home_url('/?categoria=vehiculos')); ?>">Vehículos</a></li><li><a href="<?php echo esc_url(home_url('/?categoria=ofertas')); ?>">Ofertas</a></li><li><a href="#contacto">Contáctanos</a></li></ul></div></nav>
</header>
