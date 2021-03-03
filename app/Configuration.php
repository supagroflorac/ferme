<?php

namespace Ferme;

use ArrayAccess;

class Configuration implements ArrayAccess
{
    private $config;

    /**
     * [__construct description]
     * @param [type] $file [description]
     */
    public function __construct($file)
    {
        $this->config = array();

        if (is_file($file)) {
            include $file;
        }

        if (isset($wakkaConfig)) {
            $this->config = $wakkaConfig;
            return;
        }

        if (isset($config)) {
            $this->config = $config;
            return;
        }
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->config[] = $value;
            return;
        }
        $this->config[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->config[$offset]) ? $this->config[$offset] : null;
    }

    /**
     * écris le fichier de configuration
     * @return [type] [description]
     */
    public function write($file, $arrayName = "wakkaConfig")
    {
        $content = "<?php\n\n";
        $content .= "\$$arrayName = array(\n";
        foreach ($this->config as $key => $value) {
            $content .= "    \"" . $key . "\" => \"" . $value . "\",\n";
        }
        $content .= ");\n";
        file_put_contents($file, $content);
    }
}
