<?php

namespace Nstaeger\WpPostEmailNotification\Model;

use Nstaeger\CmsPluginFramework\Broker\DatabaseBroker;
use Nstaeger\CmsPluginFramework\Support\ArgCheck;
use Nstaeger\CmsPluginFramework\Support\Time;
use Symfony\Component\HttpFoundation\Request;

class SubscriberModel
{
	const TABLE_NAME = '@@wppen_subscribers';

	/**
	 * @var DatabaseBroker
	 */
	private $database;

	public function __construct(DatabaseBroker $database)
	{
		$this->database = $database;
	}

	/**
	 * @param Request $request
	 */
	public function add(Request $request)
	{
		$subscriber = json_decode($request->getContent());

		$email   = isset($subscriber->email) ? sanitize_email($subscriber->email) : null;
		$ip      = $request->getClientIp();
		$authors = isset( $subscriber->checkedAuthors ) ? array_map( 'absint', $subscriber->checkedAuthors ) : array();
		$authors = serialize( $authors );

		$blog_id           = get_current_blog_id();
		$email_blog_id_md5 = md5( $email . $blog_id );
		$query             = sprintf( "SELECT id, email FROM %s WHERE email_blog_id_md5 = '{$email_blog_id_md5}' ", self::TABLE_NAME );

		$dupe_check = $this->database->fetchAll( $query );
		$curr_id    = isset( $dupe_check[0]['id'] ) ? $dupe_check[0]['id'] : null;
		$mail_exist = isset( $dupe_check[0]['email'] ) ? $dupe_check[0]['email'] : '';

		if ( $mail_exist !== $email ) {
			$this->addPlain( $email, $ip, $authors, $email_blog_id_md5 );
		} else {
			$this->updatePlain( $curr_id, $authors, $email );
		}
	}

	public function createTable()
	{
		$query = "CREATE TABLE IF NOT EXISTS " . self::TABLE_NAME . " (
			id int(10) NOT NULL AUTO_INCREMENT,
			blog_id int(10) NOT NULL,
			authors_array longtext NOT NULL,
			email VARCHAR(255) NOT NULL,
			ip VARCHAR(255),
			created_gmt DATETIME NOT NULL,
			email_blog_id_md5 VARCHAR(100) NOT NULL,
			PRIMARY KEY  id (id)
		) DEFAULT CHARSET=utf8;";

		if ($this->database->executeQuery($query) === false) {
			throw new \RuntimeException('Unable to create database for WP Post Subscription Plugin');
		}
	}

	public function delete($id)
	{
		ArgCheck::isInt($id);

		$where = [
			'id'      => $id,
			'blog_id' => get_current_blog_id(),
		];

		if ($this->database->delete(self::TABLE_NAME, $where) === false) {
			throw new \RuntimeException('Unable to delete subscriber from database (Post Subscription Plugin)');
		}
	}

	public function external_delete( $id ) {


		$where = [
			'id'      => $id,
			'blog_id' => get_current_blog_id(),
		];

		if ($this->database->delete(self::TABLE_NAME, $where) === false) {
			throw new \RuntimeException('Prenumeranten kunde inte radera sig.');
		}
	}

	public function dropTable()
	{
		$query = sprintf("DROP TABLE IF EXISTS %s", self::TABLE_NAME);

		if ($this->database->executeQuery($query) === false) {
			throw new \RuntimeException('Unable to delete database for WP Post Subscription Plugin');
		}
	}

	public function getAll()
	{
		$blog_id = get_current_blog_id();
		$query = sprintf("SELECT * FROM %s WHERE blog_id = {$blog_id} ORDER BY id", self::TABLE_NAME);

		return $this->database->fetchAll($query);
	}

	public function getEmails($offset, $count)
	{
		ArgCheck::isInt($offset);
		ArgCheck::isInt($count);

		$blog_id = get_current_blog_id();

		$query = sprintf("SELECT email, blog_id, authors_array, email_blog_id_md5 FROM %s WHERE blog_id = {$blog_id} ORDER BY id LIMIT %d, %d", self::TABLE_NAME, $offset, $count);

		return $this->database->fetchAll($query);
	}

	/**
	 * Adds the subscriber to dbtable - with blog_id
	 *
	 * @param  string $email   subscriber email
	 * @param  string $ip      subscriber ip-address
	 */

	private function addPlain( $email, $ip, $authors, $email_blog_id_md5 )
	{
		ArgCheck::isEmail( $email );
		ArgCheck::isIp( $ip );
		$blog_id = get_current_blog_id();

		$data = [
			'email'             => $email,
			'ip'                => $ip,
			'blog_id'           => $blog_id,
			'authors_array'     => $authors,
			'created_gmt'       => Time::now()->asSqlTimestamp(),
			'email_blog_id_md5' => $email_blog_id_md5,
		];

		if ( $this->database->insert( self::TABLE_NAME, $data ) === false ) {
			throw new \RuntimeException( 'Unable to add subscriber to the database' );
		}

	}

	private function updatePlain( $curr_id, $authors, $email )  {
		ArgCheck::isEmail( $email );
		$blog_id = get_current_blog_id();

		$data = [
			'authors_array' => $authors,
			'created_gmt'   => Time::now()->asSqlTimestamp(),
		];
		$where = [
			'id'      => $curr_id,
		];

		if ( $this->database->update( self::TABLE_NAME, $data, $where ) === false ) {
			throw new \RuntimeException( 'Unable to update subscriber in the database' );
		}
	}


}
