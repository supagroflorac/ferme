<?php

namespace Ferme\HtmlController\Actions;

class Logout extends Action
{
    public function execute()
    {
        $this->ferme->users->logout();
    }
}
