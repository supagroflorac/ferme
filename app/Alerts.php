<?php

namespace Ferme;

class Alerts
{

    private array $list;

    public function __construct()
    {
        $this->list = array();
    }

    // type = notice | string | error
    public function add(string $text, string $type = 'notice')
    {
        $this->list[] = array(
            'text' => $text,
            'type' => $type,
        );
    }

    public function getAll(): array
    {
        $listAlerts = array();

        foreach ($this->list as $key => $alert) {
            $listAlerts[] = array(
                'id' => "alert" . $key,
                'text' => $alert['text'],
                'type' => $alert['type'],
            );
        }

        return $listAlerts;
    }
}
