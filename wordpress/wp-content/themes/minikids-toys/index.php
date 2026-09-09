<?php
get_header();
$products = minikids_products();
$category = isset($_GET['categoria']) ? sanitize_title(wp_unslash($_GET['categoria'])) : '';
$search = isset($_GET['buscar']) ? strtolower(sanitize_text_field(wp_unslash($_GET['buscar']))) : '';
$filtered = array_filter($products, function ($product) use ($category, $search) {
    $category_match = !$category || $category === 'lo-nuevo' || ($category === 'ofertas' ? (bool) $product['old'] : $product['slug'] === $category);
    $search_match = !$search || strpos(strtolower($product['name'] . ' ' . $product['category']), $search) !== false;
    return $category_match && $search_match;
});
?>
<main><section class="catalog-heading"><div class="container"><h1><?php echo $search ? 'Resultados de búsqueda' : ($category ? ucwords(str_replace('-', ' ', $category)) : 'Todos los juguetes'); ?></h1><p><?php echo $search ? 'Encontramos estos juguetes para ti.' : 'Elige una nueva aventura para cada día.'; ?></p></div></section><section class="section"><div class="container"><div class="catalog-toolbar"><div class="filter-pills"><a class="filter-pill <?php echo !$category ? 'active' : ''; ?>" href="<?php echo esc_url(home_url('/')); ?>">Todos</a><a class="filter-pill <?php echo $category === 'peluches' ? 'active' : ''; ?>" href="?categoria=peluches">Peluches</a><a class="filter-pill <?php echo $category === 'didacticos' ? 'active' : ''; ?>" href="?categoria=didacticos">Didácticos</a><a class="filter-pill <?php echo $category === 'ofertas' ? 'active' : ''; ?>" href="?categoria=ofertas">Ofertas</a></div><span class="section-subtitle"><?php echo count($filtered); ?> productos</span></div><?php if ($filtered) : ?><div class="product-grid"><?php foreach ($filtered as $product) { minikids_product_card($product); } ?></div><?php else : ?><div class="empty-state">No encontramos ese juguete. Prueba con “peluche”, “juego” o “bloques”.</div><?php endif; ?></div></section></main>
<?php get_footer(); ?>
