<?php

namespace Ferme\Views;

use Ferme\Views\View;
use Ferme\CSV;

class CsvMailing extends View
{
    public const FILENAME = "mailing.csv";

    public function show()
    {
        $csv = new CSV();

        if ($this->ferme->wikis->count() <= 0) {
            $csv->printFile($this::FILENAME);
            return;
        }

        $csv->insert(array('Nom wiki', 'Email', 'Date création', 'URL'));

        foreach ($this->ferme->wikis as $wiki) {
            $csv->insert(
                array(
                    $wiki->name,
                    $wiki->infos['mail'],
                    date("Y-m-d", $wiki->infos['date']),
                    $wiki->config['base_url'],
                )
            );
        }

        $csv->printFile($this::FILENAME);
    }
}
