<?php

namespace BilletSimpleAlaska\Domain;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
	private $id,
			$username,
			$password,
			$salt,
			$role;

	/**
	 * @inheritDoc
	 */

	public function getRoles()
	{
		return array($this->getRole);
	}

	/**
	 * @inheritDoc
	 */

	public function eraseCredentials()
	{
		// Nothing to do here
	}

	/**
	 * User class getters
	 *
	 * @inheritDoc
	 */

	public function getId()
	{
		return $this->id;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getSalt()
	{
		return $this->salt;
	}

	public function getRole()
	{
		return $this->role;
	}

	/**
	 * User class setters
	 */

	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	public function setUsername($username)
	{
		$this->username = $username;
		return $this;
	}

	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}

	public function setSalt($salt)
	{
		$this->salt = $salt;
		return $this;
	}

	public function setRole($role)
	{
		$this->role = $role;
		return $this;
	}
}