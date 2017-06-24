<?php

namespace BilletSimpleAlaska\DAO;

use BilletSimpleAlaska\Domain\Comment;

class CommentDAO extends DAO
{
	/**
	 * @var  BilletSimpleAlaska\DAO\TicketDAO
	 */

	private $ticketDAO;

	/**
	 * @var  BilletSimpleAlaska\DAO\userDAO
	 */

	private $userDAO;

	/**
	 * Return list of all comments for a ticket, sorted by date (most recent last)
	 *
	 * @param int $ticketId in the ticket id
	 *
	 * @return array A list of all comments for the article
	 */

	public function findAllByTicket($ticketId)
	{
		// The associated ticket is retrived only once
		$ticket = $this->ticketDAO->find($ticketId);

		$sql = 'SELECT com_id, com_author, com_content, DATE_FORMAT(com_date, "%d/%m/%Y à %Hh%i") AS date_creation, tick_id, usr_id FROM t_comment WHERE tick_id = ? ORDER BY com_id';
		$result = $this->getDb()->fetchAll($sql, array($ticketId));

		// Convert query result in an array of Domain objects
		$comments = array();

		foreach ($result as $row)
		{
			$comId = $row['com_id'];
			$comment = $this->buildDomainObject($row);
			// The associated ticket is defined for the constructed comment
			$comment->setTicket($ticket);
			$comments[$comId] = $comment;
		}

		return $comments;
	}

	/**
     * Saves a comment into the database.
     *
     * @param \BilletSimpleAlaska\Domain\Comment $comment The comment to save
     */

	public function save(Comment $comment)
	{
		$commentData = array(
			'tick_id' => $comment->getTicket()->getId(),
			'usr_id' => $comment->getAuthor()->getId(),
			'com_content' => $comment->getContent()
		);

		if ($comment->getId())
		{
			// The comment has already been saved : update it
			$this->getDb()->update('t_comment', $commentData, array('com_id' =>$comment->getId()));
		}
		else
		{
			// The comment has never been saved : insert it
			$this->getDb()->insert('t_comment', $commentData);

			// Get the id of the newly created comment and set it on the entity
			$id = $this->getDb()->lastInsertId();
			$comment->setId($id);
		}
	}

	/**
     * Creates a Comment object based on a DB row.
     *
     * @param array $row The DB row containing Comment data.
     * @return \BilletSimpleAlaska\Domain\Comment
     */

	protected function buildDomainObject(array $row)
	{
		$comment = new Comment();
		$comment->setId($row['com_id']);
		$comment->setContent($row['com_content']);
		$comment->setDateCreation($row['date_creation']);
		
		if (array_key_exists('tick_id', $row))
		{
			$ticketId = $row['tick_id'];
			$ticket = $this->ticketDAO->find($ticketId);
			$comment->setTicket($ticket);
		}

		if (array_key_exists('usr_id', $row))
		{
			$userId = $row['usr_id'];
			$user = $this->userDAO->find($userId);
			$comment->setAuthor($user);
		}

		return $comment;
	}

	/**
	 * CommentDAO class setters
	 */

	public function setTicketDAO(TicketDAO $ticketDAO)
	{
		$this->ticketDAO = $ticketDAO;
	}

	public function setUserDAO(UserDAO $userDAO)
	{
		$this->userDAO = $userDAO;
	}
}