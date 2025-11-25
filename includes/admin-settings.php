<?php
add_action('admin_menu', function(){
    add_submenu_page('edit.php?post_type=review_slider','Slider Settings','Slider Settings','manage_options','rs_settings','rs_settings_page');
});

function rs_settings_page(){
    if (isset($_POST['rs_save_settings'])){
        check_admin_referer('rs_settings_nonce');
        update_option('rs_theme_color', sanitize_hex_color($_POST['rs_theme_color'] ?? '#000'));
        update_option('rs_star_color', sanitize_hex_color($_POST['rs_star_color'] ?? '#FFD700'));
        echo '<div class="updated"><p>Saved.</p></div>';
    }

    $theme = get_option('rs_theme_color','#000');
    $star = get_option('rs_star_color','#FFD700');
    ?>
    <div class="wrap">
      <h1>Review Slider Settings</h1>
      <form method="post">
        <?php wp_nonce_field('rs_settings_nonce'); ?>
        <table class="form-table">
          <tr><th>Dot/Accent Color</th><td><input type="color" name="rs_theme_color" value="<?php echo esc_attr($theme); ?>"></td></tr>
          <tr><th>Star Color</th><td><input type="color" name="rs_star_color" value="<?php echo esc_attr($star); ?>"></td></tr>
          <tr><th>Shortcode</th><td><input type="text" readonly value="[review_slider]" id="rs_shortcode" style="width:100%;max-width:300px;"> <button class="button" id="rs_copy_shortcode">Copy</button></td></tr>
        </table>
        <p><button class="button button-primary" name="rs_save_settings">Save</button></p>
      </form>
    </div>
    <script>
    jQuery(function($){
        $('#rs_copy_shortcode').on('click', function(e){
            e.preventDefault();
            var copyText = document.getElementById("rs_shortcode");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            $(this).text('Copied!');
        });
    });
    </script>
    <?php
}
