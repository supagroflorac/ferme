<?php

namespace Ferme;

class CSV
{

    private $csv = null;

    public function insert(array $row)
    {
        $string = "";
        foreach ($row as $cell) {
            // corrige le bug de l'export avec des intitulés contenant des guillemet.
            $cell = str_replace('"', '""', $cell);
            $string .= "\"{$cell}\",";
        }
        $this->csv .= $string . "\n";
    }

    public function array2CSV(array $array)
    {
        foreach ($array as $row) {
            $this->insert($row);
        }
    }

    public function printFile(string $filename)
    {
        header("Content-type: text/CSV");
        header("Content-disposition: attachment; filename={$filename}");
        print $this->csv;
    }
}
