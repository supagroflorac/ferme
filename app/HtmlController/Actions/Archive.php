<?php

namespace Ferme\HtmlController\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class Archive extends Action
{
    public function execute()
    {
        if (!isset($this->get['name'])) {
            $this->alerts->add(
                "Paramètres manquant pour créer l'archive."
            );
        }

        try {
            $this->ferme->users->isAuthorized();
            $this->ferme->archiveWiki($this->get['name']);
        } catch (\Exception $e) {
            $this->ferme->alerts->add($e->getMessage(), 'error');
            return;
        }

        $this->ferme->alerts->add(
            "Le wiki " . $this->get['name'] . " a été archivé avec succès.",
            'success'
        );
    }
}
