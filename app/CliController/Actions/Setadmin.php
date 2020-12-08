<?php

namespace Ferme\CliController\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class Setadmin extends Action
{
    public const DESCRIPTION = "Adds the user FermeAdmin (replace existing)";

    public function execute()
    {
        $nbParameters = count($this->parameters);
        if ($nbParameters !== 1) {
            $this->usage();
            return;
        }

        $this->ferme->wikis->load();
        if ($this->parameters[0] === '--all') {
            print("\n");
            $this->setAdminForAllWikis();
            print("\n");
            return;
        }

        print("\n");
        $this->setAdminForOneWiki($this->parameters[0]);
        print("\n");
    }

    private function setAdminForAllWikis()
    {
        $listWiki = $this->ferme->wikis->search();
        foreach ($listWiki as $wiki) {
            $this->setAdminForOneWiki($wiki->name);
        }
    }

    private function setAdminForOneWiki(string $wikiName)
    {
        $userName = 'FermeAdmin';
        $mail = $this->ferme->config['mail_from'];
        $md5Password = $this->ferme->config['admin_password'];

        $this->ferme->wikis->load();

        print("Set FermeAdmin account for $wikiName : ");

        if (!isset($this->ferme->wikis[$wikiName])) {
            print("Error (does not exist)\n");
            return;
        }

        $wiki = $this->ferme->wikis[$wikiName];

        try {
            if ($wiki->isUserExist($userName)) {
                $wiki->addAdminUser($userName, $mail, $md5Password);
                print("OK\n");
                return;
            }
            $wiki->setPassword($userName, $md5Password);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            printf("Error : $error\n");
            return;
        }
        print("OK\n");
    }

    public function usage()
    {
        print("Usage : \n"
            . "  setadmin wiki  \t to set admin user to one wiki\n"
            . "  setadmin --all \t to set admin user for all wiki\n"
        );
    }
}
