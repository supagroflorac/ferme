<?php

namespace Ferme;

use Exception;
use Ferme\ArchiveTgz;
use Ferme\ArchiveZip;

class ArchiveFactory
{
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function createFromWiki($wiki)
    {
        $archivePath = $wiki->archive();
        return $this->createFromExisting(basename($archivePath));
    }

    public function createFromExisting($filename)
    {
        $fileType = substr($filename, -3);
        if ($fileType === "tgz") {
            $archive = new ArchiveTgz($filename, $this->config);
        }

        if ($fileType === "zip") {
            $archive = new ArchiveZip($filename, $this->config);
        }

        if (!isset($archive)) {
            throw new Exception("Type d'archive none reconnu : $filename", 1);
        }

        return $archive;
    }
}
