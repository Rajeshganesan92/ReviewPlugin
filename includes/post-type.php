<?php
// Register CPT: review_slider
add_action('init', function(){
    $labels = [
        'name' => 'Reviews',
        'singular_name' => 'Review',
    ];
    register_post_type('review_slider', [
        'labels' => $labels,
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-testimonial',
        'supports' => ['title','editor','thumbnail','page-attributes'],
    ]);
});

// Add thumbnail support size
add_action('after_setup_theme', function(){
    add_image_size('rs-review-thumb', 120, 120, true);
});
