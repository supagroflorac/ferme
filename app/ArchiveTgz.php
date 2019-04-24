<?php
namespace Ferme;

class ArchiveTgz extends Archive
{
    public function restore($fermeFolder, $archivesFolder, $dbConnexion)
    {
        $name = $this->getInfos()['name'];
        $sqlFile = $fermeFolder . '/' . $name . '.sql';
        $archivePath = $archivesFolder . $this->filename;

        $archivePhar = new \PharData($archivePath);
        $archivePhar->extractTo($fermeFolder);
        $database = new Database($dbConnexion);
        $database->import($sqlFile);
        unlink($sqlFile);
        return $name;
    }
}
