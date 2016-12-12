<?php

namespace Nstaeger\WpPostEmailNotification\Model;

use Nstaeger\CmsPluginFramework\Broker\DatabaseBroker;
use Nstaeger\CmsPluginFramework\Support\ArgCheck;
use Nstaeger\CmsPluginFramework\Support\Time;

class JobModel
{
	const TABLE_NAME = '@@wppen_jobs';

	/**
	 * @var DatabaseBroker
	 */
	private $database;

	public function __construct(DatabaseBroker $database)
	{
		$this->database = $database;
	}

	public function completeJob($id)
	{
		$this->delete($id);
	}

	public function createNewJob($post_id)
	{
		ArgCheck::isInt($post_id);

		if ($post_id < 1) {
			throw new \InvalidArgumentException('postId must be an integer value greater than 0');
		}
		$blog_id   = get_current_blog_id();
		$postdata  = get_postdata( $post_id );
		$author_id = $postdata['Author_ID'];

		$data = [
			'offset'         => 0,
			'post_id'        => $post_id,
			'author_id'      => $author_id,
			'blog_id'        => $blog_id,
			'next_round_gmt' => Time::now()->plusMinutes(1)->asSqlTimestamp(),
			'created_gmt'    => Time::now()->asSqlTimestamp()
		];

		if ($this->database->insert(self::TABLE_NAME, $data) === false) {
			throw new \RuntimeException('Unable to add job to the database');
		}
	}

	public function createTable()
	{
		$query = "CREATE TABLE IF NOT EXISTS " . self::TABLE_NAME . " (
			id int(10) NOT NULL AUTO_INCREMENT,
			post_id int(10) NOT NULL,
			author_id int(10) NOT NULL,
			blog_id int(10) NOT NULL,
			offset int(10) NOT NULL,
			next_round_gmt DATETIME NOT NULL,
			created_gmt DATETIME NOT NULL,
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
			'id' => $id
		];

		if ($this->database->delete(self::TABLE_NAME, $where) === false) {
			throw new \RuntimeException('Unable to delete job from database (Post Subscription Plugin)');
		}
	}

	public function dropTable()
	{
		$query = sprintf("DROP TABLE IF EXISTS %s", self::TABLE_NAME);

		if ($this->database->executeQuery($query) === false) {
			throw new \RuntimeException('Unable to delete database for WP Post Subscription Plugin');
		}
	}

	public function getAll() {
		$blog_id    = get_current_blog_id();

		$query = sprintf("SELECT * FROM %s WHERE blog_id = {$blog_id} ORDER BY id", self::TABLE_NAME);

		return $this->database->fetchAll($query);
	}

	public function getNextJob() {
		$blog_id    = get_current_blog_id();

		$query = sprintf(
			"SELECT * FROM %s WHERE next_round_gmt <= '%s' AND blog_id = {$blog_id} ORDER BY id LIMIT 1",
			self::TABLE_NAME,
			Time::now()->asSqlTimestamp()
		);

		return $this->database->fetchAll($query);
	}

	public function removeJobsFor($postId)
	{
		ArgCheck::isInt($postId);
		$blog_id    = get_current_blog_id();

		$where = [
			'post_id' => $postId,
			'blog_id' => $blog_id
		];

		if ($this->database->delete(self::TABLE_NAME, $where) === false) {
			throw new \RuntimeException('Unable to delete job from database (Post Subscription Plugin)');
		}
	}

	public function rescheduleWithNewOffset($id, $addToOffset)
	{
		ArgCheck::isInt($id);
		ArgCheck::isInt($addToOffset);

		$query = sprintf(
			"UPDATE %s SET offset = offset + %u, next_round_gmt = '%s' WHERE id = %u",
			self::TABLE_NAME,
			$addToOffset,
			Time::now()->addSeconds(2)->asSqlTimestamp(),
			$id
		);

		return $this->database->executeQuery($query);
	}
}
