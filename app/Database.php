<?php

namespace Ferme;

use PDO;

class Database
{
    private PDO $dbConnexion;

    public function __construct(PDO $dbConnexion)
    {
        $this->dbConnexion = $dbConnexion;
    }

    public function export(string $file, string $prefix = "")
    {
        $tableList = $this->getTableList($prefix);

        $output = "";
        foreach ($tableList as $table) {
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
            $output .= $this->getCreateTable($table) . ";\n\n";
        }

        foreach ($tableList as $table) {
            $output .= $this->getTableContent($table);
        }

        file_put_contents($file, $output);
    }

    public function import(string $sqlFile)
    {
        $content = file_get_contents($sqlFile);
        $sth = $this->dbConnexion->prepare($content);
        $sth->execute();
    }

    private function getCreateTable(string $tableName): string
    {
        $query = "SHOW CREATE TABLE {$tableName};";
        $sth = $this->dbConnexion->prepare($query);
        $sth->execute();
        return $sth->fetchAll()[0]['Create Table'];
    }

    private function getTableContent(string $tableName): string
    {
        $query = "SELECT * FROM {$tableName};";
        $sth = $this->dbConnexion->prepare($query);
        $sth->execute();
        $queryResult = $sth->fetchAll(PDO::FETCH_ASSOC);

        if (empty($queryResult)) {
            return "";
        }

        $output = "LOCK TABLES `{$tableName}` WRITE;\n"
            . "INSERT INTO `{$tableName}` VALUES ";
        // TODO Une commande par page plutot que tout dans la même : permettrai d'extraire plus facilement certaines données.
        $lineOutput = "";
        foreach ($queryResult as $line) {
            $columnOutput = "";
            foreach ($line as $columnValue) {
                if ($columnValue === null) {
                    $columnOutput .= "'',";
                    continue;
                }
                $columnOutput .= "'{$this->prepareData($columnValue)}',";
            }
            $lineOutput .= "({$this->removeLastComma($columnOutput)}),";
        }
        $output .= "{$this->removeLastComma($lineOutput)};\n"
            . "UNLOCK TABLES;\n\n";

        return $output;
    }


    private function getTableList(string $prefix): array
    {
        // Echappe les caractère % et _
        $prefix = str_replace(array('%', '_'), array('\%', '\_'), $prefix);

        $tableList = array();
        $query = "SHOW TABLES LIKE ?";
        $sth = $this->dbConnexion->prepare($query);
        $sth->execute(array($prefix . '%'));

        $result = $sth->fetchAll();

        foreach ($result as $value) {
            $tableList[$value[0]] = $value[0];
        }

        return $tableList;
    }

    private function prepareData(string $data): string
    {
        $output = addslashes($data);
        $output = str_replace("\n", "\\n", $output);
        return $output;
    }

    private function removeLastComma(string $string): string
    {
        if (substr($string, -1) === ',') {
            return substr($string, 0, -1);
        }
        return $string;
    }
}
