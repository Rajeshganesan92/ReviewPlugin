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
        </table>
        <p><button class="button button-primary" name="rs_save_settings">Save</button></p>
      </form>
    </div>
    <?php
}
