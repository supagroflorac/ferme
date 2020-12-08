<?php

namespace Ferme;

/**
 * Classe wiki
 *
 * gère les opération sur un wiki
 * @package Ferme
 * @author  Florestan Bredow <florestan.bredow@supagro.fr>
 * @version 0.1.1 (Git: $Id$)
 * @copyright 2013 Florestan Bredow
 */
class Wiki implements InterfaceObject
{
    public $path;
    public $name;
    private $fermeConfig;
    private $dbConnexion;
    public $infos = null;
    private $config = null;

    /**
     * Constructeur
     * @param string        $path         chemin vers le wiki
     * @param Configuration $config       configuration de la ferme
     * @param PDO           $dbConnexion connexion vers la base de donnée (déjà
     * établie)
     */
    public function __construct($name, $path, $fermeConfig, $dbConnexion)
    {
        $this->name = $name;
        $this->path = $path;
        $this->fermeConfig = $fermeConfig;
        $this->dbConnexion = $dbConnexion;
    }

    public function loadConfiguration()
    {
        if (is_null($this->config)) {
            $filePath = $this->path . "wakka.config.php";
            if (!file_exists($filePath)) {
                return false;
            }
            $this->config = new Configuration($filePath);
        }
        return true;
    }

    /**
     * Calcule la taille occupée par les fichiers et la base de donnée du wiki
     * @return array Liste des informations sur le wiki avec au moins la taille
     * de la base de donnée et des fichiers
     */
    public function getFilesDiskUsage()
    {
        $file = new \Files\File($this->path . '/files');
        return $file->diskUsage();
    }

    /**
     * Retourne la date a laquelle le wiki a été modifié pour la dernière fois.
     * (création ou modification de page)
     * @return DateTime La date et l'heure à laquelle la dernière modification a
     *                  eu lieu.
     */
    public function getLasPageModificationDateTime()
    {
        $tablePages = $this->config['table_prefix'] . 'pages';
        // Binary sert ici a faire une comparaison sensible a la casse.
        $query = $this->dbConnexion->query(
            "SHOW TABLE STATUS WHERE binary Name = '$tablePages';"
        );
        $result = $query->fetchAll();

        return new \DateTime($result[0]['Update_time']);
    }

    /**
     * Renvois les informations sur le wiki.
     *
     * @return array
     */
    public function getInfos()
    {
        if (is_null($this->infos)) {
            return $this->loadInfos();
        }
        return $this->infos;
    }

    /**
     * Supprime ce wiki.
     */
    public function delete()
    {
        $database = $this->dbConnexion;
        $fermePath = $this->fermeConfig['ferme_path'];

        //Supprime la base de donnée
        $tables = $this->getDBTablesList();

        foreach ($tables as $tableName) {
            $sth = $database->prepare("DROP TABLE IF EXISTS " . $tableName);
            if (!$sth->execute()) {
                throw new \Exception(
                    "Erreur lors de la suppression de la base de donnée",
                    1
                );
            }
        }

        //Supprimer les fichiers
        $wikiFiles = new \Files\File($fermePath . $this->config['wakka_name']);
        $wikiFiles->delete();
    }

    /**
     * Crée une archive de ce wiki.
     */
    public function archive()
    {
        $wikiName = $this->config['wakka_name'];
        $archiveFilename = $this->fermeConfig['archives_path']
            . $wikiName
            . date("YmdHi")
            . '.zip';
        $wikiPath = realpath($this->fermeConfig['ferme_path'] . $wikiName);
        $sqlFile = $this->fermeConfig['tmp_path'] . $wikiName . '.sql';

        // Dump de la base de donnée.
        $database = new Database($this->dbConnexion);
        $database->export($sqlFile, $this->config['table_prefix']);

        // Création de l'archive
        $archive = new \ZipArchive();

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $wikiPath,
                \RecursiveDirectoryIterator::SKIP_DOTS // Evite les repertoire .. et .
            )
        );

        if ($archive->open($archiveFilename, \ZipArchive::CREATE) !== true) {
            throw new \Exception(
                "Erreur lors de la création de : \"$archiveFilename\".",
                1
            );
        }
        $prefix_length = strlen(realpath($this->fermeConfig['ferme_path'])) + 1;
        foreach ($files as $key => $file) {
            $fileToAdd = substr($file, $prefix_length);
            $archive->addFile($file, $fileToAdd);
        }
        $archive->addFile($sqlFile, basename($sqlFile));
        $archive->close();

        unset($archive);
        unlink($sqlFile);

        return $archiveFilename;
    }

    public function upgrade($srcPath)
    {
        // Delete wiki files
        $fileToKeep = array(
            '.', '..', 'wakka.config.php', 'wakka.infos.php', 'files', 'themes', 'custom'
        );

        if ($res = opendir($this->path)) {
            while (($filename = readdir($res)) !== false) {
                if (!in_array($filename, $fileToKeep)) {
                    $file = new \Files\File($this->path . '/' . $filename);
                    $file->delete();
                }
            }
            closedir($res);
        }

        // Copies new files
        if ($res = opendir($srcPath)) {
            while (($filename = readdir($res)) !== false) {
                if (!in_array($filename, $fileToKeep)) {
                    $file = new \Files\File($srcPath . $filename);
                    $file->copy($this->path . '/' . $filename);
                }
            }
            closedir($res);
        }

        // Updates the version and release number.
        include_once "${srcPath}/includes/constants.php";
        $this->setRelease(YESWIKI_VERSION, YESWIKI_RELEASE);
    }

    /**
     * Retourne la version de YesWiki (cercopitheque, doriphore...)
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->config['yeswiki_version'];
    }

    /**
     * Retourne le numéro de release du wiki
     *
     * @return string
     */
    public function getRelease()
    {
        return $this->config['yeswiki_release'];
    }

     /**
     * Change user password
     *
     * @param string $username
     * @param string $md5Password
     * @return void
     */
    public function setPassword(string $username, string $md5Password)
    {
        $database = $this->dbConnexion;
        $table = $this->name . "_users";

        $sqlQuery = "UPDATE ${table} SET password = :md5Password WHERE name = :username";
        $sth = $database->prepare($sqlQuery);
        $values = array(
            ':md5Password' => $md5Password,
            ':username' => $username,
        );

        if ($sth->execute($values) === false) {
            throw new \Exception(
                "Impossible de changer le mot de passe de ${username}",
                1
            );
        }
    }

    public function loadInfos()
    {
        unset($this->infos);

        $filePath = $this->path . "wakka.infos.php";

        $wakkaInfos = array(
            'mail' => 'nomail',
            'description' => 'Pas de description.',
            'date' => 0,
        );

        if (file_exists($filePath)) {
            include $filePath;
        }

        $this->infos = $wakkaInfos;
        $this->infos['name'] = $this->name;
        $this->infos['url'] = $this->config['base_url'];
        $this->infos['description'] = html_entity_decode(
            $this->infos['description'],
            ENT_QUOTES,
            "UTF-8"
        );
        return $this->infos;
    }

    public function isUserExist(string $username): bool
    {
        $database = $this->dbConnexion;
        $table =  $this->name . "_triples";

        $sqlQuery = "SELECT 1 FROM ${table} WHERE name=:username LIMIT 1;";
        $sth = $database->prepare($sqlQuery);

        $sth->execute(array(':username' => $username));

        if ($sth->fetchColumn() > 0) {
            return true;
        }
        return false;
    }

    public function addAdminUser(string $username, string $mail, string $md5Password)
    {
        $this->addUser($username, $mail, $md5Password);
        $this->addUserToGroup($username, 'admins');
    }

    public function addUser(string $username, string $mail, string $md5Password)
    {
        $database = $this->dbConnexion;
        $table =  $this->name . "_users";

        $sqlQuery = "INSERT INTO ${table} (name, password, email, signuptime, motto)
            VALUES (:name, :password, :mail, now(), '');";

        $sth = $database->prepare($sqlQuery);

        $values = array(
            ':name' => $username,
            ':mail' => $mail,
            ':password' => $md5Password,
        );

        if ($sth->execute($values) === false) {
            throw new \Exception(
                "Impossible de créer l'utilisateur ${username}. Existe t'il déjà ?",
                1
            );
        }
    }

    public function addUserToGroup(string $username, string $groupname)
    {
        $groupMembers = $this->getGroupMembers($groupname);
        if (in_array($username, $groupMembers)) {
            return;
        }

        $database = $this->dbConnexion;
        $table =  $this->name . "_triples";
        $resource = "ThisWikiGroup:${groupname}";

        $sqlQuery = "UPDATE ${table} SET value=:groupMembers WHERE resource=:resource LIMIT 1;";
        $sth = $database->prepare($sqlQuery);

        $groupMembers[] = $username;

        $values = array(
            ':resource' => $resource,
            ':groupMembers' => implode(PHP_EOL, $groupMembers),
        );

        if ($sth->execute($values) === false) {
            throw new \Exception(
                "Erreur lors de l'ajout de ${username} au groupe ${groupname}.",
                1
            );
        }
    }

    /**
     * Récupère la liste des noms de tables dans la base de donnée pour ce Wiki.
     *
     * @param $db
     * @return mixed
     */
    private function getDBTablesList()
    {
        $database = $this->dbConnexion;
        // Echape le caractère '_' et '%'
        $search = array('%', '_');
        $replace = array('\%', '\_');
        $tablePrefix = str_replace(
            $search,
            $replace,
            $this->config['table_prefix']
        ) . '%';

        $query = "SHOW TABLES LIKE ?";
        $sth = $database->prepare($query);
        $sth->execute(array($tablePrefix));

        $results = $sth->fetchAll();

        $finalResults = array();
        foreach ($results as $value) {
            $finalResults[] = $value[0];
        }

        return $finalResults;
    }

    private function getGroupMembers(string $groupname): array
    {
        $database = $this->dbConnexion;
        $table =  $this->name . "_triples";
        $resource = "ThisWikiGroup:${groupname}";

        $sqlQuery = "SELECT `value` FROM ${table} WHERE resource=:resource LIMIT 1;";
        $sth = $database->prepare($sqlQuery);

        $values = array(
            ':resource' => $resource,
        );

        if ($sth->execute($values) === false) {
            throw new \Exception(
                "Ne peut récuperer la liste des membres du groupe ${groupname}. Le groupe existe t'il ?",
                1
            );
        }
        $result = explode(PHP_EOL, $sth->fetch(\PDO::FETCH_ASSOC)['value']);
        return $result;
    }

    private function setRelease(string $version, string $release)
    {
        $this->loadConfiguration();
        $this->config['yeswiki_version'] = $version;
        $this->config['yeswiki_release'] = $release;
        $this->config->write($this->path . "/wakka.config.php");
    }
}
