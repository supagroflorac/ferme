<?php

namespace Ferme;

use Exception;
use Ferme\Configuration as FermeConfiguration;

class Archive implements InterfaceObject
{
    public string $filename;
    public string $name;
    public string $url;
    public int $creationDate;

    private FermeConfiguration $fermeConfig;
    
    public function __construct(string $filename, FermeConfiguration $fermeConfig)
    {
        $this->filename = $filename;
        $this->fermeConfig = $fermeConfig;
        $this->name = $this->getName($filename);
        $this->url = $this->getUrl($filename);
        $this->creationDate = $this->getCreationDate($filename);
    }
    
    public function delete()
    {
        if (unlink($this->fermeConfig['archives_path'] . $this->filename) === false) {
            throw new Exception('Impossible de supprimer l\'archive', 1);
        }
    }
    
    public function size()
    {
        return filesize($this->fermeConfig['archives_path'] . $this->filename);
    }
    
    private function getURL(string $filename): string
    {
        $name = substr($filename, 0, -4);
        $url = '?download=' . $name;
        return $url;
    }

    private function getName(string $filename): string
    {
        return substr($this->filename, 0, -16);
    }

    private function getCreationDate(string $filename): int
    {
        $strDate = substr($filename, -16, 12);
        return mktime(
            (int) substr($strDate, 8, 2),
            (int) substr($strDate, 10, 2),
            0,
            (int) substr($strDate, 4, 2),
            (int) substr($strDate, 6, 2),
            (int) substr($strDate, 0, 4),
        );
    }
}
