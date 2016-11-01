<?php
/**
 * Plugin Name: GA Analytics
 * Description: Setup for Google Analytics legacy code
 * Version: 1.2.0
 * Author: Dejan Batanjac
  *Author URI: https://programming-review.com/
  */

/**
 * WordPress version need to be at least 4.5.
 */
global $wp_version;
if ( version_compare( $wp_version, '4.5', '<' ) ) {
    if ( is_admin() ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
    deactivate_plugins( __FILE__ );
    wp_die( __( 'GA Analytics requires WordPress 4.5 or higher. The plugin has now disabled itself.' ) );
  } else {
    return;
  }
}

/**
* Make sure we don't have another class like this.
*/
if ( ! class_exists( 'Db_Google_Analytics_Main' ) ) {
  class Db_Google_Analytics_Main {

      public function __construct() {
          // Plugin action to inject the GA.
          add_action( 'wp_enqueue_scripts', array($this, 'add_ga_code') );

          // Only Admin will be able to access settings.
          if ( is_admin() ) {
            add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
          }
      }

      public function add_settings_page() {
        if ( is_admin() ) {
          add_options_page(
              'Google Analytics Setup',
              'Google Analytics Setup',
              'manage_options',
              'db_google_analytics_code',
          array($this, 'form_to_set_ga'));
        }
      }

      // Form to set Google Analytics.
      public function form_to_set_ga() {
        if ( is_admin() ) {
          if ( isset( $_POST['_wpnonce'] ) ) {
            $nonce = $_POST['_wpnonce'];
            if ( ! wp_verify_nonce( $nonce, 'google-analytics' ) ) {
              wp_die( __( 'Security check.' ) );
            }
          }

          if ( isset( $_POST['ap'] ) ) {
            update_option( 'db_google_analytics_code', htmlspecialchars( $_POST['ap'] ) );
          }

          $gac = htmlspecialchars_decode( stripslashes( get_option( 'db_google_analytics_code' ) ) );

          ?>
          <div class="wrap">
          <h2>Google Analytics</h2>
          <form method="post" action="">
          <?php wp_nonce_field( 'google-analytics' ); ?>
          <div>Insert Google Analytics legacy code:</div>
          <textarea rows="10" cols="100%" name="ap"><?php echo esc_textarea( $gac ) ?></textarea>
          <p class="submit">
          <input type="submit" class="button-primary" value="Save Google Analytics code" />
          </p>
          </form>
          </div>
          <?php
        }// End if().
      }

      public function add_ga_code() {
        // We know for sure WordPress uses JQuery.
        wp_enqueue_script( 'jquery-core' );
        $google_analytics_script = get_option( 'db_google_analytics_code' );

        // Remove <script> </script> tags from Google Analytics since WordPress will provide them.
        wp_add_inline_script( 'jquery-core', stripslashes( strip_tags( htmlspecialchars_decode( $google_analytics_script ) ) ) );
      }
  }

  new Db_Google_Analytics_Main;
}// End if().
?>
