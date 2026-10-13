<?php

add_action('parse_tax_query', function ($query) {
	if (is_admin() || ! $query->is_main_query()) {
		return;
	}

	if (! (
		is_home() || is_archive() || is_search()
	)) {
		return;
	}

	if (function_exists('is_woocommerce')) {
		if (is_woocommerce()) {
			return;
		}
	}

	if ($query->get('post_type') === 'product') {
		return;
	}

	$prefix = blocksy_manager()->screen->get_prefix();

	if (
		$prefix === 'bbpress_single'
		||
		(
			$prefix === 'courses_archive'
			&&
			function_exists('tutor')
		)
	) {
		return;
	}

	$prefix = blocksy_manager()->screen->get_prefix([
		'allowed_prefixes' => [
			'blog',
			'categories',
			'woo_categories',
			'search',
			'author'
		],
		'default_prefix' => 'blog'
	]);

	$query->set(
		'posts_per_page',
		intval(blocksy_get_theme_mod(
			$prefix . '_archive_per_page',
			get_option('posts_per_page', 10)
		))
	);

	// Posts that share an identical `post_date` produce an undefined SQL
	// order that isn't stable across separate LIMIT/OFFSET queries, so a
	// post can surface on two consecutive pages while another never appears.
	// Add `ID` as a deterministic tiebreaker to make the sort total and
	// stable. Only do this when ordering by date (the default), so search
	// relevance and any explicit custom orderby are left untouched.
	$current_orderby = $query->get('orderby');

	if (
		empty($current_orderby)
		||
		$current_orderby === 'date'
		||
		$current_orderby === 'post_date'
	) {
		$current_order = strtoupper($query->get('order')) === 'ASC'
			? 'ASC'
			: 'DESC';

		// Append the tiebreaker at the SQL level rather than setting the
		// `orderby` query var to an associative array. WooCommerce's
		// `get_catalog_ordering_args()` (used by the `[products]` shortcode)
		// falls back to `get_query_var('orderby')` from the main query, and
		// assumes a string / numeric array — an associative array triggers
		// "Undefined array key 0" in class-wc-query.php. Filtering the SQL keeps
		// the query var untouched, so that fallback still reads a clean string.
		add_filter(
			'posts_orderby',
			function ($orderby_sql, $wp_query) use ($query, $current_order) {
				if ($wp_query !== $query) {
					return $orderby_sql;
				}

				global $wpdb;

				$tiebreaker = $wpdb->posts . '.ID ' . $current_order;

				if (empty($orderby_sql)) {
					return $tiebreaker;
				}

				return $orderby_sql . ', ' . $tiebreaker;
			},
			10,
			2
		);
	}
});

if (! function_exists('blocksy_get_listing_card_type')) {
	function blocksy_get_listing_card_type($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'prefix' => blocksy_manager()->screen->get_prefix()
			]
		);

		$blog_post_structure = blocksy_listing_page_structure([
			'prefix' => $args['prefix']
		]);

		if ($blog_post_structure === 'gutenberg') {
			return '';
		}

		$card_type = blocksy_get_theme_mod($args['prefix'] . '_card_type', 'boxed');

		if ($card_type === 'cover') {
			if ($blog_post_structure === 'simple') {
				$card_type = 'boxed';
			}
		}

		/**
		 * Filters the resolved card type for a posts listing.
		 *
		 * @since 2.1.47
		 *
		 * @param string $card_type Card type (e.g. 'boxed', 'cover', 'simple').
		 */
		return apply_filters('blocksy:posts-listing:card_type', $card_type);
	}
}

if (! function_exists('blocksy_listing_page_structure')) {
	function blocksy_listing_page_structure($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'prefix' => blocksy_manager()->screen->get_prefix()
			]
		);


		$blog_post_structure = blocksy_get_theme_mod(
			$args['prefix'] . '_structure',
			'grid'
		);

		/**
		 * Filters the resolved page structure for a posts listing.
		 *
		 * @since 2.1.47
		 *
		 * @param string $blog_post_structure Listing structure (e.g. 'grid', 'simple', 'gutenberg').
		 * @param string $prefix              The current listing prefix.
		 */
		return apply_filters(
			'blocksy:posts-listing:structure',
			$blog_post_structure,
			$args['prefix']
		);
	}
}

if (! function_exists('blocksy_cards_get_deep_link')) {
	function blocksy_generic_get_deep_link($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'suffix' => '',
				'prefix' => null,
				'shortcut' => 'border:outside',
				'return' => 'string'
			]
		);

		if (! $args['prefix']) {
			$args['prefix'] = blocksy_manager()->screen->get_prefix();
		}

		$attr = [];

		if (is_customize_preview()) {
			$attr['data-shortcut'] = $args['shortcut'];
			$attr['data-shortcut-location'] = blocksy_first_level_deep_link(
				$args['prefix']
			);

			if (! empty($args['suffix'])) {
				$attr['data-shortcut-location'] .= ':' . $args['suffix'];
			}
		}

		if ($args['return'] === 'array') {
			return $attr;
		}

		return blocksy_attr_to_html($attr);
	}
}

