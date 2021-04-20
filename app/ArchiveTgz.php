<?php

namespace Ferme;

use Ferme\Archive;
use Ferme\Database;
use PharData;
use PDO;

class ArchiveTgz extends Archive
{
    public function restore(string $fermeFolder, string $archivesFolder, PDO $dbConnexion): string
    {
        $name = $this->getInfos()['name'];
        $sqlFile = $fermeFolder . '/' . $name . '.sql';
        $archivePath = $archivesFolder . $this->filename;

        $archivePhar = new PharData($archivePath);
        $archivePhar->extractTo($fermeFolder);
        $database = new Database($dbConnexion);
        $database->import($sqlFile);
        unlink($sqlFile);
        return $name;
    }
}
