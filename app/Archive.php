<?php

namespace Ferme;

use Exception;
use Ferme\Configuration;

class Archive implements InterfaceObject
{
    public string $filename;
    private Configuration $config;

    public function __construct(string $filename, Configuration $config)
    {
        $this->filename = $filename;
        $this->config = $config;
    }

    public function getInfos(): array
    {
        return array(
            'name' => substr($this->filename, 0, -16),
            'filename' => $this->filename,
            'date' => $this->readCreationDateFromFilename(),
            'url' => $this->getArchiveURL(),
            'size' => $this->getArchiveWeight(),
        );
    }

    public function getArchiveURL(): string
    {
        $name = substr($this->filename, 0, -4);
        $url = '?download=' . $name;
        return $url;
    }

    public function delete()
    {
        if (unlink($this->config['archives_path'] . $this->filename) === false) {
            throw new Exception('Impossible de supprimer l\'archive', 1);
        }
    }

    private function readCreationDateFromFilename(): int
    {
        $strDate = substr($this->filename, -16, 12);
        return mktime(
            (int) substr($strDate, 8, 2),
            (int) substr($strDate, 10, 2),
            0,
            (int) substr($strDate, 4, 2),
            (int) substr($strDate, 6, 2),
            (int) substr($strDate, 0, 4),
        );
    }

    private function getArchiveWeight()
    {
        return filesize($this->config['archives_path'] . $this->filename);
    }
}
