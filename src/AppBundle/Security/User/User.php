<?php
/**
 * @file
 * User object
 */

namespace AppBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    /**
     * @var string The username
     */
    private $username;

    /**
     * @var string The (encoded) password
     */
    private $password;

    /**
     * @var array The roles (as strings)
     */
    private $roles;

    public function __construct($username, $password, array $roles)
    {
        $this->username = $username;
        $this->password = $password;
        $this->roles = $roles;
    }

    /**
     * @inheritdoc
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @inheritdoc
     */
    public function getSalt()
    {
    }

    /**
     * @inheritdoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials()
    {
    }
}