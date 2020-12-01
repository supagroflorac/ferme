<?php

namespace Ferme\CliController\Actions;

abstract class Action
{
    protected \Ferme\Ferme $ferme;
    protected array $parameters;

    protected const DESCRIPTION = 'TODO : Write this help. Sorry...';

    public function __construct(\Ferme\Ferme $ferme, array $parameters)
    {
        $this->ferme = $ferme;
        $this->parameters = $parameters;
    }

    abstract public function execute();
}
