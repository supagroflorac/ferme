<?php

namespace Ferme;

use ArrayAccess;
use Exception;

class Configuration implements ArrayAccess
{
    protected string $arrayName = "\$this->config";
    protected array $config = array();

    public function __construct(string $file)
    {
        if ($this->isFileExist === false) {
            throw new \Exception("{$file} is not a file.");
        }

        $this->loadFile($file);
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

    public function write(string $file)
    {
        $content = "<?php\n\n";
        $content .= "{$this->arrayName} = array(\n";
        foreach ($this->config as $key => $value) {
            $content .= "  \"{$key}\" => \"{$value}\",\n";
        }
        $content .= ");\n";
        file_put_contents($file, $content);
    }

    protected function loadFile($file)
    {
        include $file;

        if (empty($this->config)) {
            throw new Exception("{$file} is not a configuration file.");
            return;
        }
    }

    protected function isFileExist($file): bool
    {
        return is_file($file);
    }
}
