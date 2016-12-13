<?php
/**
 * Plugin Name: E-post om ny bloggpost
 * Description: Skicka e-post till prenumeranter när en ny bloggpost är publicerad
 * Version: 1.0.3
 * Author: Nicolai Stäger - anpassad av IIS
 * Author URI: https://iis.se
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use Nstaeger\CmsPluginFramework\Item\AssetItem;
use Nstaeger\CmsPluginFramework\Configuration;
use Nstaeger\CmsPluginFramework\Creator\WordpressCreator;
use Nstaeger\WpPostEmailNotification\WpPostEmailNotificationPlugin;

defined('ABSPATH') or die('No script kiddies please!');

require __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/config.php';

$plugin = new WpPostEmailNotificationPlugin(new Configuration($config), new WordpressCreator());

$plugin->permission()->registerPermissionMapping('can_manage', 'manage_options');
// $plugin->asset()->addAsset(new AssetItem('js/bundle/frontend-widget.js'));

// add_action(
// 	'widgets_init',
// 	function () {
// 		register_widget('Nstaeger\WpPostEmailNotification\Widget\SubscriptionWidget');
// 	},
// 	10,
// 	2
// );

add_action(
	'transition_post_status',
	function ($new_status, $old_status, $post) use ($plugin) {
		if ($post->post_type != 'post') {
			return;
		}

		if ($new_status == 'publish' && $old_status != 'publish') {
			$plugin->events()->fire('post-published', [$post->ID]);
		} elseif ($old_status == 'publish' && $new_status != 'publish') {
			$plugin->events()->fire('post-unpublished', [$post->ID]);
		}
	},
	10,
	3
);

// If a new blog is created
add_action( 'wpmu_new_blog', 'new_site_add_admin_page', 10, 6 );

add_action( 'wp_enqueue_scripts', 'register_iis_notify_scripts' );

// Hook activation to set a front facing user adm page on activation
register_activation_hook( __FILE__, 'multi_network_activate' );

function register_iis_notify_scripts() {
  if ( is_page_template( 'userfacing-template.php' ) ) {
  	wp_register_script( 'iis_notify_frontend', plugins_url( 'js/bundle/frontend-widget.js', __FILE__ ), array(), '20161208', true );
  	wp_localize_script( 'iis_notify_frontend', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
    wp_enqueue_script( 'iis_notify_frontend' );

  }
}

function multi_network_activate( $networkwide ) {
	global $wpdb;

	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		 //check if it is network activation if so run the activation function for each id
		if ( $networkwide ) {
			//Get all blog ids WP >= 4.6
			if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ) {
				$sites = get_sites();
				foreach ( $sites as $site ) {
					switch_to_blog( $site->blog_id );
					admin_my_mail_page();
					restore_current_blog();
				}
				return;
			}
		}
		admin_my_mail_page();
		return;
	} else {
		admin_my_mail_page();
		return;
	}
}

function new_site_add_admin_page( $blog_id ) {

	if ( is_plugin_active_for_network( 'iis-wp-post-email-notification/iis-wp-post-email-notification.php' ) ) {
		switch_to_blog( $blog_id );
		admin_my_mail_page();
		restore_current_blog();
	}
}

function admin_my_mail_page() {
	$user_page      = get_page_by_path( '/prenumerationsval/' );
	$user_post_name = isset( $user_page->post_name );

	// Check that it does not allready exists
	if ( ! $user_post_name ) {
		// Create post object
		$adm_page = array(
				'post_title'    => 'Prenumerationsval',
				'post_content'  => 'Denna sida visar dina användare vilka val de kan göra när de prenumererar. Låt sidan vara som den är.',
				'post_status'   => 'publish',
				'post_type'     => 'page',
				'meta_input'    => array(
				                         '_wp_page_template'         => 'userfacing-template.php',
				                         '_iis_notify_page_template' => 'userfacing-template.php',
				                         ),
		);
		// Insert the post into the database
		wp_insert_post( $adm_page, '' );
	} else {
		$page_template_meta = get_post_meta( $user_page->ID, '_iis_notify_page_template', true );
		if ( 'userfacing-template.php' !== $page_template_meta ) {
			add_post_meta( $user_page->ID, '_iis_notify_page_template', 'userfacing-template.php', true );
		}
	}

}
