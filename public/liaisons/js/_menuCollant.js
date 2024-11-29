/**
 * @file Un menu collant simple / A basic sticky menu
 * @author Yves Hélie <yves.helie@cegep-ste-foy.qc.ca>
 * @author Ève Février <efevrier@cegep-ste-foy.qc.ca>
 * @version 1.0.1
 *
 * Structure HTML-BEM attendue :
 * nav.menu--collant
 *  ul.menu--collant__liste OU ul.menu--collant__liste.menu--fixe
 *    li.menu--collant__listeItem > a.menu--collant__lien
 * @todo Appliquer dans le html les classes BEM décrites ci-dessus
 */

//*******************
// Déclaration d'objet(s)
//*******************

var menuCollant = {
  refMenuCollant: null,
  distanceHaut: null,
  classeMenuFixe: 'menuCollant--fixe',

  configurerMenuCollant: function ()
  {
    // Initialisation de la référence à l'élément qui est le conteneur parent du menu.
    this.refMenuCollant = document.querySelector(".menuCollant");
    this.distanceHaut = this.refMenuCollant.offsetTop;
    console.log('configurerMenuCollant() - this.distanceHaut: ' + this.distanceHaut);
  },

  calculerHauteur: function ()
  {
    document.body.classList.remove(this.classeMenuFixe);
    this.distanceHaut = this.refMenuCollant.offsetTop;
    document.body.classList.add(this.classeMenuFixe);
    this.verifierEtat();
    console.log('calculerHauteur() - this.distanceHaut: ' + this.distanceHaut);
  },

  verifierEtat: function ()
  {
    if (window.scrollY >= this.distanceHaut)
    {
      document.body.classList.add(this.classeMenuFixe);
      console.log('verifierEtat() - classe ' + this.classeMenuFixe + ' ajoutée');
    } else
    {
      document.body.classList.remove(this.classeMenuFixe);
        console.log('verifierEtat() - classe ' + this.classeMenuFixe + ' retirée');
    }
  }
};


//*******************
// Écouteurs d'événements
//*******************
window.addEventListener('load', function () { menuCollant.configurerMenuCollant(); });
window.addEventListener('scroll', function () { menuCollant.verifierEtat(); });
window.addEventListener('resize', function () { menuCollant.calculerHauteur(); });