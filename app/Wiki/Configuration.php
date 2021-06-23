<?php

namespace Ferme\Wiki;

use Ferme\Configuration as FermeConfiguration;
use ArrayAccess;
use Exception;

class Configuration extends FermeConfiguration
{
    protected string $arrayName = "\$wakkaConfig";

    protected function loadFile($file)
    {
        include $file;

        if (isset($wakkaConfig) === false) {
            throw new Exception("{$file} is not a wakka config file.");
        }

        $this->config = $wakkaConfig;
    }
}