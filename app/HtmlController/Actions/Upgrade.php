<?php

namespace Ferme\HtmlController\Actions;

use Exception;

class Upgrade extends Action
{
    public function execute()
    {
        if (!isset($this->get['name'])) {
            $this->ferme->alerts->add("Paramètres manquant pour la suppression du wiki.");
        }

        try {
            $this->ferme->users->isAuthorized();
            $this->ferme->upgrade($this->get['name']);
        } catch (Exception $e) {
            $this->ferme->alerts->add($e->getMessage(), 'error');
            return;
        }

        $this->ferme->alerts->add(
            "Le wiki {$this->get['name']} a été mis à jour avec succès",
            'success'
        );
    }
}
