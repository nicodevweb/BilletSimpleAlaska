<?php

namespace BilletSimpleAlaska\Domain;

class Ticket
{
	private	$id,
			$title,
			$content,
			$dateCreation;

	/**
	 *	Ticket class getters
	 */

	public function getId()
	{
		return $this->id;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getDateCreation()
	{
		return $this->dateCreation;
	}

	/**
	 *	Ticket class setters
	 */

	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	public function setTitle($title)
	{
		$this->title = $title;
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

	/**
	 * Reduce @var $content to 600 characters max and set attribute
	 */

	public function setPortionContent($content)
	{
		if (strlen($content) <= 200)
		{
			$this->setContent($content);
		}
		else
		{
			$debut = substr($content, 0, 600);
      		$debut = substr($debut, 0, strrpos($debut, ' ')) . '...';

      		$this->setContent($debut);
		}
	}
}