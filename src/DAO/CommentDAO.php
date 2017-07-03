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
     * Return a Comment matching the supplied id.
     *
     * @param integer $id The comment id
     *
     * @return \BilletSimpleAlaska\Domain\Comment
     */

	public function find($id)
	{
		$sql = 'SELECT com_id, com_author, com_content, DATE_FORMAT(com_date, "%d/%m/%Y à %Hh%i") AS date_creation, tick_id, usr_id, parent_id, depth, com_nb_report FROM t_comment WHERE com_id = ?';
		$row = $this->getDb()->fetchAssoc($sql, array($id));

		if ($row)
			return $this->buildDomainObject($row);
		else
            throw new \Exception("Aucun commentaire ne correspond à l'identifiant n°" . $id);
	}

	/**
     * Return Comment children matching the supplied id.
     *
     * @param integer $id The parent comment id
     *
     * @return array A list of Domain Objects
     */

	public function findChildren($parentId)
	{
		$sql = 'SELECT com_id, com_author, com_content, DATE_FORMAT(com_date, "%d/%m/%Y à %Hh%i") AS date_creation, tick_id, usr_id, parent_id, depth, com_nb_report FROM t_comment WHERE parent_id = ?';
		$result = $this->getDb()->fetchAll($sql, array($parentId));

		// Convert query results to an array of domain objects
		$comments = array();
		foreach ($result AS $row)
		{
			$comId = $row['com_id'];
			$comments[$comId] = $this->buildDomainObject($row);
		}

		return $comments;
	}


	/**
     * Return a list of all Comments, sorted by date (most recent first).
     *
     * @return array A list of all Comments.
     */

	public function findAll()
	{
		$sql = 'SELECT com_id, com_author, com_content, DATE_FORMAT(com_date, "%d/%m/%Y à %Hh%i") AS date_creation, tick_id, usr_id, parent_id, depth, com_nb_report FROM t_comment ORDER BY com_id DESC';
		$result = $this->getDb()->fetchAll($sql);

		// Convert query results to an array of domain objects
		$comments = array();
		foreach ($result AS $row)
		{
			$comId = $row['com_id'];
			$comments[$comId] = $this->buildDomainObject($row);
		}

		return $comments;
	}

	/**
	 * Return comments with their anwsers associated
	 * 
	 * @param $ticketId The Ticket id
	 *
	 * @param bool $unset_children Verification to unset comments that are answers to others
	 *
	 * @return array of domain objects, if an object has a children, contains an array of domain object
	 */

	public function findAllWithChildren($ticketId, $unsetChildren = TRUE)
	{
		// 2 vars are needed
		// @var $commentsByTicket will never be modified, whereas comments will
		$comments = $commentsByTicket = $this->findAllByTicket($ticketId);

		foreach ($comments as $id => $comment)
		{
			// if the parent is an answer ...
			if ($comment->getParentId() != 0)
			{
				// Then the parent is updated to set its child
				$commentsByTicket[$comment->getParentId()]->setChildren($comment);

				if ($unsetChildren)
				{
					// And the child is removed from parent's depth
					unset($comments[$id]);
				}
			}
		}

		return $comments;
	}

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

		$sql = 'SELECT com_id, com_author, com_content, DATE_FORMAT(com_date, "%d/%m/%Y à %Hh%i") AS date_creation, tick_id, usr_id, parent_id, depth, com_nb_report FROM t_comment WHERE tick_id = ? ORDER BY com_id DESC';
		$result = $this->getDb()->fetchAll($sql, array($ticketId));

		// Convert query result in an array of Domain objects
		$commentsByTicket = array();

		foreach ($result as $row)
		{
			$comId = $row['com_id'];
			$comment = $this->buildDomainObject($row);
			// The associated ticket is defined for the constructed comment
			$comment->setTicket($ticket);
			$commentsByTicket[$comId] = $comment;
		}

		return $commentsByTicket;
	}

	/**
	 * Return list of all comments reported, ordered by number of times it has been reported (Most reported first)
	 *
	 * @return array A list of all comments reported
	 */

	public function findAllByNbReport()
	{
		$sql = 'SELECT com_id, com_author, com_content, DATE_FORMAT(com_date, "%d/%m/%Y à %Hh%i") AS date_creation, tick_id, usr_id, parent_id, depth, com_nb_report FROM t_comment WHERE com_nb_report > 0 ORDER BY com_nb_report DESC';
		$result = $this->getDb()->fetchAll($sql);

		// Convert query results to an array of domain objects
		$comments = array();
		foreach ($result AS $row)
		{
			$comId = $row['com_id'];
			$comments[$comId] = $this->buildDomainObject($row);
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
			'com_author' => $comment->getAuthor()->getUsername(),
			'com_content' => $comment->getContent(),
			'parent_id' => ($comment->getParentId() !== NULL) ? $comment->getParentId() : 0,
			'depth' => ($comment->getDepth() !== NULL) ? $comment->getDepth() : 0,
			'com_nb_report' => $comment->getNbReport()
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
	 * Delete a comment
	 *
	 * @param integer id The comment id
	 */

	public function delete($id)
	{
		// Delete the comment
		$this->getDb()->delete('t_comment', array('com_id' => $id));
	}

	/**
     * Removes all comments for a Ticket
     *
     * @param $ticketId The id of the Ticket
     */
	
    public function deleteAllByTicket($ticketId) {
        $this->getDb()->delete('t_comment', array('tick_id' => $ticketId));
    }

    /**
     * Removes all comments for a user
     *
     * @param integer $userId The id of the user
     */
    public function deleteAllByUser($userId) {
        $this->getDb()->delete('t_comment', array('usr_id' => $userId));
    }

    /**
     * Reports a comment
     *
     * @param $id The comment id
     */

    public function reportComment($commentId)
    {
    	$comment = $this->find($commentId);
    	$nbReport = $comment-> getNbReport() + 1;
    	$comment->setNbReport($nbReport);

    	$this->getDb()->update('t_comment', array('com_nb_report' => $comment->getNbReport()), array('com_id' => $commentId));
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
		$comment->setParentId($row['parent_id']);
		$comment->setDepth($row['depth']);
		$comment->setNbReport($row['com_nb_report']);
		
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