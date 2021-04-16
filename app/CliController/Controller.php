<?php

namespace Ferme\CliController;

use Ferme\Ferme;

/**
 * Classe Controller
 *
 * gère les entrées ($post et $get)
 * @package Ferme
 * @author  Florestan Bredow <florestan.bredow@supagro.fr>
 * @version 0.0.1 (Git: $Id$)
 * @copyright 2015 Florestan Bredow
 */
class Controller
{
    private $ferme;

    public function __construct(Ferme $ferme)
    {
        $this->ferme = $ferme;
    }

    public function run(array $argv)
    {
        $action = "help";
        $parameters = array_slice($argv, 2);

        if (isset($argv[1])) {
            $action = $argv[1];
        }

        $this->action($action, $parameters);
    }

    private function action(string $action, array $parameters)
    {

        $className = "Ferme\\CliController\Actions\\" . ucfirst($action);
        if (
            !class_exists($className)
            or $action === 'action'
        ) {
            print("\nCommand '$action' is not defined.\n\n");
            return;
        }

        $action = new $className($this->ferme, $parameters);
        $action->execute();
    }
}
