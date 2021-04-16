<?php

namespace Ferme;

class Log
{
    private string $file = "";

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function write(string $user, string $action)
    {
        $date = date("Y-m-d G:i:s");
        file_put_contents(
            $this->file,
            "$date : $user : $action\n",
            FILE_APPEND
        );
    }
}
