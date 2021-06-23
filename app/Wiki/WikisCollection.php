<?php

namespace Ferme\Wiki;

use Ferme\Collection;
use Ferme\Configuration;
use Ferme\Wiki\WikiFactory;
use RecursiveDirectoryIterator;
use PDO;

class WikisCollection extends Collection
{
    private $config;
    private $dbConnexion = null;

    public function __construct(Configuration $config, PDO $dbConnexion)
    {
        parent::__construct();
        $this->config = $config;
        $this->dbConnexion = $dbConnexion;
    }

    public function load()
    {
        $wikiFactory = new WikiFactory($this->config, $this->dbConnexion);
        $wikisList = new RecursiveDirectoryIterator(
            $this->config['ferme_path'],
            RecursiveDirectoryIterator::SKIP_DOTS
        );

        foreach ($wikisList as $wikiPath) {
            if (!is_dir($wikiPath)) {
                continue;
            }

            if (!is_file("{$wikiPath}/wakka.config.php")) {
                continue;
            }

            $wikiName = basename($wikiPath);
            
            try {
                $wiki = $wikiFactory->loadWikiFromExisting($wikiName);
            } catch (Exception $e) {
                printf("Error on {$wikiName}\n");
                continue;
            }

            $this->add(
                $wikiName,
                $wikiFactory->loadWikiFromExisting($wikiName)
            );
        }
    }
}
