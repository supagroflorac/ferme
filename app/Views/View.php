<?php

namespace Ferme\Views;

use Ferme\Ferme;

abstract class View
{
    protected Ferme $ferme;

    public function __construct(Ferme $ferme)
    {
        $this->ferme = $ferme;
    }

    abstract public function show();
}
