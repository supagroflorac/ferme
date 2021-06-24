<?php

namespace Ferme\Wiki;

use Ferme\Configuration as FermeConfiguration;
use ArrayAccess;
use Exception;

class Informations extends FermeConfiguration
{
    protected string $arrayName = "\$wakkaInfos";

    protected function loadFile($file)
    {
        
        $this->config = array();
        
        if (is_file($file)) {
            include $file;
            
            if (isset($wakkaInfos) === false) {
                throw new Exception("{$file} is not a wakka information file.");
            }
            $this->config = $wakkaInfos;
        }

        if (isset($this->config['mail']) === false) {
            $this->config['mail'] = 'nomail';
        }

        if (isset($this->config['description']) === false) {
            $this->config['description'] = 'Pas de description.';
        }

        if (isset($this->config['date']) === false) {
            $this->config['date'] = 0;
        }
    }  
}