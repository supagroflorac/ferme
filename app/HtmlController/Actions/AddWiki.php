<?php

namespace Ferme\HtmlController\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class AddWiki extends Action
{
    public function execute()
    {
        if (!$this->isHashcashValid()) {
            $this->ferme->alerts->add(
                'La plantation de wiki est une activité délicate qui'
                . ' ne doit pas être effectuée par un robot. (Pensez à'
                . ' activer JavaScript)',
                'error'
            );
            return;
        }

        if (
            !isset($this->post['wikiName'])
            or !isset($this->post['mail'])
            or !isset($this->post['description'])
        ) {
            $this->ferme->alerts->add("Formulaire incomplet.", 'error');
            return;
        }

        if ($this->isValidWikiName($this->post['wikiName'])) {
            $this->ferme->alerts->add("Ce nom n'est pas valide : "
                . "longueur entre 1 et 20 caractères, uniquement les lettres de 'A' à 'Z' "
                . "(minuscules ou majuscules), chiffres de 0 à 9 "
                . "(pas d'espaces ou de tirets)", 1);
            return;
        }

        if (!filter_var($this->post['mail'], FILTER_VALIDATE_EMAIL)) {
            $this->ferme->alerts->add("Cet email n'est pas valide.", 1);
            return;
        }

        try {
            $wikiFactory = new \Ferme\WikiFactory(
                $this->ferme->config,
                $this->ferme->dbConnexion
            );
            $wikiName = $this->cleanEntry($this->post['wikiName']);
            $wiki = $wikiFactory->createNewWiki(
                $wikiName,
                $this->cleanEntry($this->post['mail']),
                $this->cleanEntry($this->post['description'])
            );
            $this->ferme->wikis->add($wikiName, $wiki);
            $wikiAdminPassword = \Ferme\Password::random(12);
            $wiki->setPassword("WikiAdmin", md5($wikiAdminPassword));
            $wiki->addAdminUser(
                "FermeAdmin",
                $this->ferme->config['mail_from'],
                $this->ferme->config['admin_password'],
            );
        } catch (\Exception $e) {
            $this->ferme->alerts->add($e->getMessage(), 'error');
            return;
        }

        $wikiUrl = $this->ferme->config['base_url'] . $wiki->path;
        $this->ferme->alerts->add(
            "<a href='${wikiUrl}'>Visiter le nouveau wiki</a>. Vous recevrez un mail avec le mot de passe WikiAdmin.",
            'success'
        );

        $mail = new \Ferme\Mails\MailCreateWiki($this->ferme->config, $wiki, $wikiAdminPassword);
        $mail->send();
    }

    private function isHashcashValid()
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

    /**
     * Définis si le nom d'un wiki est valide
     * @param  strin   $name Nom potentiel du wiki.
     * @return boolean       Vrai si le nom est valide, faux sinon
     */
    private function isValidWikiName($name)
    {
        if (preg_match("~^[a-z0-9]{1,20}$~i", $name)) {
            return false;
        }
        return true;
    }

    /**
     * Nettoie une chaine de caractère
     * @param  string $entry Chaine a nettoyer
     * @return string        Chaine de caractères nettoyées
     */
    private function cleanEntry($entry)
    {
        return htmlentities($entry, ENT_QUOTES, "UTF-8");
    }
}
