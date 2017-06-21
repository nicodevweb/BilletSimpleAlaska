<?php

namespace BilletSimpleAlaska\DAO;

use BilletSimpleAlaska\Domain\Ticket;

class TicketDAO extends DAO
{
	/**
     * Return a list of all articles, sorted by date (most recent first).
     *
     * @return array A list of all articles.
     */

	public function findAll()
	{
		$sql = 'SELECT tick_id, tick_title, tick_content, DATE_FORMAT(tick_date, "%d/%m/%Y à %Hh%i") AS date_creation FROM t_ticket ORDER BY tick_id DESC';
		$result = $this->getDb()->fetchAll($sql);

		// Convert query results to an array of domain objects
		$tickets = array();
		foreach ($result AS $row)
		{
			$ticketId = $row['tick_id'];
			$tickets[$ticketId] = $this->buildDomainObject($row);
		}

		return $tickets;
	}

	/**
     * Return an article matching the supplied id.
     *
     * @param integer $id The ticket id
     *
     * @return \BilletSimpleAlaska\Domain\Ticket
     */

	public function find($id)
	{
		$sql = 'SELECT tick_id, tick_title, tick_content, DATE_FORMAT(tick_date, "%d/%m/%Y à %Hh%i") AS date_creation FROM t_ticket WHERE tick_id = ?';
		$row = $this->getDb()->fetchAssoc($sql, array($id));

		if ($row)
			return $this->buildDomainObject($row);
		else
            throw new \Exception("Aucun billet ne correspond à l'identifiant n°" . $id);
	}

	/**
     * Creates a Ticket object based on a DB row.
     *
     * @param array $row The DB row containing Ticket data.
     * @return \BilletSimpleAlaska\Domain\Ticket
     */

	public function buildDomainObject(array $row)
	{
		$ticket = new Ticket();
		$ticket->setId($row['tick_id']);
		$ticket->setTitle($row['tick_title']);
		$ticket->setContent($row['tick_content']);
		$ticket->setDateCreation($row['date_creation']);

		return $ticket;
	}
}