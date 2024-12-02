<?php
$niveau = "../";
include($niveau . "liaisons/php/config.inc.php");

$messageErreur = ["nom" => "", "couleur" => ""];
$messageSucces = "";

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["nom"], $_GET["couleur_id"], $_GET["utilisateur_id"])) {
    $nom = trim($_GET["nom"]);
    $couleur_id = $_GET["couleur_id"];
    $utilisateur_id = $_GET["utilisateur_id"];

    if (!preg_match('/^[a-zA-ZÀ-ÿ0-9#\-"?!\.]{1,28}$/', $nom)) {
        $messageErreur["nom"] = "Nom invalide (1 à 28 caractères, incluant #-'?!.).";
    } elseif (empty($couleur_id)) {
        $messageErreur["couleur"] = "Veuillez sélectionner une couleur.";
    } else {
        try {
            $requete = 'INSERT INTO listes (nom, couleur_id, utilisateur_id) VALUES (:nom, :couleur_id, :utilisateur_id)';
            $stmt = $objPdo->prepare($requete);
            $stmt->execute([
                ':nom' => $nom,
                ':couleur_id' => $couleur_id,
                ':utilisateur_id' => $utilisateur_id
            ]);

            header("Location: {$niveau}index.php?message=" . urlencode("Liste \"{$nom}\" créée avec succès"));
            exit();
        } catch (PDOException $e) {
            $messageErreur["nom"] = "Erreur : " . $e->getMessage();
        }
    }
}

$couleurs = $objPdo->query('SELECT id, nom_fr, hexadecimal FROM couleurs')->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nouvelle Liste</title>
    <?php include($niveau . "liaisons/fragments/headlinks.inc.php"); ?>
    <style>
        .form-container { max-width: 600px; margin: auto; }
        .error { color: red; font-size: 0.9rem; }
        .color-option { display: flex; align-items: center; margin-bottom: 5px; }
        .color-swatch { width: 20px; height: 20px; margin-right: 10px; border: 1px solid #ccc; }
        .button { margin-top: 10px; padding: 10px; text-align: center; background-color: #000; color: #fff; border: none; cursor: pointer; }
    </style>
</head>
<body>
<?php include($niveau . "liaisons/fragments/entete.inc.php"); ?>

<main class="form-container">
    <h1>Nouvelle Liste</h1>
    <form action="ajouter.php" method="GET" onsubmit="return validateForm()">
        <label>Nom de la liste :
            <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($nom ?? '') ?>" required>
        </label>
        <div class="error"><?= $messageErreur["nom"] ?></div>

        <input type="hidden" name="utilisateur_id" value="1">
        <input type="hidden" id="couleur_id" name="couleur_id" value="<?= htmlspecialchars($couleur_id ?? '') ?>">
        <section class="section__couleur">
        <label>Couleur de la liste</label>
        <article class="article__couleur">
            <?php foreach ($couleurs as $couleur): ?>
                <label class="color-option">
                    <input type="radio" name="couleur" value="<?= $couleur['id'] ?>" 
                           data-name="<?= htmlspecialchars($couleur['nom_fr']) ?>" 
                           <?= isset($couleur_id) && $couleur_id == $couleur['id'] ? 'checked' : '' ?>>
                    <span class="color-swatch" style="background-color: #<?= $couleur['hexadecimal'] ?>"></span>
                   
                </label>
            <?php endforeach; ?>
            </article>
        </section>
        <div class="error"><?= $messageErreur["couleur"] ?></div>

        <button class="form-container__button" type="submit">Ajouter</button>
    </form>
</main>

<?php include($niveau . "liaisons/fragments/piedDePage.inc.php"); ?>

<script>
    document.querySelectorAll('input[name="couleur"]').forEach(input => {
        input.addEventListener('change', function () {
            document.getElementById('couleur_id').value = this.value;
        });
    });

    function validateForm() {
        let valid = true;
        const nom = document.getElementById('nom').value.trim();
        const couleurId = document.getElementById('couleur_id').value;
        const nomError = document.querySelector('.error');
        const colorError = document.querySelector('.error');

        if (!/^[a-zA-ZÀ-ÿ0-9#\-"?!\.]{1,28}$/.test(nom)) {
            nomError.textContent = "Nom invalide.";
            valid = false;
        } else {
            nomError.textContent = "";
        }

        if (!couleurId) {
            colorError.textContent = "Sélectionnez une couleur.";
            valid = false;
        } else {
            colorError.textContent = "";
        }

        return valid;
    }
</script>
</body>
</html>
