<?php

namespace Ferme\Mails;

use Ferme\Configuration;
use Ferme\Wiki\Wiki;

class MailCreateWiki extends Mail
{
    private string $wikiAdminPassword;
    protected Configuration $config;
    private Wiki $wiki;

    public function __construct(Configuration $config, Wiki $wiki, string $wikiAdminPassword)
    {
        $this->wiki = $wiki;
        $this->config = $config;
        $this->wikiAdminPassword = $wikiAdminPassword;
    }

    protected function getData(): array
    {
        return array(
            'name' => $this->wiki->name,
            'url' => $this->wiki->getInfos()['url'],
            'to' => $this->wiki->getInfos()['mail'],
            'from' => $this->config['mail_from'],
            'subject' => "Installation du wiki {$this->wiki->name}",
            'wikiAdminPassword' => $this->wikiAdminPassword,
            'listContacts' => $this->config['contacts']
        );
    }

    protected function getTemplate(): string
    {
        return "createWiki.twig";
    }
}
