<?php

namespace Ferme;

class MailCreateWiki extends Mail
{
    public function __construct($config, $wiki)
    {
        $this->wiki = $wiki;
        $this->config = $config;
    }

    protected function getData()
    {
        $data = array(
            'name' => $this->wiki->name,
            'url' => $this->wiki->getInfos()['url'],
            'to' => $this->wiki->getInfos()['mail'],
            'from' => $this->config['mail_from'],
            'subject' => 'Installation du wiki ' . $this->wiki->name,
            'listContacts' => $this->config['contacts']
        );

        return $data;
    }

    protected function getTemplate()
    {
        return "createWiki.twig";
    }
}
