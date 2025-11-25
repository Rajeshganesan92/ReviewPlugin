<?php
add_action('add_meta_boxes', function(){
    add_meta_box('rs_details','Review Details','rs_meta_box_cb','review_slider','normal','high');
});

function rs_meta_box_cb($post){
    wp_nonce_field('rs_save_meta','rs_meta_nonce');
    $meta = get_post_meta($post->ID);
    $image = $meta['rs_image'][0] ?? '';
    $customer_info = $meta['rs_customer_info'][0] ?? '';
    $stars = $meta['rs_stars'][0] ?? 5;
    $location = $meta['rs_location'][0] ?? '';

    ?>
    <p><label>Customer Info</label><br>
    <input type="text" name="rs_customer_info" value="<?php echo esc_attr($customer_info); ?>" style="width:100%"></p>

    <p><label>Stars (1-5)</label><br>
    <input type="number" name="rs_stars" min="1" max="5" value="<?php echo esc_attr($stars); ?>"></p>

    <p><label>Location</label><br>
    <input type="text" name="rs_location" value="<?php echo esc_attr($location); ?>" style="width:100%"></p>

    <p>
      <label>Image</label><br>
      <input type="text" id="rs_image" name="rs_image" value="<?php echo esc_attr($image); ?>" style="width:70%">
      <button class="button" id="rs_upload">Upload</button>
    </p>
    <div id="rs_preview">
      <?php if ($image): ?><img src="<?php echo esc_url($image); ?>" style="max-width:120px;border-radius:50%" /><?php endif; ?>
    </div>

    <script>
    jQuery(function($){
        $('#rs_upload').on('click', function(e){
            e.preventDefault();
            var frame = wp.media({title:'Select Image', button:{text:'Use'}, multiple:false});
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

add_action('save_post', function($post_id){
    if (!isset($_POST['rs_meta_nonce']) || !wp_verify_nonce($_POST['rs_meta_nonce'],'rs_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (get_post_type($post_id) !== 'review_slider') return;

    update_post_meta($post_id,'rs_image', sanitize_text_field($_POST['rs_image'] ?? ''));
    update_post_meta($post_id,'rs_customer_info', sanitize_text_field($_POST['rs_customer_info'] ?? ''));
    update_post_meta($post_id,'rs_stars', intval($_POST['rs_stars'] ?? 5));
    update_post_meta($post_id,'rs_location', sanitize_text_field($_POST['rs_location'] ?? ''));
});
