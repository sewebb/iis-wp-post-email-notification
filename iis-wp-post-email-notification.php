<?php
/**
 * Plugin Name: E-post om ny bloggpost
 * Description: Skicka e-post till prenumeranter när en ny bloggpost är publicerad
 * Version: 1.1.0
 * Author: Nicolai Stäger - anpassad av IIS
 * Author URI: https://iis.se
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use Nstaeger\CmsPluginFramework\Item\AssetItem;
use Nstaeger\CmsPluginFramework\Configuration;
use Nstaeger\CmsPluginFramework\Creator\WordpressCreator;
use Nstaeger\WpPostEmailNotification\WpPostEmailNotificationPlugin;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/config.php';

$plugin = new WpPostEmailNotificationPlugin( new Configuration( $config ), new WordpressCreator() );

$plugin->permission()->registerPermissionMapping( 'can_manage', 'manage_options' );

add_action(
	'transition_post_status',
	function ( $new_status, $old_status, $post ) use ( $plugin ) {
		if ( $post->post_type != 'post' ) {
			return;
		}

		if ( $new_status == 'publish' && $old_status != 'publish' ) {
			$plugin->events()->fire( 'post-published', [ $post->ID ] );
		} elseif ( $old_status == 'publish' && $new_status != 'publish' ) {
			$plugin->events()->fire( 'post-unpublished', [ $post->ID ] );
		}
	},
	10,
	3
);

add_action( 'wp_enqueue_scripts', 'register_iis_notify_scripts' );

// Hook activation to set a front facing user adm page on activation
register_activation_hook( __FILE__, array ( $plugin, 'multi_network_activate' ) );

function register_iis_notify_scripts() {
	if ( is_page_template( 'userfacing-template.php' ) || is_page( 'prenumerationsval') ) {

		wp_register_script( 'iis_notify_frontend', plugins_url( 'js/bundle/frontend-widget.js', __FILE__ ), array(), '20170825', true );

		if ( is_ssl() ) {
			$scheme = 'https';
		} else {
			$scheme = 'http';
		}

		$adminurl = admin_url( 'admin-ajax.php', $scheme );

		wp_localize_script( 'iis_notify_frontend', 'ajaxurl', $adminurl, 'allow' );
		wp_enqueue_script( 'iis_notify_frontend' );
	}

}

