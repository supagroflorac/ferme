<?php

namespace Ferme\HtmlController\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
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
