<?php

namespace Ferme\Views;

use Ferme\Views\View;
use Ferme\CSV;

class CsvMailing extends View
{
    /**
     * Default name for CVS exported file
     * @var string
     */
    public const FILENAME = "mailing.csv";

    /**
     * Show the view
     * @return void
     */
    public function show()
    {
        $csv = new CSV();

        if ($this->ferme->wikis->count() <= 0) {
            $csv->printFile($this::FILENAME);
            return;
        }

        $csv->insert(array('Nom wiki', 'Email', 'Date création', 'URL'));

        foreach ($this->ferme->wikis as $wiki) {
            $infos = $wiki->getInfos();
            $csv->insert(
                array(
                    $infos['name'],
                    $infos['mail'],
                    date("Y-m-d", $infos['date']),
                    str_replace('wakka.php?wiki=', '', $infos['url']),
                )
            );
        }

        $csv->printFile($this::FILENAME);
    }
}
