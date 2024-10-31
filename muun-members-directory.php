<?php
/**
Plugin Name: Muun members directory
Plugin URI: http://www.gomuun.com/wordpress-plugin
Description: Get an always up to date member directory for your community. A monthly subscription for Muun is needed.
Tags: community, coworkingspace, remotework, members, member directory, members overview
Version: 1.2
Author: Muun
Author URI: http://www.gomuun.com
Text Domain: Muun members directory
*/

if( !class_exists( 'MuunMembers' ) ) {
  class MuunMembers {
    public $members = [];
    private static $instance = null;

    private function __construct() {
      add_action( 'admin_menu', [ $this, 'admin_menu' ] );
      add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
      add_action( 'wp_enqueue_scripts', [ $this, 'add_js' ] );
      add_shortcode( 'muun-members-directory', [ $this, 'shortcode' ] );

      $this->options = get_option( 'muunmembers_options' );
    }

    public static function Instance() {
      self::$instance = is_null( self::$instance ) ? new self() : self::$instance;
      return self::$instance;
    }

    public function enqueue() {
      if( !empty( $this->options[ 'enable_style' ] ) )
        wp_enqueue_style( 'muunmembers', 'https://www.gomuun.com/v1/css.css' );
    }

    public function add_js() {
      if( empty( $this->options[ 'enable_popup' ] ) )
      wp_enqueue_script('muun-javascript', plugin_dir_url(__FILE__) . 'muun-javascript.js', array('jquery'));
    }

    public function get_members() {
      if( $this->options[ 'space_id' ] ) {
        $ch = curl_init();
        curl_setopt_array( $ch, [
          CURLOPT_URL => 'https://www.gomuun.com/spaces/' . $this->options[ 'space_id' ] . '/members.json',
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_FOLLOWLOCATION => 1,
          CURLOPT_DNS_CACHE_TIMEOUT => 2,
          CURLOPT_DNS_USE_GLOBAL_CACHE => 0
        ]);
        $data = curl_exec( $ch );
        if( !curl_errno( $ch ) ) {
          $data = json_decode( $data );
          if( !empty( $data->members ) ) {
            $this->members = $data->members;
          }
        }
        else {
          echo curl_error( $ch );
        }
      }
      return $this;
    }

    public function to_html() {
      if( !empty( $this->members ) ) {
        $html = array_map( function( $v ) {
          if( $this->options[ 'inline_images' ] ) {
            return sprintf( '
            <div class="muunmembers__member" data-muun-name="%s" style="background-image: url(%s)" data-muun-headline="%s" data-muun-bio="%s" data-muun-email="%s" data-muun-website="%s" data-muun-twitter="%s" data-muun-facebook="%s" data-muun-instagram="%s" data-muun-linkedin="%s" data-muun-expertises="%s">
              <p class="muunmembers__name">%s</p>
            </div>',
            $v->user->name,
            $v->user->avatar,
            $v->user->headline,
            $v->user->bio,
            $v->user->email,
            $v->user->website,
            $v->user->twitter,
            $v->user->facebook,
            $v->user->instagram,
            $v->user->linkedin,
            implode(', ', $v->user->expertises),
            str_replace('%20', ' ', $v->user->name)
          );
          } else {
            return sprintf( '
            <div class="muunmembers__member">
              <img src="%s" class="muunmembers__avatar">
              <p class="muunmembers__name">%s</p>
            </div>', $v->user->avatar, $v->user->name );
          }
        }, $this->members );

        return sprintf( '<div id="muun-members" data-muun-brand-color="%s" class="muunmembers">%s</div>', $data, implode( '', $html ) );
      }
      return;
    }

    public function shortcode( $atts ) {
      $atts = shortcode_atts( [

      ], $atts );
      $members = $this->get_members()
        ->to_html();
      return $members;
    }

    public function admin_menu() {
      add_submenu_page( 'options-general.php', 'Muun members directory', 'Muun members directory', 'manage_options', 'muunmembers', [ $this, 'admin_page' ] );
    }

    public function admin_page() {
      if( !empty( $_POST[ 'space_id' ] ) && wp_verify_nonce( $_POST[ 'muunmembers_nonce' ], 'muunmembers_settings' ) ) {
        $save = [
          'space_id' => htmlentities( $_POST[ 'space_id' ] ),
          'enable_style' => !empty( $_POST[ 'enable_style' ] ),
          'inline_images' => empty( $_POST[ 'inline_images' ] ),
          'enable_popup' => empty( $_POST[ 'enable_popup' ] )
        ];
        update_option( 'muunmembers_options', $save );
      }
      $option = get_option( 'muunmembers_options' );
      ?>
      <div class="wrap">
        <h1><?=get_admin_page_title()?></h1>

        <?php
        if( !empty( $save ) ): ?>
          <div class="notice notice-success is-dismissible">
                <p><?php _e( 'Saved!', 'muunmembers' ); ?></p>
            </div><?php
        endif;
        ?>
        <form action="" method="post">
          <?php wp_nonce_field( 'muunmembers_settings', 'muunmembers_nonce' )?>
          <table class="form-table">
            <tr>
              <th scope="row"><label for="space_id">Your space ID</label></th>
              <td><input name="space_id" type="text" id="space_id" value="<?=$option[ 'space_id' ]?>" class="regular-text" required="1" /></td>
            </tr>
            <tr>
              <th scope="row"><label for="enable_style">Enable Muun Css</label></th>
              <td><input name="enable_style" type="checkbox" id="enable_style" <?=( $option[ 'enable_style' ] ? 'checked' : '' )?> /></td>
            </tr>
            <tr>
              <th scope="row"><label for="inline-images">Inline avatar images</label></th>
              <td><input name="inline_images" type="checkbox" id="inline_images" <?=( $option[ 'inline_images' ] ? '' : 'checked' )?> /></td>
            </tr>
            <tr>
              <th scope="row"><label for="enable_popup">Enable popup</label></th>
              <td><input name="enable_popup" type="checkbox" id="enable_popup" <?=( $option[ 'enable_popup' ] ? '' : 'checked' )?> /></td>
            </tr>
          </table>
          <?php submit_button()?>
        </form>
      </div>
      <?php
    }
  }
}

MuunMembers::Instance();
