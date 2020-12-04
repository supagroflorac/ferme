<?php

namespace Ferme;

/**
 * Classe UserController
 *
 * gère les entrées ($_POST et $_GET)
 * @package Ferme
 * @author  Florestan Bredow <florestan.bredow@supagro.fr>
 * @version 0.0.1 (Git: $Id$)
 * @copyright 2020 Florestan Bredow
 */
class Password
{
    public static function random(int $length): string
    {
        $allowedChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_";
        $password = '';
        $allowedCharsLength = mb_strlen($allowedChars, '8bit') - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= $allowedChars[random_int(0, $allowedCharsLength)];
        }
        return $password;
    }
}