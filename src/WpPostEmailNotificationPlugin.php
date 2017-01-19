<?php

namespace Nstaeger\WpPostEmailNotification;

use Nstaeger\CmsPluginFramework\Configuration;
use Nstaeger\CmsPluginFramework\Creator\Creator;
use Nstaeger\CmsPluginFramework\Plugin;
use Nstaeger\WpPostEmailNotification\Model\JobModel;
use Nstaeger\WpPostEmailNotification\Model\Option;
use Nstaeger\WpPostEmailNotification\Model\SubscriberModel;

class WpPostEmailNotificationPlugin extends Plugin {
	function __construct( Configuration $configuration, Creator $creator ) {
		parent::__construct( $configuration, $creator );

		$this->menu()->registerAdminMenuItem( 'Epost om ny bloggpost' )
			 ->withAction( 'AdminPageController@optionsPage' )
			 ->withAsset( 'js/bundle/admin-options.js' );

		// $this->ajax()->delete('job')->resolveWith('AdminJobController@delete')->onlyWithPermission('can_manage');
		$this->ajax()->get( 'job' )->resolveWith( 'AdminJobController@get' )->onlyWithPermission( 'can_manage' );
		$this->ajax()->get( 'option' )->resolveWith( 'AdminOptionController@get' )->onlyWithPermission( 'can_manage' );
		$this->ajax()->put( 'option' )->resolveWith( 'AdminOptionController@update' )->onlyWithPermission( 'can_manage' );
		$this->ajax()->post( 'subscribe' )->resolveWith( 'FrontendSubscriberController@post' )->enableForUnauthorized( true );

		$this->ajax()->delete( 'subscribe' )->resolveWith( 'FrontendSubscriberController@delete' )->enableForUnauthorized( true );

		$this->ajax()
			 ->delete( 'subscriber' )
			 ->resolveWith( 'AdminSubscriberController@delete' )
			 ->onlyWithPermission( 'can_manage' );
		$this->ajax()
			 ->get( 'subscriber' )
			 ->resolveWith( 'AdminSubscriberController@get' )
			 ->onlyWithPermission( 'can_manage' );
		$this->ajax()
			 ->post( 'subscriber' )
			 ->resolveWith( 'AdminSubscriberController@post' )
			 ->onlyWithPermission( 'can_manage' );

		$this->events()->on( 'loaded', array( $this, 'sendNotifications' ) );
		$this->events()->on( 'post-published', array( $this, 'postPublished' ) );
		$this->events()->on( 'post-unpublished', array( $this, 'postUnpublished' ) );

		// Our templates in this array.
		$this->templates = array(
			'userfacing-template.php' => 'Prenumerationsval',
		);
		add_filter( 'template_include', array( $this, 'view_project_template' ), 99 );

		// Add query var to front facing admin page
		add_filter( 'query_vars', array( $this, 'add_query_vars_filter' ) );

		// Display subscribe link
		// Add link only after the content
		// add_filter( 'the_author', array( $this, 'subscribelink_after_content' ) );
		add_filter( 'the_content', array( $this, 'subscribelink_after_content' ) );

	}


	/**
	 * [subscribelink_after_content description]
	 *
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	function subscribelink_after_content( $content ) {
		// Add link also in archive listings - but not on pages
		// Add link if frontpage lists blog posts
		if ( is_single() || is_archive() || ( is_front_page() && is_home() ) ) {
			$author_id = get_the_author_meta( 'ID' );
			$content .= '<hr><a href="/prenumerationsval/?subscribe_author=' . $author_id . '">Prenumerera på nya blogginlägg</a>';
			return $content;
		}
		return $content;
	}
	// Add query var to front facing admin page
	public function add_query_vars_filter( $vars ) {
		$vars[] = 'subscribe_options';
		$vars[] .= 'subscribe_author';
		return $vars;
	}

	public function activate() {
		$this->job()->createTable();
		$this->subscriber()->createTable();
		$this->option()->createDefaults();
		// In iis.wp-post-email-notification.php we add functions for adding frontend adm page to each blog
	}

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {

		global $post;
		// If the dont have special postMeta for page template, return normal template
		if ( ! isset( $this->templates[ get_post_meta( $post->ID, '_iis_notify_page_template', true ) ] ) ) {
			return $template;
		}

		$file = plugin_dir_path( __FILE__ ) . get_post_meta( $post->ID, '_iis_notify_page_template', true );
		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		}
		return $template;
	}

	/**
	 * @return JobModel
	 */
	public function job() {
		return $this->make( 'Nstaeger\WpPostEmailNotification\Model\JobModel' );
	}

	/**
	 * @return Option
	 */
	public function option() {
		return $this->make( 'Nstaeger\WpPostEmailNotification\Model\Option' );
	}

	public function postPublished( $id ) {
		$this->job()->createNewJob( $id );
	}

	public function postUnpublished( $id ) {
		$this->job()->removeJobsFor( $id );
	}

	public function sendNotifications() {
		$numberOfMails = $this->option()->getNumberOfEmailsSendPerRequest();
		$jobs          = $this->job()->getNextJob();

		if ( empty( $jobs ) ) {
			return;
		}

		foreach ( $jobs as $job ) {
			$recipients = $this->subscriber()->getEmails( $job['offset'], $numberOfMails );

			if ( sizeof( $recipients ) < $numberOfMails ) {
				$this->job()->completeJob( $job['id'] );
			} else {
				$this->job()->rescheduleWithNewOffset( $job['id'], sizeof( $recipients ) );
			}

			if ( ! empty( $recipients ) ) {
				$post           = get_post( $job['post_id'] );
				$author_id      = $job['author_id'];

				$blog_name      = get_bloginfo( 'name' );
				$blog_url       = get_bloginfo( 'url' );
				$postAuthorName = get_the_author_meta( 'display_name', $post->post_author );
				$postLink       = get_permalink( $post->ID );
				$postTitle      = $post->post_title;

				$rep_search     = [ '@@blog.name', '@@post.author.name', '@@post.link', '@@post.title' ];
				$rep_replace    = [ $blog_name, $postAuthorName, $postLink, $postTitle ];

				$subject        = $this->option()->getEmailSubject();
				$subject        = str_replace( $rep_search, $rep_replace, $subject );

				$message        = $this->option()->getEmailBody();
				$message        = str_replace( $rep_search, $rep_replace, $message );

				$headers[]      = '';

				foreach ( $recipients as $recipient ) {
					$rec_authors = $recipient['authors_array'];
					$rec_authors = unserialize( $rec_authors );

					// Check if recipient should get this authors post notification
					if ( in_array( $author_id, $rec_authors ) ) {
						$subscriber_md5            = $recipient['email_blog_id_md5'];
						$subscribe_options_message = '';
						$add_message               = '';
						$subscribe_options_message = "\n\n\n\nOm du vill ändra dina prenumerationsval eller sluta prenumerera - gå till denna länken\n" . $blog_url . '/prenumerationsval/?subscribe_options=' . $subscriber_md5;
						$add_message               = $message . $subscribe_options_message;
						wp_mail( [ $recipient['email'] ], $subject, $add_message, $headers );
					}
				}
			}
		}
	}

	/**
	 * @return SubscriberModel
	 */
	public function subscriber() {
		return $this->make( 'Nstaeger\WpPostEmailNotification\Model\SubscriberModel' );
	}
}
