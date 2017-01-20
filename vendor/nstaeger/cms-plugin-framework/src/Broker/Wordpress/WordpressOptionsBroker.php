<?php

namespace Nstaeger\CmsPluginFramework\Broker\Wordpress;

use Nstaeger\CmsPluginFramework\Broker\OptionBroker;
use Nstaeger\CmsPluginFramework\Configuration;
use Nstaeger\CmsPluginFramework\Support\ArgCheck;

class WordpressOptionsBroker implements OptionBroker
{
	/**
	 * @var string
	 */
	private $prefix;

	public function __construct( Configuration $configuration ) {
		$this->prefix = $configuration->getOptionPrefix();
	}

	function delete( $option ) {
		ArgCheck::notNull( $option );

		delete_option( $this->prefix( $option ) );
	}

	public function get( $option, $blogid = '' ) {
		ArgCheck::notNull( $option );
		if ( '' !== $blogid ) {
			switch_to_blog( $blogid );
		}
		$this_option =  get_option( $this->prefix( $option, $blogid ) );
		if ( '' !== $blogid ) {
			restore_current_blog();
		}
		return $this_option;
	}

	/**
	 * save option in / on correct site
	 *
	 * @param  string $option trailing name for option
	 * @param  string $value  what to save
	 * @param  int $blogid only a integer then creating new site
	 *
	 * @return void
	 */
	public function store( $option, $value, $blogid = '' ) {
		ArgCheck::notNull( $option );

		if ( '' !== $blogid ) {
			switch_to_blog( $blogid );
		}
		update_option( $this->prefix( $option, $blogid ), $value );
		if ( '' !== $blogid ) {
			restore_current_blog();
		}
	}

	/**
	 * set plugin prefix
	 *
	 * @param  string $option trailing name for option
	 * @param  int $blogid only an integer then crating new site
	 *
	 * @return string option_name
	 */
	private function prefix( $option, $blogid = '' ) {
		if ( '' !== $blogid ) {
			return 'wppen_' . $blogid . '_' . $option;
		} else {
			return $this->prefix . $option;
		}
	}
}
