<?php
/*
Plugin Name: google-analytics
Description: Setup for Google Analytics legacy code
Version: 1.1.0
Author: Dejan Batanjac
Author URI: https://programming-review.com/
*/

// make sure we don't have another class like this
if ( !class_exists('db_google_analytics_main') ) {
  // using classes is the recommanded way to write plugins
  class db_google_analytics_main {

      // we don't have the PHP version checker yet
      public function __construct() {
          // plugin only action
          add_action( 'wp_enqueue_scripts', array($this, 'add_ga_code') );

          // only for the admin we will have google analytics link inside settings
          if (is_admin()) {
            add_action('admin_menu', array($this, 'add_settings_page') );
          }
      }

      public function add_settings_page(){
        //introduce the menu for admins only
        if ( is_admin() ) {
          // add the page to the settins menu
          add_options_page(
          'Google Analytics Setup',
          'Google Analytics Setup',
          'manage_options',
          'db_google_analytics_code',
          array($this, 'form_to_set_ga'));
        }
      }

      /* form to set Google Analytics*/
      public function form_to_set_ga() {
        if ( is_admin() ) {
          if( isset($_POST['_wpnonce']) ) {
            $nonce = $_POST['_wpnonce'];
            if ( ! wp_verify_nonce($nonce, 'google-analytics') ) die("Security check");
          }

          if(isset($_POST["ap"])) {
            update_option('db_google_analytics_code', htmlspecialchars($_POST["ap"]) );
          }

          // get rid of the magic quotes with stripslashes and since WP don't have
          // sanitize_textarea needed to use htmlspecialchars although it is safe
          // witout
          $gac = htmlspecialchars_decode(stripslashes(get_option('db_google_analytics_code')));

          // page of interest ?>
          <div class="wrap">
          <h2>Google Analytics</h2>
          <form method="post" action="">
          <?php wp_nonce_field('google-analytics'); ?>
          <div>Insert Google Analytics legacy code:</div>
          <textarea rows="10" cols="100%" name="ap"><?php echo esc_textarea($gac) ?></textarea>
          <p class="submit">
          <input type="submit" class="button-primary" value="Save Google Analytics code" />
          </p>
          </form>
          </div>
          <?php
        }// is admin
    }
      public function add_ga_code() {
        // we know for sure WordPress uses jquery
        wp_enqueue_script( 'jquery-core');
        $google_analytics_script = get_option( 'db_google_analytics_code' );
        // with this function we will remove the <script> </script> tags Google Analytics have
        wp_add_inline_script( 'jquery-core', stripslashes(strip_tags(htmlspecialchars_decode( $google_analytics_script ))) );
      }
  }
  new db_google_analytics_main;
}
?>
