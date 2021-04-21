<?php

namespace Ferme\HtmlController\Actions;

use Ferme\HtmlController\Actions\Action;
use Ferme\Wiki\WikiFactory;
use Ferme\Password;
use Ferme\Mails\MailCreateWiki;
use Exception;
use Ferme\Wiki\Wiki;

class AddWiki extends Action
{
    public function execute()
    {
        $wikiName = $this->cleanString($this->post['wikiName']);
        $wikiAdminPassword = Password::random(12);

        try {
            $this->checkEntries();
            $wiki = $this->addNewWiki($wikiName);
            $wiki->setUserPassword("WikiAdmin", md5($wikiAdminPassword));
            $wiki->addAdminUser(
                "FermeAdmin",
                $this->ferme->config['mail_from'],
                $this->ferme->config['admin_password'],
            );
        } catch (Exception $e) {
            $this->ferme->alerts->add($e->getMessage(), 'error');
            return;
        }

        $mail = new MailCreateWiki($this->ferme->config, $wiki, $wikiAdminPassword);
        $mail->send();

        $wikiUrl = $this->ferme->config['base_url'] . $wiki->path;
        $this->ferme->alerts->add(
            "<a href='{$wikiUrl}'>Visiter le nouveau wiki</a>. Vous recevrez un mail avec le mot de passe WikiAdmin.",
            'success'
        );
    }

    private function addNewWiki($wikiName): Wiki
    {
        $wikiFactory = new WikiFactory(
            $this->ferme->config,
            $this->ferme->dbConnexion
        );

        $wiki = $wikiFactory->createNewWiki(
            $wikiName,
            $this->cleanString($this->post['mail']),
            $this->cleanString($this->post['description'])
        );

        $this->ferme->wikis->add($wikiName, $wiki);

        return $wiki;
    }

    private function checkEntries()
    {
        if (!$this->isHashcashValid()) {
            throw new Exception(
                "La plantation de wiki est une activité délicate qui'
                . ' ne doit pas être effectuée par un robot. (Pensez à'
                . ' activer JavaScript)",
                1
            );
        }

        if (
            !isset($this->post['wikiName'])
            or !isset($this->post['mail'])
            or !isset($this->post['description'])
        ) {
            throw new Exception("Formulaire incomplet.", 'error');
        }

        if ($this->isValidWikiName($this->post['wikiName'])) {
            throw new Exception(
                "Ce nom n'est pas valide : longueur entre 1 et 20 caractères, uniquement"
                . " les lettres de 'A' à 'Z' (minuscules ou majuscules), chiffres de 0 à 9 "
                . "(pas d'espaces ou de tirets)", 
                1
            );
        }

        if (!filter_var($this->post['mail'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Cet email n'est pas valide.", 1);
        }
    }

    private function isHashcashValid(): bool
    {
        require_once 'app/secret/wp-hashcash.php';
        if (
            !isset($this->post["hashcash_value"])
            || hashcash_field_value() != $this->post["hashcash_value"]
        ) {
            return false;
        }
        return true;
    }

    private function isValidWikiName(string $name): bool
    {
        if (preg_match("~^[a-z0-9]{1,20}$~i", $name)) {
            return false;
        }
        return true;
    }

    private function cleanString(string $entry): string
    {
        return htmlentities($entry, ENT_QUOTES, "UTF-8");
    }
}
