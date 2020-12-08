<?php

namespace Ferme\CliController\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class Passwd extends Action
{
    public const DESCRIPTION = "Set password for an existing user on a wiki";

    public function execute()
    {
        $nbParameters = count($this->parameters);
        if ($nbParameters !== 3) {
            $this->usage();
            return;
        }

        $wikiName = $this->parameters[0];
        $userName = $this->parameters[1];
        $password = $this->parameters[2];

        $this->ferme->wikis->load();

        if (!isset($this->ferme->wikis[$wikiName])) {
            print("\n$wikiName does not exist.\n\n");
            return;
        }

        if (!$this->ferme->wikis[$wikiName]->isUserExist($userName))
        {
            print("\n$userName does not exist.\n\n");
            return;
        }

        try {
            $this->ferme->wikis[$wikiName]->setPassword($userName, md5($password));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            printf("Error : $error\n");
            return;
        }
    }

    public function usage()
    {
        print("Usage : \n"
            . "  passwd wiki username password \t to set user password\n"
        );
    }
}
