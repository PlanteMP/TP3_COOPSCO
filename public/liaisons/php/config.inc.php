<?php

// Verifier si l'exécution se fait sur le serveur de développement (local) ou celui de la production:
if (stristr($_SERVER['HTTP_HOST'], 'local') || (substr($_SERVER['HTTP_HOST'], 0, 7) == '192.168')) {
    $blnLocal = TRUE;
} else {
    $blnLocal = FALSE;
}

//var_dump($_SERVER['HTTP_HOST']);

// Selon l'environnement d'exécution (développement ou en ligne)
if ($blnLocal) {
    //Local
    $strHost = 'localhost';
    $strBD='24_rpni1_tofu';
    $strUser = 'rpni1_user';
    $strPassword= 'rpni1_mdp';
    error_reporting(E_ALL);
} else {
    //en ligne
    $strHost = 'localhost';
    $strBD='24_rpni1_tofu';
    $strUser = '24_rpni1_user';
    $strPassword = '24_rpni1_mdp';
    error_reporting(E_ALL & ~E_NOTICE);
    //error_reporting(0);
}

//Data Source Name pour l'objet PDO
$strDsn = 'mysql:dbname='.$strBD.';host='.$strHost;
//Tentative de connexion
$objPdo = new PDO($strDsn, $strUser, $strPassword);
//Changement d'encodage de l'ensemble des caractères pour UTF-8
$objPdo->exec("SET CHARACTER SET utf8");
//Pour obtenir des rapports d'erreurs et d'exception avec errorInfo() du pilote PDO
$objPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//$objPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

?>