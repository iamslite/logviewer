<?php
/**
 * @file
 * Provider of users based on a file.
 */

namespace AppBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

class FileUserProvider implements UserProviderInterface
{
    protected $filename;

    protected $users;

    protected $logger;

    public function __construct($filename, LoggerInterface $logger = null)
    {
        $this->filename = $filename;
        $this->logger = $logger;

        $this->logger->info('FileUserProvider');
    }

    public function loadUserByUsername($username)
    {
        $this->logger->info('loadUserByUsername');
        if (empty($this->users)) {
            $this->loadUsers();
        }

        if (isset($this->users[$username])) {
            $user = $this->users[$username];
            return new User($user['username'], $user['password'], $user['roles']);
        }
        // else
        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist', $username)
        );
    }


    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof \AppBundle\Security\User\User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $this->loadUsers();

        return $this->loadUserByUsername($user->getUsername());
    }

    
    public function supportsClass($class)
    {
        $this->logger->info('supportsClass: ' . $class);
        return $class === 'AppBundle\\Security\\User\\User';
    }


    public function loadUsers()
    {
        $this->logger->info('Loading');
        
        try {
            $userfile = @fopen($this->filename, 'r');
        }
        catch (Exception $e)
        {
            $userfile = false;
        }

        if (empty($userfile)) {
            $this->logger->critical('Could not open the user file: ' . $this->filename);
            return;
        }

        $this->logger->info('Open');

        $this->users = array();

        while (!feof($userfile)) {
            $line = fgetcsv($userfile, 4096, ':');

            $this->logger->info(print_r($line, true));

            if (empty($line) || substr($line[0], 0, 1) == '#') {
                continue;
            }
            
            $this->users[$line[0]] = array(
                'username' => $line[0],
                'password' => $line[1],
                'roles' => str_getcsv($line[2]),
            );
        }

        $this->logger->info('Loaded');

        fclose($userfile);
    }
}