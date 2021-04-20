<?php

namespace Ferme;

class Password
{
    public static function random(int $length): string
    {
        $allowedChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%*_";
        $password = '';
        $allowedCharsLength = mb_strlen($allowedChars, '8bit') - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= $allowedChars[random_int(0, $allowedCharsLength)];
        }
        return $password;
    }
}
