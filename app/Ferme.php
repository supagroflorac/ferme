<?php

namespace Ferme;

use Exception;
use Ferme\UserController;
use Ferme\Wiki\WikisCollection;
use Ferme\ArchivesCollection;
use Ferme\Log;
use Ferme\Alerts;
use Ferme\ArchiveFactory;
use Ferme\Wiki\WikiFactory;
use PDO;
use PDOException;

class Ferme
{
    public Configuration $config;
    public WikisCollection $wikis;
    public ArchivesCollection $archives;
    public Alerts $alerts;
    public UserController $users;
    public PDO $dbConnexion;
    private Log $log;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $this->dbConnect();
        $this->users = new UserController($config);
        $this->wikis = new WikisCollection($config, $this->dbConnexion);
        $this->archives = new ArchivesCollection($config);
        $this->log = new Log($this->config['log_file']);
        $this->alerts = new Alerts();
    }

    public function delete(string $name)
    {
        $this->wikis->delete($name);
        $this->log->write(
            $this->users->whoIsLogged(),
            "Suppression du wiki '$name'"
        );
    }

    public function upgrade(string $name)
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
        $this->createFolderIfNotExist($this->config['ferme_path']);
        $this->createFolderIfNotExist($this->config['archives_path']);
    }

    private function createFolderIfNotExist(string $path)
    {
        if (
            is_dir($path) === false
            and mkdir($path, 0777, true) === false
        ) {
            throw new Exception(
                "Le dossier {$path} n'existe pas et ne peut être créé.",
                1
            );
        }
    }

    public function archiveWiki(string $name)
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

    public function deleteArchive(string $name)
    {
        $this->log->write(
            $this->users->whoIsLogged(),
            "Suppression de l'archive '$name'"
        );
        $this->archives->remove($name);
    }

    public function restore(string $name)
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

    public function getWikiUpgradeSourcePath(): string
    {
        return "packages/" . $this->config['source'] . "/";
    }

    private function dbConnect(): PDO
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
