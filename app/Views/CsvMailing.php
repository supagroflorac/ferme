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
            $wikiInfos = $wiki->getInfos();
            $csv->insert(
                array(
                    $wikiInfos['name'],
                    $wikiInfos['mail'],
                    date("Y-m-d", $wikiInfos['date']),
                    str_replace('wakka.php?wiki=', '', $wikiInfos['url']),
                )
            );
        }

        $csv->printFile($this::FILENAME);
    }
}
