<?php

namespace Ferme;

use Ferme\Configuration;
use Exception;

class UserController
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function login(string $username, string $password): bool
    {
        $listUsers = $this->configuration['users'];
        foreach ($listUsers as $validUsername => $hash) {
            if (
                $validUsername === $username
                and password_verify($password, $hash)
            ) {
                $_SESSION['username'] = $username;
                $_SESSION['logged'] = true;
                return true;
            }
        }
        return false;
    }

    public function logout()
    {
        foreach (array('username', 'logged') as $value) {
            if (isset($_SESSION[$value])) {
                unset($_SESSION[$value]);
            }
        }
    }

    public function isLogged(): bool
    {
        if (
            isset($_SESSION['username'])
            and isset($_SESSION['logged'])
            and true == $_SESSION
        ) {
            return true;
        }
        return false;
    }

    public function whoIsLogged(): string
    {
        if (isset($_SESSION['username'])) {
            return $_SESSION['username'];
        }
        return '';
    }

    public function isAuthorized()
    {
        if (!$this->isLogged()) {
            throw new Exception("Accès interdit", 1);
        }
    }
}
