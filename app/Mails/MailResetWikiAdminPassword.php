<?php

namespace Ferme\Mails;

class MailResetWikiAdminPassword extends Mail
{
    private string $wikiAdminPassword;

    public function __construct($config, $wiki, $wikiAdminPassword)
    {
        $this->wiki = $wiki;
        $this->config = $config;
        $this->wikiAdminPassword = $wikiAdminPassword;
    }

    protected function getData(): array
    {
        return array(
            'name' => $this->wiki->name,
            'url' => $this->wiki->infos['url'],
            'to' => $this->wiki->infos['mail'],
            'from' => $this->config['mail_from'],
            'subject' => "Mot de passe du wiki {$this->wiki->name}",
            'wikiAdminPassword' => $this->wikiAdminPassword,
            'listContacts' => $this->config['contacts']
        );
    }

    protected function getTemplate(): string
    {
        return "resetWikiAdminPassword.twig";
    }
}
