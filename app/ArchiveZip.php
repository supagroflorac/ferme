<?php

namespace Ferme;

use Ferme\Archive;
use Ferme\Database;
use ZipArchive;
use Exception;
use PDO;

class ArchiveZip extends Archive
{
    public function restore(string $fermeFolder, string $archivesFolder, PDO $dbConnexion): string
    {
        $name = $this->getInfos()['name'];
        $sqlFile = "{$fermeFolder}/{$name}.sql";
        $archivePath = $archivesFolder . $this->filename;

        $archive = new ZipArchive();
        if ($archive->open($archivePath) === false) {
            throw new Exception(
                "Impossible d'ouvrir l'archive : $this->filename.",
                1
            );
        };

        $archive->extractTo($fermeFolder);
        $archive->close();

        $database = new Database($dbConnexion);
        $database->import($sqlFile);

        unlink($sqlFile);
        return $name;
    }
}
