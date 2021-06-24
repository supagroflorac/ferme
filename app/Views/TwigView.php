<?php

namespace Ferme\Views;

use Ferme\Ferme;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

abstract class TwigView extends View
{

    protected Environment $twig;
    abstract protected function compileInfos(): array;

    public function __construct(Ferme $ferme)
    {
        parent::__construct($ferme);
        $loader = new FilesystemLoader($this->getThemePath());
        $this->twig = new Environment($loader);
    }

    public function show()
    {
        $listInfos = $this->compileInfos();
        $listInfos = $this->addThemesInfos($listInfos);
        $listInfos = $this->addUserInfos($listInfos);
        echo $this->twig->render($this->getTemplateFilename(), $listInfos);
    }

    private function getTemplateFilename(): string
    {
        $explodedClassName = explode('\\', get_class($this));
        $className = end($explodedClassName);
        return "$className.twig";
    }

    private function addThemesInfos(array $infos): array
    {
        return array_merge(
            $infos,
            array(
                'list_css' => $this->getCSS(),
                'list_alerts' => $this->ferme->alerts->getAll(),
                'list_js' => $this->getJS(),
            )
        );
    }

    private function getCSS(): array
    {
        $cssPath =  "{$this->getThemePath()}/css/";
        $listCss = array();
        foreach ($this->getFiles($cssPath) as $file) {
            $listCss[] = $file;
        }
        return $listCss;
    }

    private function getJS(): array
    {
        $jsPath = "{$this->getThemePath()}/js/";
        $listJs = array();
        foreach ($this->getFiles($jsPath) as $file) {
            $listJs[] = $file;
        }
        return $listJs;
    }

    private function getFiles(string $path): array
    {
        $fileArray = array();
        $handle = opendir($path);
        if ($handle !== false) {
            while ($entry = readdir($handle)) {
                $entryPath = $path . $entry;
                if (
                    "." != $entry
                    and ".." != $entry
                    and is_file($entryPath)
                ) {
                    $fileArray[] = $entryPath;
                }
            }
            closedir($handle);
        }
        return $fileArray;
    }

    private function addUserInfos(array $infos): array
    {
        return array_merge(
            $infos,
            array(
                'username' => $this->ferme->users->whoIsLogged(),
                'logged' => $this->ferme->users->isLogged(),
            )
        );
    }

    private function getThemePath(): string
    {
        return 'themes/' . $this->ferme->config['template'];
    }
}
