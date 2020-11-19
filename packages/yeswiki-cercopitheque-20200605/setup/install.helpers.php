<?php

/**
 * Communique le resultat d'un test :
 * -- affiche OK si elle l'est
 * -- affiche un message d'erreur dans le cas contraire
 *
 * @param string $text Label du test
 * @param boolean $condition Résultat de la condition testée
 * @param string $errortext Message en cas d'erreur
 * @param string $stopOnError Si positionnée é 1 (par défaut), termine le
 *               script si la condition n'est pas vérifiée
 * @return int 0 si la condition est vraie et 1 si elle est fausse
 */
function test($text, $condition, $errorText = "", $stopOnError = 1)
{
    echo "$text ";
    if ($condition) {
        echo "<span class=\"ok\">"._t('OK')."</span><br />\n";
        return 0;
    } else {
        echo "<span class=\"failed\">"._t('FAIL')."</span>";
        if ($errorText) {
            echo ": ",$errorText;
        }
        echo "<br />\n";
        if ($stopOnError) {
            echo "<br />\n<div class=\"alert alert-danger alert-error\"><strong>"._t('END_OF_INSTALLATION_BECAUSE_OF_ERRORS').".</strong></div>\n";
            echo "<script>
                document.write('<div class=\"form-actions\"><a class=\"btn btn-large btn-primary revenir\" href=\"javascript:history.go(-1);\">"._t('GO_BACK')."</a></div>');
                </script>\n";
            echo "</body>\n</html>\n";
            exit;
        }
        return 1;
    }
}

function myLocation()
{
    list($url, ) = explode("?", $_SERVER["REQUEST_URI"]);
    return $url;
}
