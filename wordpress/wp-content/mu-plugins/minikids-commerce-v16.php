<?php
/** Plugin Name: MiniKids · Reseñas modal, estrellas y zoom V16 */
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'minikids-commerce-v16',
        plugins_url('minikids-commerce-v16.css', __FILE__),
        ['minikids-commerce-v15'],
        filemtime(__DIR__ . '/minikids-commerce-v16.css')
    );
    wp_enqueue_script(
        'minikids-commerce-v16',
        plugins_url('minikids-commerce-v16.js', __FILE__),
        ['jquery', 'minikids-commerce-v12'],
        filemtime(__DIR__ . '/minikids-commerce-v16.js'),
        true
    );
    if (is_product()) {
        wp_dequeue_script('zoom');
    }
}, 200);

// Disable Blocksy's built-in card rating at the root so cards render one compact rating row.
add_filter('theme_mod_woo_card_layout', function ($layout) {
    if (!is_array($layout)) {
        return $layout;
    }
    foreach ($layout as &$item) {
        if (($item['id'] ?? '') === 'product_rating') {
            $item['enabled'] = false;
        }
    }
    return $layout;
}, 20);

// Replace Woo's native hover zoom on single product pages with our own smooth hover zoom, keeping PhotoSwipe lightbox.
add_filter('theme_mod_has_product_single_zoom', function ($value) {
    return is_product() ? 'no' : $value;
}, 20);
add_filter('theme_mod_has_product_single_lightbox', function ($value) {
    return is_product() ? 'yes' : $value;
}, 20);
add_action('wp', function () {
    if (is_product()) {
        remove_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
    }
}, 50);

// Ensure high-res (1200/full) attribute is present on single-product gallery images.
add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment) {
    if (!is_product() || !$attachment) {
        return $attr;
    }
    $full = wp_get_attachment_image_url($attachment->ID, 'full');
    if ($full) {
        $attr['data-large_image'] = $full;
    }
    return $attr;
}, 20, 2);

// Replace the emoji magnifying glass in Blocksy's gallery trigger with a crisp SVG icon.
add_filter('woocommerce_single_product_image_thumbnail_html', function ($html) {
    if (!str_contains($html, 'woocommerce-product-gallery__trigger')) {
        return $html;
    }
    $svg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/><path d="M11 8v6M8 11h6"/></svg>';
    return preg_replace(
        '~<a\b[^>]*class="woocommerce-product-gallery__trigger"[^>]*>.*?</a>~su',
        '<a href="#" class="woocommerce-product-gallery__trigger" aria-label="Ampliar imagen">' . $svg . '</a>',
        $html,
        1
    );
}, 30);

function mk16_stars_html($rating, $size = 14) {
    $pct = round(max(0.0, min(5.0, (float) $rating)) / 5 * 100, 2);
    $star = '<svg viewBox="0 0 24 24" width="' . (int) $size . '" height="' . (int) $size . '" aria-hidden="true"><path d="M12 2.2l2.92 6.08 6.68.93-4.86 4.7 1.17 6.64L12 17.38l-5.91 3.17 1.17-6.64-4.86-4.7 6.68-.93z"/></svg>';
    $five = str_repeat($star, 5);
    return '<span class="mk16-stars" style="--mk16-star-size:' . (int) $size . 'px" aria-hidden="true">'
        . '<span class="mk16-stars-bg">' . $five . '</span>'
        . '<span class="mk16-stars-fg" style="width:' . esc_attr((string) $pct) . '%">' . $five . '</span>'
        . '</span>';
}

function mk16_card_rating() {
    global $product;
    if (!$product || !$product->get_rating_count()) {
        return;
    }
    $avg_num = (float) $product->get_average_rating();
    $avg = number_format($avg_num, 1, '.', '');
    $count = (int) $product->get_review_count();
    $word = $count === 1 ? 'comentario' : 'comentarios';
    echo '<div class="mk16-card-rating" aria-label="' . esc_attr($avg . ' de 5, ' . $count . ' ' . $word) . '">'
        . mk16_stars_html($avg_num, 14)
        . '<span class="mk16-card-avg">' . esc_html($avg) . '</span>'
        . '<span class="mk16-card-count">(' . esc_html((string) $count) . ')</span>'
        . '</div>';
}

function mk16_single_rating_trigger() {
    global $product;
    if (!$product) {
        return;
    }
    $count = (int) $product->get_review_count();
    if ($count > 0) {
        $avg_num = (float) $product->get_average_rating();
        $avg = number_format($avg_num, 1, '.', '');
        $word = $count === 1 ? 'comentario' : 'comentarios';
        echo '<div class="mk16-single-rating">'
            . mk16_stars_html($avg_num, 16)
            . '<span class="mk16-single-summary">' . esc_html($avg . ' · ' . $count . ' ' . $word) . '</span>'
            . '<button type="button" class="mk16-reviews-open" data-mk16-reviews-open aria-haspopup="dialog" aria-controls="mk16-reviews-dialog">Ver comentarios (' . esc_html((string) $count) . ')</button>'
            . '</div>';
        return;
    }
    echo '<div class="mk16-single-rating mk16-single-rating--empty">'
        . '<button type="button" class="mk16-reviews-open" data-mk16-reviews-open aria-haspopup="dialog" aria-controls="mk16-reviews-dialog">Sin comentarios todavía</button>'
        . '</div>';
}

function mk16_relative_date($mysql_date) {
    $ts = strtotime($mysql_date);
    if (!$ts) {
        return '';
    }
    $diff = max(0, (int) current_time('timestamp') - $ts);
    if ($diff < 3600) {
        return 'hace unos minutos';
    }
    if ($diff < DAY_IN_SECONDS) {
        $h = max(1, (int) floor($diff / 3600));
        return 'hace ' . $h . ($h === 1 ? ' hora' : ' horas');
    }
    if ($diff < 7 * DAY_IN_SECONDS) {
        $d = (int) floor($diff / DAY_IN_SECONDS);
        return $d === 1 ? 'ayer' : 'hace ' . $d . ' días';
    }
    if ($diff < 30 * DAY_IN_SECONDS) {
        $w = max(1, (int) floor($diff / (7 * DAY_IN_SECONDS)));
        return 'hace ' . $w . ($w === 1 ? ' semana' : ' semanas');
    }
    if ($diff < YEAR_IN_SECONDS) {
        $m = max(1, (int) floor($diff / (30 * DAY_IN_SECONDS)));
        return 'hace ' . $m . ($m === 1 ? ' mes' : ' meses');
    }
    $y = max(1, (int) floor($diff / YEAR_IN_SECONDS));
    return 'hace ' . $y . ($y === 1 ? ' año' : ' años');
}

function mk16_author_initials($name) {
    $parts = preg_split('/\s+/u', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
    if (!$parts) {
        return 'U';
    }
    $first = mb_substr($parts[0], 0, 1, 'UTF-8');
    $second = isset($parts[1]) ? mb_substr(rtrim($parts[1], '.'), 0, 1, 'UTF-8') : '';
    return mb_strtoupper($first . $second, 'UTF-8');
}

function mk16_review_has_photo($comment) {
    if (preg_match('/<img\b/i', (string) $comment->comment_content)) {
        return true;
    }
    $meta_photo = get_comment_meta($comment->comment_ID, 'review_images', true);
    return !empty($meta_photo);
}

add_action('wp_footer', function () {
    if (!is_product()) {
        return;
    }
    global $product;
    if (!$product || !comments_open($product->get_id())) {
        return;
    }
    $pid = $product->get_id();
    $all_reviews = get_comments([
        'post_id' => $pid,
        'type'    => 'review',
        'status'  => 'approve',
        'orderby' => 'comment_date_gmt',
        'order'   => 'DESC',
        'number'  => 0,
    ]);
    $total = count($all_reviews);
    $counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    $sum = 0;
    $has_photos = false;
    foreach ($all_reviews as $c) {
        $r = (int) get_comment_meta($c->comment_ID, 'rating', true);
        if ($r >= 1 && $r <= 5) {
            $counts[$r]++;
            $sum += $r;
        }
        if (!$has_photos && mk16_review_has_photo($c)) {
            $has_photos = true;
        }
    }
    $rated_total = array_sum($counts);
    $avg_num = $rated_total ? ($sum / $rated_total) : (float) $product->get_average_rating();
    $avg_text = number_format($avg_num, 1, '.', '');

    $per_page = 20;
    $total_pages = max(1, (int) ceil($total / $per_page));
    $raw_page = isset($_GET['cpage']) ? (int) $_GET['cpage'] : (int) get_query_var('cpage', 1);
    $cpage = max(1, min($total_pages, $raw_page));
    $page_reviews = array_slice($all_reviews, ($cpage - 1) * $per_page, $per_page);
    $show_verified = get_option('woocommerce_review_rating_verification_label') === 'yes';

    echo '<dialog id="mk16-reviews-dialog" class="mk8-dialog mk16-reviews-dialog" role="dialog" tabindex="-1" aria-modal="true" aria-labelledby="mk16-reviews-title">';
    echo '<button type="button" class="mk8-close mk16-reviews-close" aria-label="Cerrar"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>';
    echo '<h2 id="mk16-reviews-title">Comentarios (' . esc_html((string) $total) . ')</h2>';

    echo '<div class="mk16-reviews-overview">';
    echo '<div class="mk16-overview-score"><strong class="mk16-big-avg">' . esc_html($avg_text) . '</strong>'
        . mk16_stars_html($avg_num, 18)
        . '<span class="mk16-overview-count">' . esc_html($total . ($total === 1 ? ' comentario' : ' comentarios')) . '</span></div>';
    echo '<div class="mk16-overview-bars" role="group" aria-label="Filtrar por calificación">';
    for ($star = 5; $star >= 1; $star--) {
        $c_star = $counts[$star];
        $bar_pct = $rated_total ? round($c_star / $rated_total * 100, 1) : 0;
        echo '<button type="button" class="mk16-bar-btn" data-mk16-filter="' . (int) $star . '" aria-pressed="false">'
            . '<span class="mk16-bar-label">' . (int) $star . ($star === 1 ? ' estrella' : ' estrellas') . '</span>'
            . '<span class="mk16-bar-track"><span style="width:' . esc_attr((string) $bar_pct) . '%"></span></span>'
            . '<span class="mk16-bar-num">' . esc_html((string) $c_star) . '</span>'
            . '</button>';
    }
    echo '</div></div>';

    echo '<div class="mk16-filter-pills" role="group" aria-label="Filtros de comentarios">';
    echo '<button type="button" class="mk16-pill is-active" data-mk16-filter="all" aria-pressed="true">Todos</button>';
    if ($has_photos) {
        echo '<button type="button" class="mk16-pill" data-mk16-filter="photos" aria-pressed="false">Con fotos</button>';
    }
    for ($star = 5; $star >= 1; $star--) {
        echo '<button type="button" class="mk16-pill" data-mk16-filter="' . (int) $star . '" aria-pressed="false">' . (int) $star . ($star === 1 ? ' estrella' : ' estrellas') . '</button>';
    }
    echo '</div>';

    echo '<div class="mk16-reviews-list-wrap">';
    if ($page_reviews) {
        echo '<ol class="mk16-reviews-list">';
        foreach ($page_reviews as $idx => $c) {
            $r = (int) get_comment_meta($c->comment_ID, 'rating', true);
            $photo = mk16_review_has_photo($c) ? '1' : '0';
            $verified = $show_verified && wc_review_is_from_verified_owner($c->comment_ID);
            $rel = mk16_relative_date($c->comment_date);
            $initials = mk16_author_initials($c->comment_author);
            $hidden_attr = $idx >= 8 ? ' hidden' : '';
            echo '<li id="comment-' . (int) $c->comment_ID . '" class="mk16-review-item" data-rating="' . (int) $r . '" data-has-photo="' . $photo . '"' . $hidden_attr . '>';
            echo '<div class="mk16-review-head">';
            echo '<span class="mk16-avatar" aria-hidden="true">' . esc_html($initials) . '</span>';
            echo '<div class="mk16-review-meta">';
            echo '<div class="mk16-review-author-row"><strong class="mk16-author">' . esc_html($c->comment_author) . '</strong>';
            if ($verified) {
                echo '<span class="mk16-verified"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="m5 12 4 4L19 7"/></svg>Comprador verificado</span>';
            }
            echo '</div>';
            echo '<div class="mk16-review-rating-row">'
                . mk16_stars_html($r, 14)
                . ($rel ? '<time datetime="' . esc_attr(mysql2date('c', $c->comment_date_gmt)) . '">' . esc_html($rel) . '</time>' : '')
                . '</div>';
            echo '</div></div>';
            echo '<div class="mk16-review-body">' . wpautop(wp_kses_post($c->comment_content)) . '</div>';
            echo '</li>';
        }
        echo '</ol>';
        echo '<p class="mk16-filter-empty" hidden>No hay comentarios con esa calificación.</p>';
    } else {
        echo '<p class="mk16-no-reviews">Aún no hay comentarios. Sé el primero en opinar.</p>';
    }
    echo '</div>';

    if ($total > 8) {
        $show_more = count($page_reviews) > 8;
        echo '<button type="button" class="mk16-load-more" data-mk16-load-more' . ($show_more ? '' : ' hidden') . '>Cargar más</button>';
    }

    if ($total_pages > 1) {
        echo '<nav class="mk16-comment-pages" aria-label="Páginas de comentarios">';
        for ($p = 1; $p <= $total_pages; $p++) {
            $url = add_query_arg('cpage', $p, $product->get_permalink()) . '#reviews';
            if ($p === $cpage) {
                echo '<span class="page-numbers current" aria-current="page">' . (int) $p . '</span>';
            } else {
                echo '<a class="page-numbers" href="' . esc_url($url) . '">' . (int) $p . '</a>';
            }
        }
        echo '</nav>';
    }

    echo '<div class="mk16-review-form-section">';
    $can_review = get_option('woocommerce_review_rating_verification_required') === 'no'
        || wc_customer_bought_product('', get_current_user_id(), $pid);
    if ($can_review) {
        echo '<div id="review_form_wrapper"><div id="review_form">';
        $commenter = wp_get_current_commenter();
        $req = (bool) get_option('require_name_email', 1);
        $fields = [
            'author' => '<p class="comment-form-author"><label for="author">Nombre' . ($req ? ' <span class="required">*</span>' : '') . '</label><input id="author" name="author" type="text" autocomplete="name" value="' . esc_attr($commenter['comment_author']) . '" size="30"' . ($req ? ' required' : '') . '></p>',
            'email'  => '<p class="comment-form-email"><label for="email">Correo electrónico' . ($req ? ' <span class="required">*</span>' : '') . '</label><input id="email" name="email" type="email" autocomplete="email" value="' . esc_attr($commenter['comment_author_email']) . '" size="30"' . ($req ? ' required' : '') . '></p>',
        ];
        $comment_field = '';
        if (wc_review_ratings_enabled()) {
            $comment_field .= '<div class="comment-form-rating"><label for="rating" id="comment-form-rating-label">Tu valoración' . (wc_review_ratings_required() ? ' <span class="required">*</span>' : '') . '</label><select name="rating" id="rating" required>'
                . '<option value="">Valorar…</option>'
                . '<option value="5">Excelente</option>'
                . '<option value="4">Bueno</option>'
                . '<option value="3">Regular</option>'
                . '<option value="2">Podría mejorar</option>'
                . '<option value="1">Malo</option>'
                . '</select></div>';
        }
        $comment_field .= '<p class="comment-form-comment"><label for="comment">Tu comentario <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="5" required></textarea></p>';
        comment_form(apply_filters('woocommerce_product_review_comment_form_args', [
            'title_reply'         => $total ? 'Escribe tu comentario' : 'Sé la primera persona en comentar',
            'title_reply_before'  => '<h3 id="reply-title" class="comment-reply-title">',
            'title_reply_after'   => '</h3>',
            'comment_notes_after' => '',
            'label_submit'        => 'Enviar comentario',
            'logged_in_as'        => '',
            'fields'              => $fields,
            'comment_field'       => $comment_field,
        ]), $pid);
        echo '</div></div>';
    } else {
        echo '<div class="mk15-review-gate"><p>Solo los clientes que compraron este producto pueden dejar un comentario.</p>';
        if (!is_user_logged_in()) {
            echo '<a class="button" href="' . esc_url(mk12_account_url()) . '">Iniciar sesión</a>';
        }
        echo '</div>';
    }
    echo '</div>';
    if (!is_user_logged_in()) {
        echo '<span class="mk16-auth-nonces" hidden></span>';
    }
    echo '</dialog>';
}, 65);

// Keep #mk12-auth nonces synced when guest adds/removes items (which creates a WC session cookie).
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    if (!is_user_logged_in()) {
        $fragments['span.mk16-auth-nonces'] = '<span class="mk16-auth-nonces" hidden'
            . ' data-login="' . esc_attr(wp_create_nonce('woocommerce-login')) . '"'
            . ' data-register="' . esc_attr(wp_create_nonce('woocommerce-register')) . '"'
            . ' data-lost="' . esc_attr(wp_create_nonce('woocommerce-lost-password')) . '"'
            . '></span>';
    }
    return $fragments;
});
