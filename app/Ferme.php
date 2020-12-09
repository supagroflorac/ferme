<?php

namespace Ferme;

use Exception;
use Ferme\UserController;
use Ferme\WikisCollection;
use Ferme\ArchivesCollection;
use Ferme\Log;
use Ferme\Alerts;
use Ferme\ArchiveFactory;
use Ferme\WikiFactory;
use PDO;
use PDOException;

class Ferme
{
    public $config;
    public $wikis;
    public $archives;
    public $alerts;
    public $users;
    public $dbConnexion;
    private $log;

    /*************************************************************************
     * constructor
     * **********************************************************************/
    public function __construct($config)
    {
        $this->config = $config;
        $this->dbConnect();
        $this->users = new UserController($config);
        $this->wikis = new WikisCollection($config, $this->dbConnexion);
        $this->archives = new ArchivesCollection($config);
        $this->log = new Log($this->config['log_file']);
        $this->alerts = new Alerts();
    }

    /*************************************************************************
     * Gestion des Wikis
     ************************************************************************/
    public function delete($name)
    {
        $this->wikis->delete($name);
        $this->log->write(
            $this->users->whoIsLogged(),
            "Suppression du wiki '$name'"
        );
    }

    public function upgrade($name)
    {
        $this->log->write(
            $this->users->whoIsLogged(),
            "Mise à jour du wiki '$name'"
        );

        $this->wikis[$name]->upgrade(
            $this->getWikiUpgradeSourcePath()
        );
    }

    public function checkInstallation()
    {
        if (!is_dir($this->config['ferme_path'])) {
            if (!mkdir($this->config['ferme_path'], 0777, true)) {
                throw new Exception(
                    "Le dossier d'installation des wiki ("
                        . $this->config['ferme_path']
                        . ") ne peut être créé.",
                    1
                );
            }
        }

        if (!is_dir($this->config['archives_path'])) {
            if (!mkdir($this->config['archives_path'], 0777, true)) {
                throw new Exception(
                    "Le dossier des archives ("
                    . $this->config['archives_path']
                    . ") ne peut être créé.",
                    1
                );
            }
        }
    }

    /*************************************************************************
     * Gestion des archives
     ************************************************************************/
    public function archiveWiki($name)
    {
        $this->log->write(
            $this->users->whoIsLogged(),
            "Archive le wiki '$name'"
        );

        $archiveFactory = new ArchiveFactory($this->config);
        $archive = $archiveFactory->createFromWiki($this->wikis[$name]);
        $archiveName = $archive->getInfos()['filename'];
        $this->archives->add($archiveName, $archive);
    }

    public function deleteArchive($name)
    {
        $this->log->write(
            $this->users->whoIsLogged(),
            "Suppression de l'archive '$name'"
        );
        $this->archives->remove($name);
    }

    public function restore($name)
    {
        $archive = $this->archives[$name];
        $wikiName = $archive->getInfos()['name'];

        if ($this->wikis->exist($wikiName)) {
            throw new Exception("Un wiki de ce nom ($wikiName) existe déjà.");
        }

        $this->log->write(
            $this->users->whoIsLogged(),
            "Restauration de l'archive '$name'"
        );
        $wikiFactory = new WikiFactory($this->config, $this->dbConnexion);
        $wiki = $wikiFactory->createFromArchive($archive);
        $this->wikis->add($wiki->name, $wiki);
    }

    public function getWikiUpgradeSourcePath()
    {
        return "packages/" . $this->config['source'] . "/";
    }

    /**
     * Établis la connexion a la base de donnée si ce n'est pas déjà fait.
     * @return PDO la connexion a la base de donnée
     */
    private function dbConnect()
    {
        $dsn = 'mysql:host=' . $this->config['db_host'] . ';';
        $dsn .= 'dbname=' . $this->config['db_name'] . ';';

        try {
            $this->dbConnexion = new PDO(
                $dsn,
                $this->config['db_user'],
                $this->config['db_password']
            );
            return $this->dbConnexion;
        } catch (PDOException $e) {
            throw new Exception(
                "Impossible de se connecter à la base de donnée : "
                . $e->getMessage(),
                1
            );
        }
    }
}
