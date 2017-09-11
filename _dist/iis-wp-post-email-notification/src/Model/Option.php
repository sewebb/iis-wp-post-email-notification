<?php

namespace Nstaeger\WpPostEmailNotification\Model;

use Nstaeger\CmsPluginFramework\Broker\OptionBroker;
use Nstaeger\CmsPluginFramework\Support\ArgCheck;

class Option
{
	const EMAIL_BODY = 'emailBody';
	const EMAIL_SUBJECT = 'emailSubject';
	const NUMBER_OF_EMAILS_SEND_PER_REQUEST = 'numberOfEmailsSendPerRequest';

	/**
	 * @var OptionBroker
	 */
	private $optionBroker;

	public function __construct( OptionBroker $optionBroker ) {
		$this->optionBroker = $optionBroker;
	}

	/**
	 * Sets up default options then creating new site or activating plugin
	 *
	 * @param  int $blogid Only an integer then creating a new blog on multisite network
	 *
	 * @return void
	 */
	public function createDefaults( $blogid = '' ) {
		$body    = $this->getEmailBody( $blogid );
		$subject = $this->getEmailSubject( $blogid );
		$number  = $this->getNumberOfEmailsSendPerRequest( $blogid );

		if ( '' == $body ) {
			$this->setEmailBody( "Hej!\n\nPå @@blog.name har vi just publicerat ett nytt inlägg som heter @@post.title. Du kan läsa inlägget via denna länken:\n\n@@post.link\n\nMed vänlig hälsning\n@@post.author.name", $blogid );
		}
		if ( '' == $subject ) {
			$this->setEmailSubject( 'Nytt inlägg på @@blog.name', $blogid );
		}
		if ( '' == $number ) {
			$this->setNumberOfEmailsSendPerRequest( 5, $blogid );
		}

	}

	public function deleteAll() {
		$this->optionBroker->delete( self::EMAIL_BODY );
		$this->optionBroker->delete( self::EMAIL_SUBJECT );
		$this->optionBroker->delete( self::NUMBER_OF_EMAILS_SEND_PER_REQUEST );
	}

	public function getAll() {
		return [
			'emailBody'                   => $this->getEmailBody(),
			'emailSubject'                => $this->getEmailSubject(),
			'numberOfMailsSendPerRequest' => $this->getNumberOfEmailsSendPerRequest(),
		];
	}

	public function getEmailBody( $blogid = '' ) {
		return $this->optionBroker->get( self::EMAIL_BODY, $blogid );
	}

	public function getEmailSubject( $blogid = '' ) {
		return $this->optionBroker->get( self::EMAIL_SUBJECT, $blogid );
	}

	public function getNumberOfEmailsSendPerRequest( $blogid = '' ) {
		return $this->optionBroker->get( self::NUMBER_OF_EMAILS_SEND_PER_REQUEST, $blogid );
	}

	public function setAll( $values ) {
		ArgCheck::isArray( $values );

		if ( isset( $values['emailBody'] ) ) {
			$this->setEmailBody( $values['emailBody'] );
		}

		if ( isset( $values['emailSubject'] ) ) {
			$this->setEmailSubject( $values['emailSubject'] );
		}

		if ( isset( $values['numberOfMailsSendPerRequest'] ) ) {
			$this->setNumberOfEmailsSendPerRequest( $values['numberOfMailsSendPerRequest'] );
		}
	}

	/**
	 * saves value
	 *
	 * @param [type] $value  [description]
	 * @param [type] $blogid [description]
	 */
	public function setEmailBody( $value, $blogid = '' ) {
		ArgCheck::notNull( $value );

		return $this->optionBroker->store( self::EMAIL_BODY, $value, $blogid );
	}

	public function setEmailSubject( $value, $blogid = '' ) {
		ArgCheck::notNull( $value );

		return $this->optionBroker->store( self::EMAIL_SUBJECT, $value, $blogid );
	}

	public function setNumberOfEmailsSendPerRequest( $value, $blogid = '' ) {
		ArgCheck::isInt( $value );

		$this->optionBroker->store( self::NUMBER_OF_EMAILS_SEND_PER_REQUEST, $value, $blogid );
	}
}
