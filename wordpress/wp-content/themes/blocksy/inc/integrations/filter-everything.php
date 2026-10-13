<?php

add_filter('wpc_theme_posts_container', function ($selector) {
	return '#main > .ct-container > section';
}, 20);
