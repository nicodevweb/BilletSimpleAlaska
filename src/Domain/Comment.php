<?php

namespace BilletSimpleAlaska\Domain;

class Comment
{
	private	$id,
			$authorName,
			$content,
			$dateCreation;
	/**
	 *	@var \BilletSimpleAlaska\Ticket
	 */
		
	private	$ticket;

	/**
	 *	@var \BilletSimpleAlaska\User
	 */	
			
	private	$author;

	/**
	 * Comment class getters
	 */

	public function getId()
	{
		return $this->id;
	}

	public function getAuthorName()
	{
		return $this->authorName;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getDateCreation()
	{
		return $this->dateCreation;
	}

	public function getTicket()
	{
		return $this->ticket;
	}

	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * Comment class setters
	 */

	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	public function setDateCreation($dateCreation)
	{
		$this->dateCreation = $dateCreation;
		return $this;
	}

	public function setTicket(Ticket $ticket)
	{
		$this->ticket = $ticket;
		return $this;
	}

	public function setAuthor(User $author)
	{
		$this->author = $author;
		return $this;
	}
}