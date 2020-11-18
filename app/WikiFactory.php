<?php
namespace Ferme;

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
    public function createWikiFromExisting($name)
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
     * @param  string $mail        Mail de la personne qui install le wiki
     * @param  string $description Description du Wiki
     * @return Wiki                Le wiki fraichement installé
     */
    public function createNewWiki($name, $mail, $description)
    {
        $this->installNewWiki($name, $mail, $description);
        return $this->createWikiFromExisting($name);
    }

    public function createFromArchive($archive)
    {
        $wikiName = $archive->restore(
            $this->fermeConfig['ferme_path'],
            $this->fermeConfig['archives_path'],
            $this->dbConnexion
        );
        return $this->createWikiFromExisting($wikiName);
    }

    private function getWikiPath($name)
    {
        return $this->fermeConfig['ferme_path'] . $name . "/";
    }

    private function installNewWiki($wikiName, $mail, $description)
    {
        $wikiPath = $this->getWikiPath($wikiName);
        $packagePath = "packages/"
            . $this->fermeConfig['source']
            . "/";

        // Vérifie si le wiki n'existe pas déjà
        if (is_dir($wikiPath) || is_file($wikiPath)) {
            throw new \Exception("Ce nom de wiki est déjà utilisé (${wikiName})");
        }

        $wikiSrcFiles = new \Files\File($packagePath . "files");
        $wikiSrcFiles->copy($wikiPath);

        $curlSession = curl_init(
            $this->fermeConfig['base_url'] 
            . '/'
            . $wikiPath
            . '/?PagePrincipale&installAction=install'
        );
        
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
            'config[table_prefix]' => $wikiName . '_',
            'admin_name' => 'WikiAdmin',
            'admin_password' => $this->fermeConfig['admin_password'],
            'admin_password_conf' => $this->fermeConfig['admin_password'],
            'admin_email' => $mail,
            'config[base_url]' => $this->fermeConfig['base_url'] . $wikiPath . '?',
            'config[rewrite_mode]' => '0',
            'config[allow_raw_html]' => '1',
        );
        
        curl_setopt($curlSession, CURLOPT_POST, true);
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, http_build_query($postParameters));
        //curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
        
        if (!curl_exec($curlSession)) {
            throw new \Exception("bah ça merde..." . var_dump(curl_error($curlSession)));
        }

        curl_close($curlSession);
    }
}
