<?php

namespace Ferme\HtmlController\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class Delete extends Action
{
    public function execute()
    {
        if (!isset($this->get['name'])) {
            $this->ferme->alerts->add(
                "Paramètres manquant pour la suppression du wiki."
            );
        }

        try {
            $this->ferme->users->isAuthorized();
            $this->ferme->archiveWiki($this->get['name']);
        } catch (\Exception $e) {
            $this->ferme->alerts->add($e->getMessage(), 'error');
            return;
        }

        try {
            $this->ferme->users->isAuthorized();
            $this->ferme->delete($this->get['name']);
        } catch (\Exception $e) {
            $this->ferme->alerts->add($e->getMessage(), 'error');
            return;
        }

        $this->ferme->alerts->add(
            "Le wiki " . $this->get['name'] . " a été archivé puis supprimé avec succès",
            'success'
        );
    }
}
