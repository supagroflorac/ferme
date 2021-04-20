<?php

namespace Ferme\HtmlController;

use Ferme\Views\AjaxListWikis;
use Ferme\Download;
use Ferme\Views\Authentification;
use Ferme\Views\Administration;
use Ferme\Views\CsvMailing;
use Ferme\Views\Home;
use Ferme\Ferme;

class Controller
{
    private Ferme $ferme;

    public function __construct(Ferme $ferme)
    {
        $this->ferme = $ferme;
    }

    public function run(array $get, array $post)
    {
        $this->ferme->wikis->load();
        $this->ferme->archives->load();

        if (isset($get['download'])) {
            $this->download($get['download']);
            return;
        }

        if (isset($get['action'])) {
            $this->action($get, $post);
        }

        $view = 'default';
        if (isset($get['view'])) {
            $view = $get['view'];
        }

        if ($view === 'ajax') {
            $this->ajax($get);
            return;
        }

        $this->showHtml($view);
    }

    private function ajax(array $get)
    {
        $view = new AjaxListWikis($this->ferme);
        if (
            isset($get['query'])
            and ($get['query'] === 'search')
        ) {
            switch ($get['query']) {
                case 'search':
                    $string = '*';
                    if (isset($get['string'])) {
                        $string = $get['string'];
                        if ('' === $string) {
                            $view->setFilter('*');
                        }
                    }
                    $view->setFilter($string);
                    $view->show();
                    break;
                default:
                    # code...
                    break;
            }
        }
    }

    private function download(string $download)
    {
        $download = new Download($download, $this->ferme);
        $download->serve();
    }

    private function showHtml(string $view)
    {
        switch ($view) {
            case 'admin':
                if (!$this->ferme->users->isLogged()) {
                    $view = new Authentification($this->ferme);
                    $view->show();
                    break;
                }
                $view = new Administration($this->ferme);
                $view->show();
                break;
            case 'exportMailing':
                if ($this->ferme->users->isLogged()) {
                    $view = new CsvMailing($this->ferme);
                    $view->show();
                }
                break;
            default:
                $view = new Home($this->ferme);
                $view->show();
                break;
        }
    }

    private function action(array $get, array $post)
    {
        $className = "Ferme\\HtmlController\Actions\\" . ucfirst($get['action']);
        if (!class_exists($className)) {
            return;
        }

        $action = new $className($this->ferme, $get, $post);
        $action->execute();
    }
}
