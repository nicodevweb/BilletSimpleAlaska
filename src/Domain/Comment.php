<?php

namespace BilletSimpleAlaska\Domain;

class Comment
{
	private	$id,
			$content,
			$dateCreation,
			$parentId,
			$children = [],
			$depth,
			$nbReport;
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

	public function getParentId()
	{
		return $this->parentId;
	}

	public function getChildren()
	{
		return $this->children;
	}

	public function getDepth()
	{
		return $this->depth;
	}

	public function getNbReport()
	{
		return $this->nbReport;
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

	public function setParentId($parentId)
	{
		$this->parentId = $parentId;
		return $this;
	}

	public function setChildren(Comment $child)
	{
		$this->children[] = $child;
		return $this;
	}

	public function setDepth($depth)
	{
		$this->depth = $depth;
		return $this;
	}

	public function setNbReport($nbReport)
	{
		$this->nbReport = $nbReport;
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