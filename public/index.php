<?php $niveau="./";?>
<?php include ($niveau . "liaisons/php/config.inc.php");?>
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

if (isset($_GET['liste_id'])) {
    $strRequete = "DELETE FROM listes WHERE id = " . (int)$_GET['liste_id'];
    $objResultat = $objPdo->prepare($strRequete);
    $objResultat->execute();


    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Planificateur, créateur de liste pour l'aide à l'organisation.">
	<meta name="keyword" content="Planificateur, planification, listes de planification, agenda">
	<meta name="author" content="Marie-Pierre Plante">
	<meta charset="utf-8">
	<title> N'oublie pas le TOFU! </title>
	<?php include ($niveau . "liaisons/fragments/headlinks.inc.php");?>
</head>

<body>

<?php include ($niveau . "liaisons/fragments/entete.inc.php");?>

<main class="bang">
	<div class="urgent-container">
		<div class="urgent">
			<img class="urgent__icon" src="liaisons/images/png/warning.png" alt="">
			<h2 class="urgent__title">Urgent</h2>
		</div>
		<ul class="urgent__list">
			<?php
			// Définir la locale pour les dates en français canadien
			$formatter = new IntlDateFormatter('fr_CA', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Toronto', IntlDateFormatter::GREGORIAN, 'd MMMM yyyy');
			for ($i = 0; $i < min(3, count($arrItemsUrgentEcheance)); $i++) {
				$item = $arrItemsUrgentEcheance[$i];
				// Convertir la date en un format lisible
				$date = new DateTime($item['echeance']);
				$formattedDate = $formatter->format($date); // Formater la date en français canadien
				?>
				<li class="urgent__item" style="border-left: 8px solid #<?php echo $item['hexadecimal']; ?>;">
					<span class="urgent__item-name"><p><?php echo $item['nom_item']; ?></p></span>
					<span class="urgent__item-list"><p><?php echo $item['nom_liste']; ?></p></span>
					<span class="urgent__item-date"><p><?php echo $formattedDate; ?></p></span>
				</li>
			<?php } ?>
		</ul>
	</div>
<h1>Mes listes</h1>
<hr><section class="listes">
<?php foreach ($arrListes as $liste):

	
	$strRequete = "SELECT COUNT(id) as nombre FROM items WHERE liste_id =". $liste['id'];
				$objResultat = $objPdo->prepare($strRequete);
			   
				$objResultat->execute();
				$arrNombre = $objResultat->fetch();
	?> 
	<article class="article__item" href="<?php echo $niveau; ?>pages/liste/index.php?nom=<?php echo urlencode($liste['nom']); ?>">
		<header class="article__item__header">
			<h2><?php echo htmlspecialchars($liste['nom']); ?></h2>
		</header>

		<!-- Carré coloré -->
		<div class="article__item__color" 
			 style="background-color:#<?php echo ($liste['hexadecimal']); ?>; ">
		</div>

		<footer class="article__item__footer">
			<div class="flex">
				<a href="<?php echo $niveau; ?> items/afficher.php?id_liste=<?php echo $liste['id']; ?> ">
			<p class="article__item__footer__titre" > <?php  echo $arrNombre['nombre']; ?> Items</p>
			</a>
			<div class="article__item__footer__titre2">

			<a class="article__item__header__edit" href="<?php echo $niveau; ?>listes/modifier.php?id_liste=<?php echo $liste['id']; ?>&code_operation=modifier"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
<path d="M10.3407 17.7762L18.476 9.64085C17.1075 9.06911 15.8644 8.23422 14.8176 7.18354C13.7664 6.13642 12.9311 4.89303 12.3592 3.52397L4.22384 11.6593C3.58917 12.294 3.27128 12.6118 2.99849 12.9616C2.67663 13.3746 2.4004 13.8213 2.17462 14.2937C1.98432 14.6941 1.84243 15.1209 1.55864 15.9722L0.0604929 20.4634C-0.00845953 20.669 -0.0186896 20.8898 0.0309522 21.1009C0.080594 21.312 0.18814 21.5051 0.341501 21.6585C0.494863 21.8119 0.68796 21.9194 0.899089 21.969C1.11022 22.0187 1.33101 22.0085 1.53664 21.9395L6.02778 20.4414C6.88025 20.1576 7.30593 20.0157 7.70632 19.8254C8.18077 19.5995 8.62479 19.3249 9.03837 19.0015C9.38816 18.7287 9.70605 18.4108 10.3407 17.7762ZM20.7332 7.38373C21.5443 6.57258 22 5.47243 22 4.32529C22 3.17815 21.5443 2.078 20.7332 1.26685C19.922 0.455699 18.8218 8.54684e-09 17.6747 0C16.5276 -8.54684e-09 15.4274 0.455699 14.6163 1.26685L13.6406 2.24251L13.6824 2.36461C14.1631 3.74042 14.9499 4.9891 15.9835 6.01648C17.0416 7.08104 18.334 7.88338 19.7575 8.35939L20.7332 7.38373Z" fill="black"/>
</svg></a>  

<a href="index.php?liste_id=<?php echo $liste['id']; ?>">
<svg xmlns="http://www.w3.org/2000/svg" width="21" height="23" viewBox="0 0 21 23" fill="none">
	<path d="M3.07659 0.546943L10.3171 8.36621L17.5201 0.587458C17.6792 0.404571 17.8709 0.258268 18.0836 0.157322C18.2963 0.0563753 18.5258 0.00286506 18.7581 0C19.2556 0 19.7327 0.213423 20.0845 0.593319C20.4363 0.973214 20.6339 1.48846 20.6339 2.02572C20.6383 2.27407 20.5956 2.52078 20.5085 2.75068C20.4214 2.98059 20.2917 3.18885 20.1274 3.36269L12.8306 11.1414L20.1274 19.0215C20.4366 19.3481 20.6179 19.7904 20.6339 20.2572C20.6339 20.7944 20.4363 21.3097 20.0845 21.6896C19.7327 22.0695 19.2556 22.2829 18.7581 22.2829C18.5191 22.2936 18.2805 22.2505 18.0577 22.1563C17.8349 22.0622 17.6327 21.919 17.4638 21.7359L10.3171 13.9167L3.09534 21.7157C2.93685 21.8925 2.74751 22.0336 2.53824 22.131C2.32897 22.2283 2.10393 22.2799 1.87609 22.2829C1.3786 22.2829 0.901486 22.0695 0.549709 21.6896C0.197931 21.3097 0.000305202 20.7944 0.000305202 20.2572C-0.00406817 20.0088 0.0386 19.7621 0.125692 19.5322C0.212783 19.3023 0.342461 19.094 0.506766 18.9202L7.80355 11.1414L0.506766 3.2614C0.197609 2.93477 0.0163237 2.49247 0.000305202 2.02572C0.000305202 1.48846 0.197931 0.973214 0.549709 0.593319C0.901486 0.213423 1.3786 0 1.87609 0C2.32627 0.00607715 2.7577 0.202572 3.07659 0.546943Z" fill="black"/>
</svg>
</a>
		   
			</div>
			</div>
		</footer>
	</article>
	<br>
<?php endforeach; ?>
</section>



</main>



<?php include ($niveau . "liaisons/fragments/piedDePage.inc.php");?>

</body>
</html>