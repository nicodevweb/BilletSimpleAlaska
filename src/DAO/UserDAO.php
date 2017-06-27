<?php

namespace BilletSimpleAlaska\DAO;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use BilletSimpleAlaska\Domain\User;

class UserDAO extends DAO implements UserProviderInterface
{
	/**
     * Return a list of all Users, sorted by date (most recent first).
     *
     * @return array A list of all Users.
     */

	public function findAll()
	{
		$sql = 'SELECT * FROM t_user ORDER BY usr_id DESC';
		$request = $this->getDb()->fetchAll($sql);

		// Convert query result to an array of Domain Objects
		$users = array();
		foreach ($request AS $row)
		{
			$userId = $row['usr_id'];
			$users[$userId] = $this->buildDomainObject($row);
		}

		return $users;
	}

	/**
	 * Returns a user matching the supplied id
	 *
	 * @param integer $id The user id
	 *
	 * @return \MicroCMS\Domain\User|throws an exception if no matching user is found
	 */

	public function find($id)
	{
		$sql = 'SELECT * FROM t_user WHERE usr_id = ?';
		$row = $this->getDb()->fetchAssoc($sql, array($id));

		if ($row)
			return $this->buildDomainObject($row);
		else
			throw new \Exception("No user matching id " . $id);
	}

	/**
     * {@inheritDoc}
     */

	public function loadUserByUsername($username)
	{
		$sql = 'SELECT * FROM t_user WHERE usr_name = ?';
		$row = $this->getDb()->fetchAssoc($sql, array($username));

		if ($row)
			return $this->buildDomainObject($row);
		else
			throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));		
	}

	/**
     * {@inheritDoc}
     */

	public function refreshUser(UserInterface $user)
	{
		$class = get_class($user);
		if (!$this->supportsClass($class))
		{
			throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
		}

		return $this->loadUserByUsername($user->getUsername());
	}

    /**
     * {@inheritDoc}
     */

    public function supportsClass($class)
    {
    	return 'BilletSimpleAlaska\Domain\User' === $class;
    }

    /**
     * Creates a User object based on DB row
     *
     * @param array $row The DB row containing User data
     * @return \BilletSimpleAlaska\Domain\User
     */

    protected function buildDomainObject(array $row)
    {
    	$user = new User();
    	$user->setId($row['usr_id']);
    	$user->setUsername($row['usr_name']);
    	$user->setPassword($row['usr_password']);
    	$user->setSalt($row['usr_salt']);
    	$user->setRole($row['usr_role']);

    	return $user;
    }

    /**
     * Saves a user into the database.
     *
     * @param \BilletSimpleAlaska\Domain\User $user The user to save
     */

    public function save(User $user) {
        $userData = array(
            'usr_name' => $user->getUsername(),
            'usr_salt' => $user->getSalt(),
            'usr_password' => $user->getPassword(),
            'usr_role' => $user->getRole()
            );

        if ($user->getId()) {
            // The user has already been saved : update it
            $this->getDb()->update('t_user', $userData, array('usr_id' => $user->getId()));
        } else {
            // The user has never been saved : insert it
            $this->getDb()->insert('t_user', $userData);
            // Get the id of the newly created user and set it on the entity.
            $id = $this->getDb()->lastInsertId();
            $user->setId($id);
        }
    }

    /**
     * Removes a user from the database.
     *
     * @param @param integer $id The user id.
     */
    
    public function delete($id) {
        // Delete the user
        $this->getDb()->delete('t_user', array('usr_id' => $id));
    }
}