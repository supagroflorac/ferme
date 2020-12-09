<?php

namespace Ferme\Mails;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

abstract class Mail
{
    abstract protected function getTemplate();
    abstract protected function getData();

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function send()
    {
        $themePath = 'themes/' . $this->config['template'] . '/mails/';
        $loader = new FilesystemLoader($themePath);
        $twig = new Environment($loader);
        $data = $this->getData();
        $content = $twig->render($this->getTemplate(), $data);

        $headers = 'From: ' . $data['from'] . "\r\n"
            . 'Reply-To: ' . $data['from'] . "\r\n";

        mail(
            $data['to'],
            $data['subject'],
            $content,
            $headers
        );
    }
}
