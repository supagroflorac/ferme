<?php

namespace Ferme\Mails;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Ferme\Configuration;

abstract class Mail
{
    abstract protected function getTemplate(): string;
    abstract protected function getData(): array;

    protected Configuration $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    public function send()
    {
        $themePath = "themes/{$this->config['template']}/mails/";
        $loader = new FilesystemLoader($themePath);
        $twig = new Environment($loader);
        $data = $this->getData();
        $content = $twig->render($this->getTemplate(), $data);

        $headers = "From: {$data['from']}\r\n"
            . "Reply-To: {$data['from']}\r\n";

        mail(
            $data['to'],
            $data['subject'],
            $content,
            $headers
        );
    }
}
