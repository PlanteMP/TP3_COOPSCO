<?php

    // ***************** Fichier de gestion des items d'une liste ******************//
    /**
     * @author Laurie Roy
     * @brief Script de gestion des données liées aux items d'une liste.
     * @details Ce script gère l'affichage des items d'une liste spécifique.
     * @version 2.0
     * @date 28 novembre 2024
     */


$niveau = "../";

//***************** Inclusion de la connexion à la base de données ******************//
include($niveau . 'liaisons/php/config.inc.php');

//***************** Variables importantes ******************//
$id_liste = $_GET['id_liste'] ?? null;
$id_item = $_GET['id_item'] ?? null;
$strCodeOperation = $_GET['code_operation'] ?? '';

//***************** Suppression d'un item ******************//
if ($strCodeOperation === 'supprimer' && $id_item && $id_liste) {
    $strRequete = "DELETE FROM items WHERE id = :id_item";
    $objResultat = $objPdo->prepare($strRequete);
    $objResultat->bindParam(':id_item', $id_item, PDO::PARAM_INT);
    $objResultat->execute();

    // Redirection vers afficher.php après suppression
    header("Location: afficher.php?id_liste=$id_liste");
    exit;
}

//***************** Modification de l'état d'un item ******************//
if (isset($_GET['btn_etat']) && $id_item && $id_liste) {
    $nouvelEtat = $_GET['btn_etat'] === '0' ? 1 : 0;
    $strRequete = "UPDATE items SET est_complete = :id_etat WHERE id = :id_item";
    $objResultat = $objPdo->prepare($strRequete);
    $objResultat->bindParam(':id_etat', $nouvelEtat, PDO::PARAM_INT);
    $objResultat->bindParam(':id_item', $id_item, PDO::PARAM_INT);
    $objResultat->execute();

    // Redirection vers afficher.php après modification
    header("Location: afficher.php?id_liste=$id_liste");
    exit;
}

//***************** Récupération de tous les items d'une liste ******************//
$strRequete = "SELECT id, nom, echeance, est_complete FROM items WHERE liste_id = :id_liste ORDER BY nom";
$objResultat = $objPdo->prepare($strRequete);
$objResultat->bindParam(':id_liste', $id_liste, PDO::PARAM_INT);
$objResultat->execute();

$arrItems = [];
while ($ligne = $objResultat->fetch(PDO::FETCH_ASSOC)) {
    $arrItems[] = [
        'id_item' => $ligne['id'],
        'item' => $ligne['nom'],
        'echeance' => $ligne['echeance'],
        'est_complete' => $ligne['est_complete'],
        'strValeurEtat' => $ligne['est_complete'] ? 'Complété' : 'Initial',
    ];
}

$objResultat->closeCursor();

//***************** Récupérations des informations de la liste ******************//
$strRequete = "SELECT listes.nom, couleurs.hexadecimal FROM listes 
                INNER JOIN couleurs ON listes.couleur_id = couleurs.id WHERE listes.id = :id_liste";
$objResultat = $objPdo->prepare($strRequete);
$objResultat->bindParam(':id_liste', $id_liste, PDO::PARAM_INT);
$objResultat->execute();

$arrInfosListe = $objResultat->fetch(PDO::FETCH_ASSOC);
if (!$arrInfosListe) {
    die("Erreur : Liste introuvable.");
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
<?php
    //***************** Récupérations des informations de la liste ******************//
    $strRequete = "SELECT listes.nom, couleurs.hexadecimal FROM listes 
                    INNER JOIN couleurs ON listes.couleur_id = couleurs.id WHERE listes.id = :id_liste";

    $objResultat = $objPdo->prepare($strRequete);
    $objResultat->bindParam(':id_liste', $id_liste);
    $objResultat->execute();

    $arrInfosListe = array();
    $ligne = $objResultat->fetch();
    $arrInfosListe['nom'] = $ligne['nom'];
    $arrInfosListe['couleur'] = $ligne['hexadecimal'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page permettant de visualiser les tâches d'une liste.">
    <meta name="keywords" content="liste, tâches, visualisation">
    <meta name="author" content="Marie-Pierre Plante">
    <?php include $niveau . "liaisons/fragments/headlinks.inc.php"; ?>
    <title>Liste : <?php echo htmlspecialchars($arrInfosListe['nom']); ?></title>
</head>
<body>
<?php include ($niveau . "liaisons/fragments/entete.inc.php");?>
<main class="bang">
<section class="meslistes">
            <header class="meslistes__header">
                <h2 class="meslistes__header__titre">Mes listes</h2>
</header>
            <ul class="meslistes__container">
                <?php
                // Définir la locale pour les dates en français canadien
                $formatter = new IntlDateFormatter('fr_CA', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Toronto', IntlDateFormatter::GREGORIAN, 'd MMMM yyyy');
                for ($i = 0; $i < min(3, count($arrItemsUrgentEcheance)); $i++) {
                    $item = $arrItemsUrgentEcheance[$i];
                    // Convertir la date en un format lisible
                    $date = new DateTime($item['echeance']);
                    $formattedDate = $formatter->format($date); // Formater la date en français canadien
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
    <section class="liste">
        <header class="liste__header">

            <h1>                     <span style="background-color: #<?php  echo $arrInfosListe['couleur']; ?>;" class="meslistes__item__couleur"></span>
            <?php echo htmlspecialchars($arrInfosListe['nom']); ?></h1>

        </header>
        <article class="liste__article">
            <ul class="liste__article__ul">
                <?php foreach ($arrItems as $item) { ?>
                    <li class="liste__article__ul__items">
                    <form action="afficher.php" method="get">
    <input type="hidden" name="id_liste" value="<?php echo htmlspecialchars($id_liste); ?>">
    <input type="hidden" name="id_item" value="<?php echo htmlspecialchars($item['id_item']); ?>">
    <button class="bouton__initial boutons__droites" type="submit" value="<?php echo htmlspecialchars($item['est_complete']); ?>" name="btn_etat">
        <?php echo htmlspecialchars($item['strValeurEtat']); ?>
    </button>
</form>
                        <form class="liste__article__ul__items__form" action="afficher.php" method="get">
                           
                            <input type="hidden" name="id_item" value="<?php echo htmlspecialchars($item['id_item']); ?>">
                            <input type="hidden" name="id_liste" value="<?php echo htmlspecialchars($id_liste); ?>">
                            <input type="hidden" name="code_operation" value="supprimer">
                            <ul class="liste__article__ul__items__form__titres">
                                <li><?php echo htmlspecialchars($item['item']); ?></li>
                            </ul>
                            <footer class="liste__article__ul__items__form__boutons">
                            
                                <a class="boutons__droites" href="<?php echo $niveau; ?>items/modifier.php?id_item=<?php echo htmlspecialchars($item['id_item']); ?>&id_liste=<?php echo htmlspecialchars($id_liste); ?>&code_operation=modifier">Éditer l'item</a>
                                <button type="submit" name="btn_supprimer" value="true" class="boutons__droites">Supprimer</button>
                            </footer>
                        </form>
                    </li>
                <?php } ?>
            </ul>
            <a class="liste__header__bouton" href="<?php echo $niveau; ?>items/modifier.php?code_operation=creer&id_liste=<?php echo htmlspecialchars($id_liste); ?>">Ajouter un item</a>

        </article>
    </section>
    </main>
    <?php include ($niveau . "liaisons/fragments/piedDePage.inc.php");?>

</body>
</html>
