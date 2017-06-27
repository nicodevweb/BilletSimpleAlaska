<?php

namespace BilletSimpleAlaska\DAO;

use BilletSimpleAlaska\Domain\Ticket;

class TicketDAO extends DAO
{
	/**
     * Return a list of all Tickets, sorted by date (most recent first).
     *
     * @return array A list of all Tickets.
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
     * Return a Ticket matching the supplied id.
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

	/**
     * Saves an article into the database.
     *
     * @param \BilletSimpleAlaska\Domain\Ticket $ticket The ticket to save
     */

    public function save(Ticket $ticket)
    {
    	$ticketData = array(
    		'tick_title' => $ticket->getTitle(),
    		'tick_content' => $ticket->getContent()
    	);

    	if ($ticket->getId())
    	{
    		// The ticket has already been saved : update it
    		$this->getDb()->update('t_ticket', $ticketData, array('tick_id' => $ticket->getId()));
    	}
    	else
    	{
    		// The ticket has never been saved : insert it
    		$this->getDb()->insert('t_ticket', $ticketData);
    	}

		// Get the id of the newly created comment and set it on the entity
		$id = $this->getDb()->lastInsertId();
		$ticket->setId($id);    	
    }
	
	/**
     * Saves a Ticket into the database.
     *
     * @param integer $id The Ticket id.
     */

	public function delete($id)
	{
		// Delete the ticket
		$this->getDb()->delete('t_ticket', array('tick_id' => $id));
	}
}