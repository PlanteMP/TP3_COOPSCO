<?php
  
     $niveau = "../";
     include($niveau . "liaisons/php/config.inc.php");
     
 

$id_liste = $_GET['id_liste'] ?? null;
$id_item = $_GET['id_item'] ?? null;
$strCodeOperation = $_GET['code_operation'] ?? null;
 

if (!$strCodeOperation || !$id_liste) {
    echo("Erreur : Opération ou ID de liste manquant.");
}
 
// Initialisation des variables 
$arrInfosItem = [
    'nom' => '',
    'jour' => 0,
    'mois' => 0,
    'annee' => 0,
    'est_complete' => 0,
];
 
if ($strCodeOperation === 'modifier' && $id_item) {
    // Récupération des informations de l'item à modifier

    $strRequete = "SELECT nom, DAY(echeance) AS jour, MONTH(echeance) AS mois,
                          YEAR(echeance) AS annee, est_complete
                   FROM items WHERE id = :id_item";
    $objResultat = $objPdo->prepare($strRequete);
    $objResultat->bindParam(':id_item', $id_item, PDO::PARAM_INT);
    $objResultat->execute();
 
    $ligne = $objResultat->fetch(PDO::FETCH_ASSOC);
 
    if (!$ligne) {
        echo("Erreur : Aucun item trouvé pour cet ID.");
    }
 
    $arrInfosItem = [
        'nom' => $ligne['nom'],
        'jour' => $ligne['jour'] ?? 0,
        'mois' => $ligne['mois'] ?? 0,
        'annee' => $ligne['annee'] ?? 0,
        'est_complete' => $ligne['est_complete'] ?? 0,
    ];
} elseif ($strCodeOperation === 'creer') {
    // Configuration pour la création d'un nouvel item
    $arrInfosItem = [
        'nom' => '',
        'jour' => 0,
        'mois' => 0,
        'annee' => 0,
        'est_complete' => 0,
    ];
} else {
    echo("Erreur : Opération invalide ou ID d'item manquant.");
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['description'] ?? '';
    $jour = $_POST['jour'] ?? 0;
    $mois = $_POST['mois'] ?? 0;
    $annee = $_POST['annee'] ?? 0;
 
    $echeance = ($jour != 0 && $mois != 0 && $annee != 0 && checkdate((int)$mois, (int)$jour, (int)$annee))
        ? "$annee-$mois-$jour"
        : null;
 
    if ($strCodeOperation === 'modifier' && $id_item) {
        // Mise à jour de l'item existant
        $strRequete = "UPDATE items
                       SET nom = :nom, echeance = :echeance, est_complete = :est_complete
                       WHERE id = :id_item";
        $objResultat = $objPdo->prepare($strRequete);
        $objResultat->bindParam(':nom', $nom);
        $objResultat->bindParam(':echeance', $echeance);
        $objResultat->bindParam(':est_complete', $arrInfosItem['est_complete']);
        $objResultat->bindParam(':id_item', $id_item, PDO::PARAM_INT);
        $objResultat->execute();
 
        // Redirection vers afficher.php après la modification
        header("Location: afficher.php?id_liste=$id_liste");
        exit;
        
    } elseif ($strCodeOperation === 'creer') {
        // Création d'un nouvel item
        $strRequete = "INSERT INTO items (nom, echeance, est_complete, liste_id)
                       VALUES (:nom, :echeance, :est_complete, :id_liste)";
        $objResultat = $objPdo->prepare($strRequete);
        $objResultat->bindParam(':nom', $nom);
        $objResultat->bindParam(':echeance', $echeance);
        $objResultat->bindParam(':est_complete', $arrInfosItem['est_complete']);
        $objResultat->bindParam(':id_liste', $id_liste, PDO::PARAM_INT);
        $objResultat->execute();
 
        // Redirection vers afficher.php après la création
        header("Location: afficher.php?id_liste=$id_liste");
    }
}
?>
<?php
$requeteSQL = "
    SELECT listes.nom, listes.id, couleurs.hexadecimal
    FROM listes
    INNER JOIN couleurs ON listes.couleur_id = couleurs.id
";
$objStat = $objPdo->prepare($requeteSQL);
$objStat->execute();
$arrListes = $objStat->fetchAll();
 
$strRequeteItemsUrgentEcheance = 'SELECT items.id as id_item,
                            items.nom as nom_item,
                            items.echeance,
                            items.est_complete,
                            items.liste_id,
                            listes.nom as nom_liste,
                            couleurs.hexadecimal
                            FROM items
                            INNER JOIN listes
                            ON items.liste_id = listes.id
                            INNER JOIN couleurs
                            ON listes.couleur_id = couleurs.id
                            WHERE items.echeance >= CURDATE()';
$pdoResultatItemsUrgentEcheance = $objPdo->prepare($strRequeteItemsUrgentEcheance);
$pdoResultatItemsUrgentEcheance->execute();
$arrItemsUrgentEcheance = array();
while ($ligne = $pdoResultatItemsUrgentEcheance->fetch()) {
    $arrItemsUrgentEcheance[] = array(
        'id_item' => $ligne['id_item'],
        'nom_item' => $ligne['nom_item'],
        'echeance' => $ligne['echeance'],
        'est_complete' => $ligne['est_complete'],
        'liste_id' => $ligne['liste_id'],
        'nom_liste' => $ligne['nom_liste'],
        'hexadecimal' => $ligne['hexadecimal']
    );
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page permettant de modifier ou créer un item d'une liste.">
    <meta name="keywords" content="liste, tâches, modification, création">
    <meta name="author" content="Laurie Roy, Marie-Pierre Plante">
    <?php include $niveau . "liaisons/fragments/headlinks.inc.php"; ?>
    <title><?php echo ($strCodeOperation === 'creer') ? 'Créer un item' : 'Modifier un item'; ?></title>
</head>
<body>
<?php include ($niveau . "liaisons/fragments/entete.inc.php");?>
<main class="navigation">
<section class="meslistes">
            <header class="meslistes__header">
                <h2 class="meslistes__header__titre">Mes listes</h2>
</header>
            <ul class="meslistes__container">
                <?php
                
                $formatter = new IntlDateFormatter('fr_CA', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Toronto', IntlDateFormatter::GREGORIAN, 'd MMMM yyyy');
                for ($i = 0; $i < min(3, count($arrItemsUrgentEcheance)); $i++) {
                    $item = $arrItemsUrgentEcheance[$i];
                    
                    $date = new DateTime($item['echeance']);
                    $formattedDate = $formatter->format($date); 
                    ?>
                    <li class="meslistes__item" style="border-left: 8px solid # list-style:none;<?php echo $item['hexadecimal']; ?>;">
                   
                    <span style="background-color: #<?php  echo $item['hexadecimal']; ?>;" class="meslistes__item__couleur"></span>
                    <a href="<?php echo  $niveau; ?>items/afficher.php?id_liste=<?php  echo $item['liste_id'] ?>  ">
                    <span class="meslistes__item__nom"><p><?php echo $item['nom_item']; ?></p></span>
                      </a>
                      
                    </li>
                <?php } ?>
            </ul>
                </section>
    <section class="section__modifier">
        <header class="section__modifier__header">
            <h1><?php echo ($strCodeOperation === 'creer') ? 'Ajouter un item' : 'Modifier l\'item : ' . htmlspecialchars($arrInfosItem['nom']); ?></h1>
        </header>
        <form class="section__modifier__article" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . "?code_operation=$strCodeOperation&id_liste=$id_liste&id_item=$id_item"); ?>" method="post">
            <input type="hidden" name="id_liste" value="<?php echo htmlspecialchars($id_liste); ?>">
            <?php if ($strCodeOperation === 'modifier') { ?>
                <input type="hidden" name="id_item" value="<?php echo htmlspecialchars($id_item); ?>">
            <?php } ?>
            <div class="section__modifier__article__tache">
                <label for="description">Nom de l'item :</label>
                <input type="text" name="description" id="description" value="<?php echo htmlspecialchars($arrInfosItem['nom']); ?>" required>
            </div>
            <label for="echeance" class="date__titre">Date d'échéance :</label>
            <div class="section__modifier__article__date">
                
                <label for="jour"></label>
                <select name="jour" id="jour">
                    <option value="0">Jour</option>
                    <?php for ($i = 1; $i <= 31; $i++) { ?>
                        <option value="<?php echo $i; ?>" <?php echo ($i == $arrInfosItem['jour']) ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php } ?>
                </select>
                <label for="mois"></label>
                <select name="mois" id="mois">
                    <option value="0">Mois</option>
                    <?php for ($i = 1; $i <= 12; $i++) { ?>
                        <option value="<?php echo $i; ?>" <?php echo ($i == $arrInfosItem['mois']) ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php } ?>
                </select>
                <label for="annee"></label>
                <select name="annee" id="annee">
                    <option value="0">Année</option>
                    <?php $anneeActuelle = date("Y");
                    for ($i = $anneeActuelle; $i <= $anneeActuelle + 10; $i++) { ?>
                        <option value="<?php echo $i; ?>" <?php echo ($i == $arrInfosItem['annee']) ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <footer class="section__modifier__article__footer">
                <button type="submit" name="<?php echo ($strCodeOperation === 'creer') ? 'btn_creer' : 'btn_modifier'; ?>" class="btn__modifier">
                    <?php echo ($strCodeOperation === 'creer') ? 'Créer' : 'Enregistrer les modifications'; ?>
                </button>
 
            </footer>
        </form>
 
    </section>
    </main>
    <?php include ($niveau . "liaisons/fragments/piedDePage.inc.php");?>
</body>
</html>