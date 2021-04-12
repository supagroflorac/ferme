<?php

namespace Ferme;

use Ferme\Wiki;
use Exception;

class Page
{
    public string $name;
    public string $content = "";

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function loadFromFile(string $file)
    {
        $this->content = file_get_contents($file);
    }

    public function loadFromWiki(Wiki $wiki)
    {
        $sth = $wiki->dbConnexion->prepare(
            "SELECT body FROM {$wiki->name}_pages WHERE latest='Y' LIMIT 1"
        );

        if ($sth->execute() === false) {
            $pageName = $this->name;
            throw new Exception(
                "Impossible de récupérer le contenu de  ${pageName}",
                1
            );
        }

        $this->content = $sth->fetch(PDO::FETCH_ASSOC)['body'];
    }

    public function saveToWiki(Wiki $wiki)
    {
        if ($this->isExistInWiki($wiki) === true) {
            $this->setAllRevisionsToOldInWiki($wiki);
        }
        $this->addNewRevisionToWiki($wiki);
    }

    public function isExistInWiki(Wiki $wiki)
    {
        $sth = $wiki->dbConnexion->prepare(
            "SELECT COUNT(tag) 
                FROM {$wiki->name}_pages 
                WHERE tag='{$this->name}';"
        );

        $sth->execute();

        $nbPages = (int) $sth->fetchColumn();
        if ($nbPages > 0) {
            return true;
        }
        return false;
    }

    private function setAllRevisionsToOldInWiki(Wiki $wiki)
    {
        $sth = $wiki->dbConnexion->prepare(
            "UPDATE {$wiki->name}_pages 
                SET latest='N' 
                WHERE tag=:pageName;"
        );

        $values = array(':pageName' => $this->name);

        if ($sth->execute($values) === false) {
            throw new Exception(
                "Erreur lors du changement de statut de {$this->name} à 'old' dans {$wiki->name}",
                1
            );
        }
    }

    private function addNewRevisionToWiki(Wiki $wiki)
    {
        $sth = $wiki->dbConnexion->prepare(
            "INSERT INTO {$wiki->name}_pages 
                SET tag=:pageName, 
                    time = now(), 
                    owner='FermeAdmin',
                    user='FermeAdmin',
                    latest='Y',
                    body=:pageContent,
                    body_r='';"
        );

        $values = array(
            ':pageName' => $this->name,
            ':pageContent' => $this->content,
        );

        if ($sth->execute($values) === false) {
            throw new Exception(
                "Erreur lors de l'ajout de la nouvelle version de {$this->name} dans {$wiki->name}",
                1
            );
        }
    }
}