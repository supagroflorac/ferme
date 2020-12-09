<?php

namespace Ferme;

use Ferme\Wiki;
use Exception;
use Files\File;
use Ferme\Configuration;

class Wikifactory
{
    private $fermeConfig;
    private $dbConnexion;

    public function __construct($fermeConfig, $dbConnexion)
    {
        $this->fermeConfig = $fermeConfig;
        $this->dbConnexion = $dbConnexion;
    }

    /**
     * Charge un wiki déjà installé
     * @param  string $name nom du wiki a charger.
     * @return  Wiki        Le wiki chargé.
     */
    public function loadWikiFromExisting(string $name): Wiki
    {
        $wikiPath = $this->getWikiPath($name);
        $wiki = new Wiki($name, $wikiPath, $this->fermeConfig, $this->dbConnexion);
        $wiki->loadConfiguration();
        $wiki->loadInfos();
        return $wiki;
    }

    /**
     * Install un nouveau wiki
     * @param  string $name        Nom du wiki
     * @param  string $mail        Mail de la personne qui installe le wiki
     * @param  string $description Description du Wiki
     * @return Wiki                Le wiki fraîchement installé
     */
    public function createNewWiki(string $name, string $mail, string $description): Wiki
    {
        $wikiPath = $this->getWikiPath($name);

        // Vérifie si le wiki n'existe pas déjà
        if (is_dir($wikiPath) || is_file($wikiPath)) {
            throw new Exception("Ce nom de wiki est déjà utilisé (${name})");
        }
        $this->copyWikiFiles($wikiPath);
        $this->setupWiki($name, $mail);
        $this->writeWakkaInfo($wikiPath, $mail, $description);
        return $this->loadWikiFromExisting($name);
    }

    public function createFromArchive($archive)
    {
        $wikiName = $archive->restore(
            $this->fermeConfig['ferme_path'],
            $this->fermeConfig['archives_path'],
            $this->dbConnexion
        );
        return $this->loadWikiFromExisting($wikiName);
    }

    private function getWikiPath($name)
    {
        return $this->fermeConfig['ferme_path'] . $name . "/";
    }

    private function checkIfWikiInstallError($pageContent)
    {
        $errorString = "<span class=\"failed\">ECHEC</span>";
        if (strpos($pageContent, $errorString) !== false) {
            return true;
        }
        return false;
    }

    private function deleteWikiFiles($wikiPath)
    {
        $wikiFiles = new File($wikiPath);
        $wikiFiles->delete();
    }

    private function writeWakkaInfo($wikiPath, $mail, $description)
    {
        $file = "${wikiPath}wakka.infos.php";

        $wakkaInfo = new Configuration($file);

        $wakkaInfo['mail'] = $mail;
        $wakkaInfo['description'] = $description;
        $wakkaInfo['date'] = time();

        $wakkaInfo->write($file, "wakkaInfos");
    }

    private function setupWiki($wikiName, $mail)
    {
        $wikiUrl = $this->fermeConfig['base_url']
            . $this->fermeConfig['ferme_path']
            . "${wikiName}/?";
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
            'config[base_url]' => $wikiUrl,
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

    private function copyWikiFiles($wikiPath)
    {
        $packagePath = "packages/" . $this->fermeConfig['source'] . "/";
        $wikiSrcFiles = new File($packagePath);
        $wikiSrcFiles->copy($wikiPath);
    }
}
