<?php
add_action('wp_enqueue_scripts', function(){
    wp_register_style('rs-frontend', RS_PLUGIN_URL.'assets/css/slider.css');
    wp_register_script('rs-frontend-js', RS_PLUGIN_URL.'assets/js/frontend.js', [], '1.0', true);
    wp_localize_script('rs-frontend-js','rsFront',array('theme'=> get_option('rs_theme_color','#000'), 'star'=> get_option('rs_star_color','#FFD700') ));
});

add_shortcode('review_slider', function($atts){
    wp_enqueue_style('rs-frontend');
    wp_enqueue_script('rs-frontend-js');

    $q = new WP_Query(['post_type'=>'review_slider','posts_per_page'=>-1,'orderby'=>'menu_order','order'=>'ASC']);
    ob_start();
    echo '<div class="review-slider-wrapper"><div class="slider"><div class="slider-track">';
    while($q->have_posts()): $q->the_post(); $meta=get_post_meta(get_the_ID());
      $img = esc_url($meta['rs_image'][0] ?? '');
      $stars = intval($meta['rs_stars'][0] ?? 5);
      echo '<div class="slide">';
      if ($img) echo '<img data-src="'.$img.'" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="rs-thumb" />';
      echo '<h3>'.get_the_title().'</h3>';
      echo '<p>'.esc_html($meta['rs_customer_info'][0] ?? '').'</p>';
      echo '<p class="rs-stars" data-stars="'.$stars.'"></p>';
      echo '<div class="rs-text">'.get_the_content().'</div>';
      echo '<small>'.esc_html($meta['rs_location'][0] ?? '').'</small>';
      echo '</div>';
    endwhile; wp_reset_postdata();
    echo '</div><div class="dots"></div></div></div>';
    return ob_get_clean();
});
