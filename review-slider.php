<?php
/*
Plugin Name: Review Slider (Modular)
Description: Customer review slider with CPT, drag-drop reorder, Elementor widget, star color and theme color settings.
Version: 3.0
Author: You
Text Domain: review-slider
*/

if (!defined('ABSPATH')) exit;

define('RS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Includes
require_once RS_PLUGIN_DIR . 'includes/post-type.php';
require_once RS_PLUGIN_DIR . 'includes/metaboxes.php';
require_once RS_PLUGIN_DIR . 'includes/admin-settings.php';
require_once RS_PLUGIN_DIR . 'includes/reorder-admin.php';
require_once RS_PLUGIN_DIR . 'includes/frontend.php';
require_once RS_PLUGIN_DIR . 'includes/elementor-widget.php';

// Activation: set default options
register_activation_hook(__FILE__, function(){
    if (get_option('rs_theme_color') === false) update_option('rs_theme_color', '#000000');
    if (get_option('rs_star_color') === false) update_option('rs_star_color', '#FFD700');
});
