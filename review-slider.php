<?php
/**
 * Plugin Name: Review Slider
 * Description: A simple and clean review slider.
 * Version: 3.1
 * Author: Your Name
 * Text Domain: review-slider
 */

if (!defined('ABSPATH')) {
    exit;
}

define('RS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RS_PLUGIN_URL', plugin_dir_url(__FILE__));

class Review_Slider {

    private $text_domain = 'review-slider';

    public function __construct() {
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_text_domain'));

        // Register post type
        add_action('init', array($this, 'register_post_type'));

        // Add thumbnail support
        add_action('after_setup_theme', array($this, 'add_thumbnail_support'));

        // Add metaboxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_post'));

        // Admin settings
        add_action('admin_menu', array($this, 'admin_settings_menu'));

        // Reorder admin
        add_action('admin_menu', array($this, 'reorder_admin_menu'));
        add_action('wp_ajax_rs_save_order', array($this, 'save_order'));

        // Frontend
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('review_slider', array($this, 'slider_shortcode'));

        // Elementor widget
        add_action('elementor/widgets/register', array($this, 'register_elementor_widget'));
        add_action('elementor/editor/before_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
    }

    public function load_text_domain() {
        load_plugin_textdomain($this->text_domain, false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function register_post_type() {
        $labels = [
            'name' => __('Reviews', $this->text_domain),
            'singular_name' => __('Review', $this->text_domain),
        ];
        register_post_type('review_slider', [
            'labels' => $labels,
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-testimonial',
            'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
        ]);
    }

    public function add_thumbnail_support() {
        add_image_size('rs-review-thumb', 120, 120, true);
    }

    public function add_meta_boxes() {
        add_meta_box('rs_details', __('Review Details', $this->text_domain), array($this, 'meta_box_cb'), 'review_slider', 'normal', 'high');
    }

    public function meta_box_cb($post) {
        wp_nonce_field('rs_save_meta', 'rs_meta_nonce');
        $meta = get_post_meta($post->ID);
        $image = $meta['rs_image'][0] ?? '';
        $customer_info = $meta['rs_customer_info'][0] ?? '';
        $stars = $meta['rs_stars'][0] ?? 5;
        $location = $meta['rs_location'][0] ?? '';
        ?>
        <p><label><?php _e('Customer Info', $this->text_domain); ?></label><br>
        <input type="text" name="rs_customer_info" value="<?php echo esc_attr($customer_info); ?>" style="width:100%"></p>

        <p><label><?php _e('Stars (1-5)', $this->text_domain); ?></label><br>
        <input type="number" name="rs_stars" min="1" max="5" value="<?php echo esc_attr($stars); ?>"></p>

        <p><label><?php _e('Location', $this->text_domain); ?></label><br>
        <input type="text" name="rs_location" value="<?php echo esc_attr($location); ?>" style="width:100%"></p>

        <p>
          <label><?php _e('Image', $this->text_domain); ?></label><br>
          <input type="text" id="rs_image" name="rs_image" value="<?php echo esc_attr($image); ?>" style="width:70%">
          <button class="button" id="rs_upload"><?php _e('Upload', $this->text_domain); ?></button>
        </p>
        <div id="rs_preview">
          <?php if ($image): ?><img src="<?php echo esc_url($image); ?>" style="max-width:120px;border-radius:50%" /><?php endif; ?>
        </div>

        <script>
        jQuery(function($){
            $('#rs_upload').on('click', function(e){
                e.preventDefault();
                var frame = wp.media({title: <?php echo json_encode(__('Select Image', $this->text_domain)); ?>, button:{text: <?php echo json_encode(__('Use', $this->text_domain)); ?>}, multiple:false});
                frame.open();
                frame.on('select', function(){
                    var att = frame.state().get('selection').first().toJSON();
                    $('#rs_image').val(att.url);
                    $('#rs_preview').html('<img src="'+att.url+'" style="max-width:120px;border-radius:50%"/>');
                });
            });
        });
        </script>
        <?php
    }

    public function save_post($post_id) {
        if (!isset($_POST['rs_meta_nonce']) || !wp_verify_nonce($_POST['rs_meta_nonce'], 'rs_save_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (get_post_type($post_id) !== 'review_slider' || !current_user_can('edit_post', $post_id)) {
            return;
        }

        update_post_meta($post_id, 'rs_image', sanitize_text_field($_POST['rs_image'] ?? ''));
        update_post_meta($post_id, 'rs_customer_info', sanitize_text_field($_POST['rs_customer_info'] ?? ''));
        update_post_meta($post_id, 'rs_stars', intval($_POST['rs_stars'] ?? 5));
        update_post_meta($post_id, 'rs_location', sanitize_text_field($_POST['rs_location'] ?? ''));
    }

    public function admin_settings_menu() {
        add_submenu_page('edit.php?post_type=review_slider', __('Slider Settings', $this->text_domain), __('Slider Settings', $this->text_domain), 'manage_options', 'rs_settings', array($this, 'settings_page'));
    }

    public function settings_page() {
        if (isset($_POST['rs_save_settings'])) {
            check_admin_referer('rs_settings_nonce');
            update_option('rs_theme_color', sanitize_hex_color($_POST['rs_theme_color'] ?? '#000'));
            update_option('rs_star_color', sanitize_hex_color($_POST['rs_star_color'] ?? '#FFD700'));
            echo '<div class="updated"><p>' . __('Saved.', $this->text_domain) . '</p></div>';
        }

        $theme = get_option('rs_theme_color', '#000');
        $star = get_option('rs_star_color', '#FFD700');
        ?>
        <div class="wrap">
          <h1><?php _e('Review Slider Settings', $this->text_domain); ?></h1>
          <form method="post">
            <?php wp_nonce_field('rs_settings_nonce'); ?>
            <table class="form-table">
              <tr><th><?php _e('Dot/Accent Color', $this->text_domain); ?></th><td><input type="color" name="rs_theme_color" value="<?php echo esc_attr($theme); ?>"></td></tr>
              <tr><th><?php _e('Star Color', $this->text_domain); ?></th><td><input type="color" name="rs_star_color" value="<?php echo esc_attr($star); ?>"></td></tr>
            </table>
            <p><button class="button button-primary" name="rs_save_settings"><?php _e('Save', $this->text_domain); ?></button></p>
          </form>
        </div>
        <?php
    }

    public function reorder_admin_menu() {
        add_submenu_page('edit.php?post_type=review_slider', __('Reorder Reviews', $this->text_domain), __('Reorder Reviews', $this->text_domain), 'manage_options', 'rs_reorder', array($this, 'reorder_page'));
    }

    public function reorder_page() {
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('rs-admin-reorder', RS_PLUGIN_URL . 'assets/js/admin-reorder.js', ['jquery', 'jquery-ui-sortable'], '1.0', true);
        wp_localize_script('rs-admin-reorder', 'rsReorder', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('rs_reorder')));
        wp_enqueue_style('rs-admin-css', RS_PLUGIN_URL . 'assets/css/slider.css');

        $reviews = get_posts(['post_type' => 'review_slider', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC']);
        echo '<div class="wrap"><h1>' . __('Drag to Reorder Reviews', $this->text_domain) . '</h1><ul id="rs_reorder_list" style="list-style:none;padding:0;">';
        foreach ($reviews as $r) {
            $meta = get_post_meta($r->ID);
            $img = esc_url($meta['rs_image'][0] ?? '');
            echo '<li class="rs_item" data-id="' . $r->ID . '" style="cursor:move;border:1px solid #ddd;padding:10px;margin-bottom:8px;background:#fff;">';
            echo '<strong>' . esc_html($r->post_title) . '</strong><br/>';
            if ($img) {
                echo '<img src="' . $img . '" style="width:60px;height:60px;border-radius:50%">';
            }
            echo '</li>';
        }
        echo '</ul><p><button class="button" id="rs_save_order">' . __('Save Order', $this->text_domain) . '</button></p></div>';
    }

    public function save_order() {
        check_ajax_referer('rs_reorder');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('no');
        }
        $order = $_POST['order'] ?? [];
        foreach ($order as $i => $id) {
            wp_update_post(['ID' => intval($id), 'menu_order' => intval($i)]);
        }
        wp_send_json_success();
    }

    public function enqueue_scripts() {
        wp_register_style('rs-frontend', RS_PLUGIN_URL . 'assets/css/slider.css');
        wp_register_script('rs-frontend-js', RS_PLUGIN_URL . 'assets/js/frontend.js', [], '1.0', true);
        wp_localize_script('rs-frontend-js', 'rsFront', array('theme' => get_option('rs_theme_color', '#000'), 'star' => get_option('rs_star_color', '#FFD700')));
    }

    public function slider_shortcode($atts) {
        $atts = shortcode_atts([
            'autoplay_speed' => 3000,
            'slides_to_show' => 3,
            'slides_to_show_tablet' => 2,
            'slides_to_show_mobile' => 1,
        ], $atts, 'review_slider');

        wp_enqueue_style('rs-frontend');
        wp_enqueue_script('rs-frontend-js');

        $slider_id = 'rs-' . uniqid();
        $style = "<style>
            #{$slider_id} { --slides-to-show: " . esc_attr($atts['slides_to_show']) . "; }
            @media (max-width: 992px) {
                #{$slider_id} { --slides-to-show: " . esc_attr($atts['slides_to_show_tablet']) . "; }
            }
            @media (max-width: 600px) {
                #{$slider_id} { --slides-to-show: " . esc_attr($atts['slides_to_show_mobile']) . "; }
            }
        </style>";

        $q = new WP_Query(['post_type' => 'review_slider', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC']);
        ob_start();
        echo $style;
        echo '<div id="' . $slider_id . '" class="review-slider-wrapper" data-autoplay-speed="' . esc_attr($atts['autoplay_speed']) . '"><div class="slider"><div class="slider-track">';
        while ($q->have_posts()): $q->the_post();
            $meta = get_post_meta(get_the_ID());
            $img = esc_url($meta['rs_image'][0] ?? '');
            $stars = intval($meta['rs_stars'][0] ?? 5);
            echo '<div class="slide">';
            if ($img) {
                echo '<img data-src="' . $img . '" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="rs-thumb" style="width:60px;border-radius:50%;margin-bottom:10px;"/>';
            }
            echo '<h3 class="rs-cus-name">' . get_the_title() . '</h3>';
            echo '<div class="rs-cus-info-div">' . esc_html($meta['rs_customer_info'][0] ?? '') . ' | ' . esc_html($meta['rs_location'][0] ?? '') . '</div>';
            echo '<p class="rs-stars" data-stars="' . $stars . '"></p>';
            echo '<div class="rs-text">' . get_the_content() . '</div>';
            echo '</div>';
        endwhile;
        wp_reset_postdata();
        echo '</div><div class="dots"></div></div></div>';
        return ob_get_clean();
    }

    public function register_elementor_widget($widgets_manager) {
        if (!defined('ELEMENTOR_PATH')) {
            return;
        }
        require_once __DIR__ . '/assets/elementor/review-widget.php';
        if (class_exists('\RS\Elementor\Review_Widget')) {
            $widgets_manager->register(new \RS\Elementor\Review_Widget());
        }
    }

    public function activate() {
        if (get_option('rs_theme_color') === false) {
            update_option('rs_theme_color', '#000000');
        }
        if (get_option('rs_star_color') === false) {
            update_option('rs_star_color', '#FFD700');
        }
    }
}

new Review_Slider();
