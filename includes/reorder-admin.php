<?php
add_action('admin_menu', function(){
    add_submenu_page('edit.php?post_type=review_slider','Reorder Reviews','Reorder Reviews','manage_options','rs_reorder','rs_reorder_page');
});

function rs_reorder_page(){
    // enqueue jQuery UI
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('rs-admin-reorder', RS_PLUGIN_URL.'assets/js/admin-reorder.js', ['jquery','jquery-ui-sortable'], '1.0', true);
    wp_localize_script('rs-admin-reorder','rsReorder',array('ajax_url'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('rs_reorder')));
    wp_enqueue_style('rs-admin-css', RS_PLUGIN_URL.'assets/css/slider.css');

    $reviews = get_posts(['post_type'=>'review_slider','posts_per_page'=>-1,'orderby'=>'menu_order','order'=>'ASC']);
    echo '<div class="wrap"><h1>Drag to Reorder Reviews</h1><ul id="rs_reorder_list" style="list-style:none;padding:0;">';
    foreach($reviews as $r){
        $meta = get_post_meta($r->ID);
        $img = esc_url($meta['rs_image'][0] ?? '');
        echo '<li class="rs_item" data-id="'.$r->ID.'" style="cursor:move;border:1px solid #ddd;padding:10px;margin-bottom:8px;background:#fff;">';
        echo '<strong>'.esc_html($r->post_title).'</strong><br/>';
        if ($img) echo '<img src="'.$img.'" style="width:60px;height:60px;border-radius:50%">';
        echo '</li>';
    }
    echo '</ul><p><button class="button" id="rs_save_order">Save Order</button></p></div>';
}

add_action('wp_ajax_rs_save_order', function(){
    check_ajax_referer('rs_reorder');
    if (!current_user_can('manage_options')) wp_send_json_error('no');
    $order = $_POST['order'] ?? [];
    foreach($order as $i => $id){
        wp_update_post(['ID'=>intval($id),'menu_order'=>intval($i)]);
    }
    wp_send_json_success();
});
