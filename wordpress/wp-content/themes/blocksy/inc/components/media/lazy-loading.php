<?php

function blocksy_lazy_zone_start() {
	blocksy_manager()->lazy_loading->start_zone();
}

function blocksy_lazy_zone_end() {
	blocksy_manager()->lazy_loading->end_zone();
}
