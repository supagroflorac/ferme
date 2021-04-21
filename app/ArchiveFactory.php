<?php

namespace Ferme;

use Exception;
use Ferme\ArchiveTgz;
use Ferme\Configuration;
use Ferme\ArchiveZip;
use Ferme\Wiki\Wiki;
use Ferme\Archive;

class ArchiveFactory
{
    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    public function createFromWiki(Wiki $wiki): Archive
    {
        $archivePath = $wiki->archive();
        return $this->createFromExisting(basename($archivePath));
    }

    public function createFromExisting(string $filename): ArchiveZip
    {
        $archive = new ArchiveZip($filename, $this->config);

        if (!isset($archive)) {
            throw new Exception("Type d'archive none reconnu : $filename", 1);
        }

        return $archive;
    }
}
