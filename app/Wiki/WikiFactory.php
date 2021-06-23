<?php

namespace Ferme\Wiki;

use Ferme\Wiki\Wiki;
use Exception;
use PDO;
use Files\File;
use Ferme\Configuration;
use Ferme\Archive;
use Ferme\Database;

class WikiFactory
{
    private $fermeConfig;
    private $dbConnexion;

    public function __construct($fermeConfig, PDO $dbConnexion)
    {
        $this->fermeConfig = $fermeConfig;
        $this->dbConnexion = $dbConnexion;
    }

    public function loadWikiFromExisting(string $name): Wiki
    {
        $wikiPath = $this->getWikiPath($name);
        $wiki = new Wiki($name, $wikiPath, $this->fermeConfig, $this->dbConnexion);
        return $wiki;
    }

    public function createNewWiki(string $name, string $mail, string $description): Wiki
    {
        $wikiPath = $this->getWikiPath($name);

        // Vérifie si le wiki n'existe pas déjà
        if (is_dir($wikiPath) || is_file($wikiPath)) {
            throw new Exception("Ce nom de wiki est déjà utilisé (${name})");
        }
        $this->copyFolder("packages/{$this->fermeConfig['source']}/", $wikiPath);
        $this->setupWiki($name, $mail);
        $this->writeWakkaInfo($wikiPath, $mail, $description);
        return $this->loadWikiFromExisting($name);
    }

    public function createFromArchive(Archive $archive)
    {
        $wikiName = $archive->restore(
            $this->fermeConfig['ferme_path'],
            $this->fermeConfig['archives_path'],
            $this->dbConnexion
        );
        return $this->loadWikiFromExisting($wikiName);
    }

    public function copyWiki(Wiki $wikiToCopy, string $newWikiName): Wiki {
        
        $this->copyFolder($wikiToCopy->path, $this->getWikiPath($newWikiName));
        $this->copyWikiDB("{$wikiToCopy->name}_", "{$newWikiName}_");

        $copiedWiki = $this->loadWikiFromExisting($newWikiName);
        $copiedWiki->config["table_prefix"] = "{$newWikiName}_";
        $copiedWiki->config["base_url"] = $this->getWikiUrl($newWikiName);
        $copiedWiki->config["meta_description"] = "{$newWikiName}";
        $copiedWiki->config["wakka_name"] = "{$newWikiName}";

        $copiedWiki->config->write("{$copiedWiki->path}/wakka.config.php");


        return $copiedWiki;
    }

    private function getWikiPath($name): string
    {
        return $this->fermeConfig['ferme_path'] . $name . "/";
    }

    private function checkIfWikiInstallError(string $pageContent): bool
    {
        $errorString = "<span class=\"failed\">ECHEC</span>";
        if (strpos($pageContent, $errorString) !== false) {
            return true;
        }
        return false;
    }

    private function deleteWikiFiles(string $wikiPath)
    {
        $wikiFiles = new File($wikiPath);
        $wikiFiles->delete();
    }

    private function writeWakkaInfo($wikiPath, $mail, $description)
    {
        $file = "${wikiPath}wakka.infos.php";

        $wakkaInfo = new FermeConfiguration($file);

        $wakkaInfo['mail'] = $mail;
        $wakkaInfo['description'] = $description;
        $wakkaInfo['date'] = time();

        $wakkaInfo->write($file, "wakkaInfos");
    }

    private function setupWiki($wikiName, $mail)
    {
        $unusablePassword = password_hash("Bjarne et Stroustrup sont dans un bateau.", PASSWORD_DEFAULT);
        $postParameters = array(
            'config[default_language]' => 'fr',
            'config[wakka_name]' => $wikiName,
            'config[meta_description]' => '',
            'config[meta_keywords]' => '',
            'config[root_page]' => 'PagePrincipale',
            'config[mysql_host]' => $this->fermeConfig['db_host'],
            'config[mysql_database]' => $this->fermeConfig['db_name'],
            'config[mysql_user]' => $this->fermeConfig['db_user'],
            'config[mysql_password]' => $this->fermeConfig['db_password'],
            'config[table_prefix]' => "${wikiName}_",
            'admin_name' => 'WikiAdmin',
            'admin_password' => $unusablePassword,
            'admin_password_conf' => $unusablePassword,
            'admin_email' => $mail,
            'config[base_url]' => $this->getWikiUrl($wikiName),
            'config[rewrite_mode]' => '0',
            'config[allow_raw_html]' => '1',
        );

        $curlSession = curl_init("${wikiUrl}PagePrincipale&installAction=install");
        curl_setopt($curlSession, CURLOPT_POST, true);
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, http_build_query($postParameters));
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

        $curlOutput = curl_exec($curlSession);

        if (
            $curlOutput === false
            or $this->checkIfWikiInstallError($curlOutput) === true
        ) {
            $this->deleteWikiFiles($this->getWikiPath($wikiName));
            throw new Exception("Problème lors de la configuration du nouveau wiki."
                . curl_error($curlSession));
        }

        curl_close($curlSession);
    }

    private function getWikiUrl(string $wikiName):string
    {
        return "{$this->fermeConfig['base_url']}{$this->fermeConfig['ferme_path']}{$wikiName}/?";
    }

    private function copyFolder(string $srcPath, string $destPath)
    {
        $wikiSrcFiles = new File($srcPath);
        $wikiSrcFiles->copy($destPath);
    }

    private function copyWikiDB(string $srcPrefix, string $destPrefix)
    {
        $database = new Database($this->dbConnexion);
        $tablesList = $database->getTablesListByPrefix($srcPrefix);
        
        foreach ($tablesList as $tableName) {
            $newTableName = str_replace($srcPrefix, $destPrefix, $tableName);
            $database->copyTable($tableName, $newTableName);
        }

    }
}
