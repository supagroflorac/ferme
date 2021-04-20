<?php

namespace Ferme\HtmlController\Actions;

class Login extends Action
{
    public function execute()
    {
        if (
            isset($this->post['username'])
            and isset($this->post['password'])
        ) {
            $this->ferme->users->login(
                $this->post['username'],
                $this->post['password']
            );
        }
    }
}
