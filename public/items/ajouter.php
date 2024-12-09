<?php
    // ***************** Fichier de gestion des items d'une liste ******************//
    /**
     * @author Laurie Roy
     * @brief Script de gestion des données liées aux items d'une liste.
     * @details Ce script gère la l'ajout des items d'une liste spécifique.
     * @version 2.0
     * @date 28 novembre 2024
     */

    // Niveau de répertoire
    $niveau = "../";

    // Inclusion de la configuration de la base de données
    include($niveau . 'liaisons/php/config.inc.php');

    // Initialisation des variables
    $idListe = $_GET['id_liste'] ?? null;
    $idItem = $_GET['id_item'] ?? null;
    $itemDescription = $_GET['description'] ?? null;
    $isComplete = 0;

    // Gestion de la date
    $jour = $_GET['jour'] ?? 0;
    $mois = $_GET['mois'] ?? 0;
    $annee = $_GET['annee'] ?? 0;

    // Vérification de l'opération à effectuer
    $operation = $_GET['code_operation'] ?? '';
    if (isset($_GET['btn_supprimer'])) $operation = 'supprimer';
    if (isset($_GET['btn_creer'])) $operation = 'creer';

    // Définition d'une échéance si la date est valide
    $echeance = null;
    if ($jour && $mois && $annee && checkdate((int)$mois, (int)$jour, (int)$annee)) {
        $echeance = sprintf('%04d-%02d-%02d', $annee, $mois, $jour);
    }

    // Opération : Création d'un item
    if ($operation === 'creer') {
        // Valider les données
        if (empty($itemDescription)) {
            die("Erreur : La description de l'item est requise.");
        }
        if (empty($idListe)) {
            die("Erreur : ID de la liste manquant.");
        }
    
        // Insertion dans la base de données
        $sql = "INSERT INTO items (nom, echeance, est_complete, liste_id) 
                VALUES (:nom, :echeance, :est_complete, :id_liste)";
        $stmt = $objPdo->prepare($sql);
        $stmt->bindParam(':nom', $itemDescription);
        $stmt->bindParam(':echeance', $echeance);
        $stmt->bindParam(':est_complete', $isComplete);
        $stmt->bindParam(':id_liste', $idListe);
        $stmt->execute();
    
        // Redirection après succès
        header("Location: afficher.php?id_liste=$idListe");
        exit;
    }
    

    // Opération : Modification d'un item
    if ($operation === 'modifier') {
        $sql = "SELECT nom, DAY(echeance) AS jour, MONTH(echeance) AS mois, 
                       YEAR(echeance) AS annee, est_complete 
                FROM items WHERE id = :id_item";
        $stmt = $objPdo->prepare($sql);
        $stmt->bindParam(':id_item', $idItem);
        $stmt->execute();
        $itemDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    }
    
?>
<?php include ($niveau . "liaisons/php/config.inc.php");?>
<?php $requeteSQL = "
    SELECT listes.nom, listes.id, couleurs.hexadecimal 
    FROM listes
    INNER JOIN couleurs ON listes.couleur_id = couleurs.id
";
$objStat = $objPdo->prepare($requeteSQL);
$objStat->execute();
$arrListes = $objStat->fetchAll();
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
    <meta name="description" content="Gérez les tâches de votre liste en toute simplicité.">
    <meta name="keywords" content="gestion, liste, tâches">
    <meta name="author" content="TonNom">
    <?php include $niveau . "liaisons/fragments/headlinks.inc.php"; ?>
    <title>Gestion de Liste</title>
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
            <section class="">
<h2>Mes Listes</h2>
<?php foreach ($arrListes as $liste): ?> 
                <h3><?php echo htmlspecialchars($liste['nom']); ?></h3>
            </section>
            <?php endforeach; ?>

    <section class="gestion">
        <header class="gestion__header">
            <h1>Gérer les Tâches</h1>
        </header>
        <form class="gestion__form" action="<?php echo $niveau; ?>items/index.php" method="get">
            <div class="form__group">
                <label for="description">Tâche :</label>
                <input type="text" name="description" id="description" value="">
            </div>
            <div class="form__group">
                <label for=""></label>
                <select name="jour" id="jour">
                    <option value="0">Jour</option>
                    <?php for ($i = 1; $i <= 31; $i++) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
                <label for="mois"></label>
                <select name="mois" id="mois">
                    <option value="0">Mois</option>
                    <?php for ($i = 1; $i <= 12; $i++) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
                <label for="annee"></label>
                <select name="annee" id="annee">
                    <option value="0">Année</option>
                    <?php $currentYear = date("Y"); ?>
                    <?php for ($i = $currentYear; $i <= $currentYear + 10; $i++) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form__buttons">
                <button type="submit" name="btn_creer" class="btn">Créer</button>
                <button type="reset" class="btn btn--reset">Réinitialiser</button>
            </div>
        </form>
    </section>
    </main>
    <?php include ($niveau . "liaisons/fragments/piedDePage.inc.php");?>
</body>
</html>
