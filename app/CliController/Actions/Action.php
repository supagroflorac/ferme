<?php

namespace Ferme\CliController\Actions;

use Ferme\Ferme;

abstract class Action
{
    protected Ferme $ferme;
    protected array $parameters;

    public const DESCRIPTION = 'TODO : Write this help. Sorry...';

    public function __construct(Ferme $ferme, array $parameters)
    {
        $this->ferme = $ferme;
        $this->parameters = $parameters;
    }

    abstract public function execute();
    abstract public function usage();
}
