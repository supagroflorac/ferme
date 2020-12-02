<?php

namespace Ferme\HtmlController\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class Restore extends Action
{
    public function execute()
    {
        if (!isset($this->get['name'])) {
            $this->ferme->alerts->add(
                "Paramètres manquant pour la restauration de l'archive."
            );
        }

        try {
            $this->users->isAuthorized();
            $this->ferme->restore($this->get['name']);
        } catch (\Exception $e) {
            $this->ferme->alerts->add($e->getMessage(), 'error');
            return;
        }

        $this->ferme->alerts->add(
            "L'archive " . $this->get['name'] . " a été restaurée avec succès.",
            'success'
        );
    }
}
