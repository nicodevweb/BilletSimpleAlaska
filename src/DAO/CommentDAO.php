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

		$sql = 'SELECT com_id, com_author, com_content, DATE_FORMAT(com_date, "%d/%m/%Y à %Hh%i") AS date_creation, tick_id FROM t_comment WHERE tick_id = ? ORDER BY com_id';
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
     * Creates a Comment object based on a DB row.
     *
     * @param array $row The DB row containing Comment data.
     * @return \MicroCMS\Domain\Comment
     */

	public function buildDomainObject(array $row)
	{
		$comment = new Comment();
		$comment->setId($row['com_id']);
		$comment->setAuthor($row['com_author']);
		$comment->setContent($row['com_content']);
		$comment->setDateCreation($row['date_creation']);
		
		if (array_key_exists('tick_id', $row))
		{
			$ticketId = $row['tick_id'];
			$ticket = $this->ticketDAO->find($ticketId);
			$comment->setTicket($ticket);
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
}