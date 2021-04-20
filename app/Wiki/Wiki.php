<?php

namespace Ferme\Wiki;

use Ferme\InterfaceObject;
use Ferme\Configuration;
use Ferme\Database;
use Files\File;
use PDO;
use DateTime;
use Exception;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Wiki implements InterfaceObject
{
    public $path;
    public $name;
    private $fermeConfig;
    public $dbConnexion;
    public $infos = null;
    private $config = null;

    public function __construct(string $name, string $path, Configuration $fermeConfig, PDO $dbConnexion)
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

    public function getDiskUsage(): int
    {
        $file = new File($this->path . '/files');
        return $file->diskUsage();
    }

    public function getLasPageModificationDateTime(): DateTime
    {
        $tablePages = $this->config['table_prefix'] . 'pages';
        // Binary sert ici a faire une comparaison sensible a la casse.
        $query = $this->dbConnexion->query(
            "SHOW TABLE STATUS WHERE binary Name = '$tablePages';"
        );
        $result = $query->fetchAll();

        return new DateTime($result[0]['Update_time']);
    }

    public function getInfos(): array
    {
        if (is_null($this->infos)) {
            return $this->loadInfos();
        }
        return $this->infos;
    }

    public function delete()
    {
        $this->deleteDb();
        $this->deleteFiles();
    }

    private function deleteFiles()
    {
        $wikiFiles = new File(
            $this->fermeConfig['ferme_path'] . $this->config['wakka_name']
        );
        $wikiFiles->delete();
    }

    private function deleteDB()
    {
        $tables = $this->getDBTablesList();

        foreach ($tables as $tableName) {
            $sth = $this->dbConnexion->prepare("DROP TABLE IF EXISTS " . $tableName);
            if ($sth->execute() === false) {
                throw new Exception(
                    "Erreur lors de la suppression de la base de donnée",
                    1
                );
            }
        }
    }

    public function archive(): string
    {
        //TODO refactore
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
        $archive = new ZipArchive();

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $wikiPath,
                RecursiveDirectoryIterator::SKIP_DOTS // Evite les repertoire .. et .
            )
        );

        if ($archive->open($archiveFilename, ZipArchive::CREATE) !== true) {
            throw new Exception(
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

    public function upgrade(string $srcPath)
    {
        // TODO refactore
        // Delete wiki files
        $fileToKeep = array(
            '.', '..', 'wakka.config.php', 'wakka.infos.php', 'files', 'themes', 'custom'
        );
        $res = opendir($this->path);
        if ($res !== false) {
            while ($filename = readdir($res)) {
                if (!in_array($filename, $fileToKeep)) {
                    $file = new File($this->path . '/' . $filename);
                    $file->delete();
                }
            }
            closedir($res);
        }

        // Copies new files
        $res = opendir($srcPath);
        if ($res !== false) {
            while ($filename = readdir($res)) {
                if (!in_array($filename, $fileToKeep)) {
                    $file = new File($srcPath . $filename);
                    $file->copy($this->path . '/' . $filename);
                }
            }
            closedir($res);
        }

        // Updates the version and release number.
        include_once "${srcPath}/includes/constants.php";
        $this->setRelease(YESWIKI_VERSION, YESWIKI_RELEASE);
    }

    public function getVersion(): string
    {
        return $this->config['yeswiki_version'];
    }

    public function getRelease(): string
    {
        return $this->config['yeswiki_release'];
    }

    public function setUserPassword(string $username, string $md5Password)
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
            throw new Exception(
                "Impossible de changer le mot de passe de ${username}",
                1
            );
        }
    }

    public function loadInfos(): array
    {
        // TODO refactor
        unset($this->infos);

        $wakkaInfos = array(
            'mail' => 'nomail',
            'description' => 'Pas de description.',
            'date' => 0,
        );

        $filePath = $this->path . "wakka.infos.php";
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
        $table =  $this->name . "_users";

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
        $sth = $this->dbConnexion->prepare(
            "INSERT INTO {$this->name}_users (name, password, email, signuptime, motto)
                VALUES (:name, :password, :mail, now(), '');"
        );

        $values = array(
            ':name' => $username,
            ':mail' => $mail,
            ':password' => $md5Password,
        );

        if ($sth->execute($values) === false) {
            throw new Exception(
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
        $groupMembers[] = $username;

        $sth = $this->dbConnexion->prepare(
            "UPDATE {$this->name}_triples 
                SET value=:groupMembers 
                WHERE resource=:resource LIMIT 1;"
        );

        $values = array(
            ':resource' => "ThisWikiGroup:${groupname}",
            ':groupMembers' => implode(PHP_EOL, $groupMembers),
        );

        if ($sth->execute($values) === false) {
            throw new Exception(
                "Erreur lors de l'ajout de ${username} au groupe ${groupname}.",
                1
            );
        }
    }

    public function setUserEmail(string $username, string $email)
    {
        $sth = $this->dbConnexion->prepare(
            "UPDATE {$this->name}_users 
                SET `email`=:mail 
                WHERE `name`=:name 
                LIMIT 1;"
        );

        $values = array(
            ':mail' => $email,
            ':name' => $username,
        );

        if ($sth->execute($values) === false) {
            throw new Exception(
                "Impossible de changer l'adresse mail de ${username}.",
                1
            );
        }
    }

    public function isUtf8()
    {
        return isset($this->config['db_charset']) and $this->config['db_charset'] === "utf8mb4";
    }

    private function getDBTablesList(): array
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
            throw new Exception(
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
