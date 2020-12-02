<?php

namespace Ferme\CliController\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class Help extends Action
{
    public const DESCRIPTION = "This help";

    public function execute()
    {
        print("\nUsage : \n  command [arguments] \n");
        print("\nGet help on command : \n  command --help \n");
        print("\n");
        print("Available commands : \n");

        $listActions = $this->getListActions();
        foreach ($listActions as $action) {
            $class = "\\Ferme\\CliController\\Actions\\" . ucfirst($action);
            $description = $class::DESCRIPTION;
            $action = strtolower($action);
            print("  $action\t\t$description\n");
        }
        print("\n");
    }

    private function getListActions(): array
    {
        $list = scandir(__DIR__);

        foreach ($list as $key => $file) {
            if ($file === '.' or $file === '..' or $file === 'Action.php') {
                unset($list[$key]);
                continue;
            }
            $list[$key] = basename($file, '.php');
        }

        return $list;
    }
}
