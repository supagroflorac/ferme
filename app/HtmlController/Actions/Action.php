<?php

namespace Ferme\HtmlController\Actions;

use Ferme\Ferme;

abstract class Action
{
    protected array $get;
    protected array $post;
    protected Ferme $ferme;

    public function __construct(Ferme $ferme, array $get, array $post)
    {
        $this->ferme = $ferme;
        $this->get = $get;
        $this->post = $post;
    }

    abstract public function execute();
}
