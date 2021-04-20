<?php

namespace Ferme;

use ArrayAccess;

class Configuration implements ArrayAccess
{
    private array $config = array();

    public function __construct($file)
    {
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

    public function write(string $file, string $arrayName = "wakkaConfig")
    {
        $content = "<?php\n\n";
        $content .= "\${$arrayName} = array(\n";
        foreach ($this->config as $key => $value) {
            $content .= "  \"{$key}\" => \"{$value}\",\n";
        }
        $content .= ");\n";
        file_put_contents($file, $content);
    }
}
