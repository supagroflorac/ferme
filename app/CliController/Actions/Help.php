<?php

namespace Ferme\CliController\Actions;

class Help extends Action
{
    public const DESCRIPTION = "This help";

    public function execute()
    {
        if (count($this->parameters) > 0) {
            $this->commandUsage($this->parameters[0]);
            return;
        }

        $this->usage();
    }

    public function usage()
    {
        print("\nUsage : \n  command [arguments] \n");
        print("\nGet help on command : \n  help command \n");
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

    private function commandUsage($commandName)
    {
        $class = "\\Ferme\\CliController\\Actions\\" . ucfirst($commandName);
        print($class::usage());
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
