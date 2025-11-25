<?php
// Elementor lightweight integration: register a simple widget that outputs the shortcode.
// If Elementor isn't active this file does nothing.
add_action('elementor/widgets/register', function($widgets_manager){
    if (!defined('ELEMENTOR_PATH')) return;
    require_once __DIR__ . '/../assets/elementor/review-widget.php';
    if (class_exists('\RS\Elementor\Review_Widget')) {
        $widgets_manager->register(new \RS\Elementor\Review_Widget());
    }
});
