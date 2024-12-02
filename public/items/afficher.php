<?php
  
    $niveau = "../";

    //***************** Inclusion de la connexion à la base de données ******************//
    include($niveau . 'liaisons/php/config.inc.php');

    //***************** Variables importante ******************//
    if (isset($_GET['id_liste'])) {
        $id_liste = $_GET['id_liste'];
    }
    if (isset($_GET['id_item'])) {
        $id_item = $_GET['id_item'];
    }

    //Code d'opération
    $strCodeOperation = '';
    if (isset($_GET['btn_supprimer'])) {
        $strCodeOperation = 'supprimer';
    }
    //variable temporaire:
    $id_liste = 2;

?>


<?php
    //***************** Récupération de tous les items d'une liste ******************//
    // $strRequete = "SELECT id, nom, echeance, est_complete FROM items WHERE liste_id = :id_liste ORDER BY est_complete, echeance";
    $strRequete = "SELECT id, nom, echeance, est_complete FROM items WHERE liste_id = :id_liste ORDER BY nom";
    $objResultat = $objPdo->prepare($strRequete);

    $objResultat->bindParam(':id_liste', $id_liste);
    $objResultat->execute();

    $arrItems = array();
    for($i = 0; $ligne = $objResultat->fetch(); $i++) {
        $arrItems[$i]['id_item'] = $ligne['id'];
        $arrItems[$i]['item'] = $ligne['nom'];
        $arrItems[$i]['echeance'] = $ligne['echeance'];
        $arrItems[$i]['est_complete'] = $ligne['est_complete'];
        $arrItems[$i]['strValeurEtat'] = null;

        //Gestion du texte d'affichage
        switch ($arrItems[$i]['est_complete']) {
            case 0:
                $arrItems[$i]['strValeurEtat'] = 'Initial';
                break;
            case 1:
                $arrItems[$i]['strValeurEtat'] = 'Complété';
                break;
        }
    }

    $objResultat->closeCursor();
?>

<?php
    //***************** Suppréssion d'un item de la liste ******************//
    if($strCodeOperation == 'supprimer') {
        echo "suppression";
        $strRequete = "DELETE FROM items WHERE id = :id_item";
        $objResultat = $pdoConnexion->prepare($strRequete);
        $objResultat->bindParam(':id_item', $id_item);
        $objResultat->execute();


        //rafraichissement automatique de la page en redirigeant vers la page actuelle
	    header("location: ".$niveau."pages/liste/index.php");
    }
?>

<?php
    //***************** Modification de l'état de l'item ******************//
    if(isset($_GET['btn_etat'])) {
        $strRequete = "UPDATE items SET est_complete = :id_etat WHERE liste_id = :id_liste AND id = :id_item";
        echo "rentre";

        switch ($_GET['btn_etat']) {
            case '0':
                $arrItems[$id_item]['est_complete'] = 1;
                break;
            case '1':
                $arrItems[$id_item]['est_complete'] = 0;
                break;
        }

        $objResultat = $objPdo->prepare($strRequete);

        $objResultat->bindParam(':id_etat',  $arrItems[$id_item]['est_complete']);
        $objResultat->bindParam(':id_liste', $id_liste);
        $objResultat->bindParam(':id_item', $id_item);

        $objResultat->execute();

        //redirection automatique vers la page d'accueil (gestion pour serveur).
	    header("location: ".$niveau."pages/liste/index.php");

    

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

<?php

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
    <title>Document</title>
</head>
<body>
    <section class="liste">
        <header class="listes__header">
    <h1>Liste: <?php echo $arrInfosListe['nom']; ?></h1>
    <a class="listes__header__bouton" href="<?php echo $niveau; ?>pages/item/index.php?code_operation=creer">Ajouter un item</a>
    </header>
    <article class="listes__article">
        <ul class="listes__article__ul">
        <?php foreach ($arrItems as $item) { ?>
            <li class="listes__article__ul__items">
            <form class="listes__article__ul__items__form" action="<?php echo $niveau; ?>pages/liste/index.php" method="get">
            <button class="bouton__initial" type="submit" value="<?php echo $item['est_complete']; ?>" name="btn_etat"><?php echo $item['strValeurEtat']; ?></button>   
            <input type="hidden" name="id_item" value="<?php echo $item['id_item']; ?>">
                <ul class="listes_article__ul__items__form__titres">
                    <li><?php echo $item['item']; ?></li>
                    <li><?php echo $item['echeance']; ?></li>
                    <li><?php echo $item['est_complete']; ?></li>
                </ul>
                <footer class="listes__article__ul__items__form__boutons">

              
                <a class="boutons__droites" href="<?php echo $niveau;?>pages/item/index.php?id_item=<?php echo $item['id_item']; ?>&code_operation=modifier">Éditer l'item</a>
                <input class="boutons__droites" type="submit" value="Supprimer" name="btn_supprimer">
               
                </footer>
            </form>
            </li>
        <?php } ?>
        </ul>
        </article>
           
            </section>
</body>
</html>