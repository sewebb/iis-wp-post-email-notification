<?php

namespace Nstaeger\CmsPluginFramework;

use Illuminate\Container\Container;
use Nstaeger\CmsPluginFramework\Broker\AssetBroker;
use Nstaeger\CmsPluginFramework\Broker\MenuBroker;
use Nstaeger\CmsPluginFramework\Broker\PermissionBroker;
use Nstaeger\CmsPluginFramework\Broker\RestBroker;
use Nstaeger\CmsPluginFramework\Creator\Creator;
use Nstaeger\CmsPluginFramework\Event\EventDispatcher;
use Nstaeger\CmsPluginFramework\Templating\TemplateRenderer;
use Symfony\Component\HttpFoundation\Request;

class Plugin extends Container
{
	public function __construct( Configuration $configuration, Creator $creator ) {
		self::setInstance( $this );

		$this->instance( 'Nstaeger\CmsPluginFramework\Configuration', $configuration );
		$this->singleton(
			'Nstaeger\CmsPluginFramework\Event\EventDispatcher',
			'Nstaeger\CmsPluginFramework\Event\EventDispatcher'
		);
		$creator->build( $this );

		// register regular events from system
		$this->make( 'Nstaeger\CmsPluginFramework\Broker\EventBroker' )->fireAll( $this->events() );

		// regular request
		$this->singleton(
			'Symfony\Component\HttpFoundation\Request',
			function () {
				return Request::createFromGlobals();
			}
		);

		$this->events()->on( 'activate', array( $this, 'activate' ) );
		$this->events()->on( 'deactivate', array( $this, 'deactivate' ) );

		add_filter( 'allowed_http_origins', array( $this, 'add_allowed_origins' ) );

		// add_action( 'wp_ajax_wppen_v1_subscribe_post', array( $this, 'admin_add_allow_header' ) );
		// add_action( 'send_headers', array( $this, 'admin_add_allow_header' ) );
	}

	public function add_allowed_origins( $origins ) {
		global $wpdb;
		$test_home = $wpdb->get_var( "select option_value from $wpdb->options where option_name = 'siteurl'" );
		// Special for webbstjarnan.nu
		if ( strpos( $test_home, 'webbstjarnan.nu' ) ) {
			$subdomain      = explode( '.', $test_home )[0];
			$newdomain      = str_replace( 'https://', 'http://', $subdomain );
			$add_origin     = $newdomain . '.se';
			$origins[]      = $add_origin;
			$origins[]      = $test_home;
		}
		return $origins;
	}


	public function admin_add_allow_header() {
		header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization' );
		header( 'Access-Control-Allow-Methods: GET, HEAD, OPTIONS, POST, PUT' );
		header( 'Access-Control-Expose-Headers: Authorization' );

		global $wpdb;
		$test_home = $wpdb->get_var( "select option_value from $wpdb->options where option_name = 'siteurl'" );
		// Special for webbstjarnan.nu
		if ( strpos( $test_home, 'webbstjarnan.nu' ) ) {
			$subdomain      = explode( '.', $test_home )[0];
			$newdomain      = str_replace( 'https://', 'http://', $subdomain );
			$add_origin     = $newdomain . '.se';
			header( 'Access-Control-Allow-Origin: ' . $add_origin );
		}
	}


	public function add_access_control_allow_headers( $headers ) {
		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$headers['Access-Control-Allow-Headers'] = 'Origin, X-Requested-With, Content-Type, Accept, Authorization';
			$headers['Access-Control-Allow-Methods'] = 'GET, HEAD, OPTIONS, POST, PUT';
			$headers['Access-Control-Expose-Headers'] = 'Authorization';
			return $headers;
		}
	}


	/**
	 * @return RestBroker
	 */
	public function ajax() {
		return $this->make( 'Nstaeger\CmsPluginFramework\Broker\RestBroker' );
	}

	/**
	 * @return AssetBroker
	 */
	public function asset() {
		return $this->make( 'Nstaeger\CmsPluginFramework\Broker\AssetBroker' );
	}

	/**
	 * @return EventDispatcher
	 */
	public function events() {
		return $this->make( 'Nstaeger\CmsPluginFramework\Event\EventDispatcher' );
	}

	/**
	 * @return MenuBroker
	 */
	public function menu() {
		return $this->make( 'Nstaeger\CmsPluginFramework\Broker\MenuBroker' );
	}

	/**
	 * @return PermissionBroker
	 */
	public function permission() {
		return $this->make( 'Nstaeger\CmsPluginFramework\Broker\PermissionBroker' );
	}

	/**
	 * @return TemplateRenderer
	 */
	public function renderer() {
		return $this->make( 'Nstaeger\CmsPluginFramework\Templating\TemplateRenderer' );
	}

	/**
	 * Is being called automatically, when the plugin is being activated
	 */
	protected function activate() {
		// noop
	}

	/**
	 * is being called automatically, when the plugin is being deactivated
	 */
	protected function deactivate() {
		// noop
	}
}
