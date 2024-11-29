<!DOCTYPE html> 
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width"/>
    <title>N'oublie pas le Tofu</title>
    <link rel="stylesheet" href="../ressources/liaisons/scss/layout/_entete.scss">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Londrina+Shadow&family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap');
    </style>

</head>
<body>
    <noscript><p>Le JavaScript n'est pas activé dans votre navigateur. Nous vous recommandons de l'activer afin d'améliorer votre expérience utilisateur.</p></noscript>
    <!--http://webaim.org/techniques/skipnav/-->
    <a href="#contenu" class="visuallyhidden focusable">Allez au contenu</a>

    <header role="banner" class="banner__entete">
    <div class="banner__contenu">
        <div class="banner__logo">
            <img src="../ressources/Images/svg/logo.svg" alt="Logo">
        </div>

        <h1 class="banner__titre">N'oublie pas le Tofu</h1>

        <div class="banner__actions">
            <button class="banner__bouton__utilisateur">
                <span class="banner__icon"><img src="../ressources/Images/svg/utilisateur.svg" alt="utilisateur"></span>
            </button>

            <div class="banner__recherche">
                <input type="text" placeholder="Rechercher" name="rechercher" class="banner__input">
                <button class="banner__bouton__loupe">
                    <span class="banner__icon"><img src="../ressources/Images/svg/rechercher.svg" alt="loupe"></span>
                </button>
            </div>
        </div>
    </div>
</header>


    <section class="conteneur">
        <header class="conteneur">
            <nav class="menuCollant">
                 <div class="conteneur">
                     <ul class="menuCollant__liste">
                        <li class="menuCollant__listeItem"><a class="menuCollant__lien" href="#">Lien 1</a></li>
                        <li class="menuCollant__listeItem"><a class="menuCollant__lien" href="#">Lien 2</a></li>
                        <li class="menuCollant__listeItem"><a class="menuCollant__lien" href="#">Lien 3</a></li>
                        <li class="menuCollant__listeItem"><a class="menuCollant__lien" href="#">Lien 4</a></li>
                     </ul>
                </div>
            </nav>
        </header>
    </section>
</body> 

<script src="js/_menuCollant.js"></script>
<script>
    document.body.classList.add('js');
</script>

</html>