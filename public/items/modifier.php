<?php
    //***************** Informations de création du fichier ******************//
    /**
     * @author Daoud Coulibaly - 2040480@csfoy.ca
     * @file liste/model.liste.php
     * @brief Page permettant de récupérer les données des item d'une liste.
     * @details Cette page permet de récupérer les données d'une liste en particulier pour l'affichage dans la vue.
     * @version 1.0
     * @date 28 novembre 2024
     */

    //  die("Le fichier est bien inclus.");
    //***************** Niveau ******************//
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
    if (isset($_GET['description'])) {
        $nomItem = $_GET['description'];
    }
    $est_complete = 0;

    //date
    if (isset($_GET['jour'])) {
        $jour = $_GET['jour'];
    }
    else {
        $jour = 0;
    }
    if (isset($_GET['mois'])) {
        $mois = $_GET['mois'];
    }
    else {
        $mois = 0;
    }
    if (isset($_GET['annee'])) {
        $annee = $_GET['annee'];
    }
    else {
        $annee = 0;
    }
    //Code d'opération
    $strCodeOperation = '';
    if (isset($_GET['btn_supprimer'])) {
        $strCodeOperation = 'supprimer';
    }
    if (isset($_GET['btn_creer'])) {
        $strCodeOperation = 'creer';
    }
    if(isset($_GET['code_operation'])) {
        switch($_GET['code_operation']) {
            case 'ajouter':
                $strCodeOperation = 'ajouter';
                break;
            case 'modifier':
                $strCodeOperation = 'modifier';
                break;
        }
    }
    //variable temporaire:
    $id_liste = 2;
?>

<?php 
    //***************** Crée un item de la liste ******************//
    //Vérification de la date
    if($jour != 0 && $mois != 0 && $annee != 0 && checkdate(intval($mois), intval($jour), intval($annee))) {
        $echeance = $annee . '-' . $mois . '-' . $jour;
    }
    else {
        $echeance = null;
    }

    var_dump($echeance);

    if($strCodeOperation == 'creer') {
        $strRequete = "INSERT INTO items (nom, echeance, est_complete, liste_id) VALUES (:nom, :echeance, :est_complete, :id_liste)";
        // $strRequete = "INSERT INTO items (nom, est_complete, liste_id) VALUES (:nom, :est_complete, :id_liste)";
        $objResultat = $objPdo->prepare($strRequete);
        $objResultat->bindParam(':nom', $nomItem);
        $objResultat->bindParam(':echeance', $echeance);
        $objResultat->bindParam(':est_complete', $est_complete);
        $objResultat->bindParam(':id_liste', $id_liste);
        $objResultat->execute();

        header("Location: " . $niveau . "pages/liste/index.php");
    }
?>
<?php 
    //***************** Modifie un item de la liste ******************//
    if($strCodeOperation == 'modifier') {
        $strRequete = "SELECT nom, DAY(echeance) AS jour, MONTH(echeance) AS mois, YEAR(echeance) AS annee, est_complete FROM items WHERE id = :id_item";
        $objResultat = $objPdo->prepare($strRequete);
        $objResultat->bindParam(':id_item', $id_item);
        $objResultat->execute();
        

        // die("Le fichier est bien inclus.");
        $ligne = $objResultat->fetch();
        $arrInfosItem['nom'] = $ligne['nom'];
        $arrInfosItem['jour'] = $ligne['jour'];
        $arrInfosItem['mois'] = $ligne['mois'];
        $arrInfosItem['annee'] = $ligne['annee'];
        $arrInfosItem['est_complete'] = $ligne['est_complete'];

        var_dump($arrInfosItem['nom']);

        $objResultat->closeCursor();

        // header("Location: " . $niveau . "pages/liste/index.php");
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page permettant de visualiser les tâches d'une liste.">
    <meta name="keywords" content="liste, tâches, visualisation">
    <meta name="author" content="Daoud Coulibaly">
    <?php include $niveau . "liaisons/fragments/headlinks.inc.php"; ?>
    <title>Document</title>
</head>
<body>
    <section class="creer">
        <header class="creer__header">

    </header>
    
    <form class="creer__article" action="<?php echo $niveau; ?>pages/item/index.php" method="get">
    <div class="creer__article__tache">
            <label for="description">Tâche: </label>
            <input type="text" name="description" id="description" value="">
        </div>
        <hr>
        <footer class="creer__article__footer">
            <button class="btn__echeance" type="button" name="btn_echeance" value="false">Ajouter une échéance</button>
            <div class="dateEcheance">
                <ul class="creer__article__footer__flex">
                <li class="jour">
                    <label for="jour"> </label>
                    <select name="jour" id="jour">
                        <option value="0">Jour</option>
                        <?php for ($i = 1; $i <= 31; $i++) { ?>
                                <option value="<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </option>
                        <?php } ?>
                    </select>
</li>
                <li class="mois">
                    <label for="mois"> </label>
                    <select name="mois" id="mois">
                        <option value="0">Mois</option>
                        <?php for ($i = 1; $i <= 12; $i++) { ?>
                                <option value="<?php echo $i;?>" >
                                    <?php echo $i; ?>
                                </option>
                        <?php } ?>
                    </select>
</li>
                <li class="annee">
                    <label for="annee"> </label>
                    <select name="annee" id="annee">
                        <option value="0">Année</option>
                        <?php 
							$anneeActuelle = date("Y");
							for ($i = $anneeActuelle; $i <= $anneeActuelle + 10; $i++) { ?>
								<option value="<?php echo $i; ?>">
									<?php echo $i; ?>
								</option>
						<?php } ?>
                    </select>
</li>
                </ul>
                <button type="button" id="reinitialiser" class="bouton__reinitialiser">Réinitialiser la date</button>
            </div>
            <input type="submit" value="Creer" name="btn_creer" class="btn__creer">

</footer>
    </form>
   
    </section>
</body>
</html>