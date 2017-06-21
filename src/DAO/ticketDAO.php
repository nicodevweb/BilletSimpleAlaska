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
		$sql = 'SELECT * FROM t_ticket ORDER BY tick_id DESC';
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
		$ticket->setDateCreation($row['tick_date']);

		return $ticket;
	}
}