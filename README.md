Projet LO07 : Gestion des cursus
==========

Arborescence
--------
```
│   index.html                            Fichier principal qui inclus tous les autres
│   lo07 - base with prefix.sql
│   lo07 - base.sql                       Export SQL de la structure
│   lo07.sql                              Export SQL avec exemples
│
├───css                                   Fichiers de style
│       styles.css
│
├───images                                Images de l'application
│       favicon.png
│
├───include                               Code externe, librairies/frameworks utilisés
│   ├───css
│   │       getmdl-select.min.css
│   │       material.min.css
│   │       styles-demo.css               Style pour le template utilisé
│   │       sweetalert.css
│   │
│   └───js
│           getmdl-select.min.js          Material design select
│           jquery-3.2.1.min.js           JQuery
│           jquery-plugin-4.2.1.min.js    Plugin pour envoyer les formulaires Ajax
│           material.min.js               Material Design Lite
│           sweetalert.min.js             Sweet Alert
│
├───js                                    Fichiers Javascript de l'application
│       script.js                         Script global, contient le PageManager
│       utils.js                          Fonctions utilitaires utilisées un peu partout
│
└───query                                 Fichiers php
    │   myPDO.class.php
    │   myPDO.include.php                 Accès à la base de données
    │
    ├───actions                           API pour gérer les ressources en frontend
    │       cursus.php
    │       element.php
    │       etudiant.php
    │       reglement.php
    │       test.php
    │
    ├───classes                           Déclaration des classes
    │       Components.class.php
    │       Cursus.class.php
    │       Cursus_Element.class.php
    │       Element.class.php
    │       Etudiant.class.php
    │       Reglement.class.php
    │       Reglement_Element.class.php
    │
    └───pages                             Contient le code HTML et JavaScript des pages
            cursus.php
            dashboard.php
            elements.php
            etudiants.php
            reglement.php
            tests.php
```


PageManager
---------

Le PageManager est un singleton déclaré dans `script.js`. Il gère le chargement dynamique des différentes pages.

Lors du chargement de la page, seul le menu est affiché, le reste du contenu de la page est chargé de façon asynchrone. C'est le PageManager qui, en fonction du paramètre GET `display` va charger faire une requête vers le fichier php contenu dans le dossier `pages` qui correspond à la page demandée.

Lors d'un clic sur un lien, l'évènement est récupéré et annulé par le PageManager s'il s'agit d'un lien interne à l'application, afin de ne pas recharge l'ensemble de la page, mais seulement le corps de celle-ci.

Le PageManager est aussi chargé de l'envoi des formulaires en Ajax.


Organisation de l'application
-----------------------------

L'organisation de l'application a été inspirée du modèle MVC (Modèle Vue Controlleur).

Les fichiers nécessaires au navigateur pour utiliser l'API orientée ressource sont déclarés dans le `index.html`. Il s'agit de tous les fichiers situés en dehors du dossier `query`.

Pour charger le contenu de la page (la vue), PageManager va appeler la page correspondante présente dans `query/pages`. Le script PHP va alors générer le code HTML, ainsi que le script JavaScript associé.  
Ce code JavaScript va communiquer avec l'API en backend pour effectuer des actions, telles que l'ajout d'un nouvel élément, son édition, sa supression, ou encore la récupération d'une liste. Ces actions sont effectuées grâce à une requêtes vers les scripts situés dans `query/actions` (les contrôleurs). Les scripts sont divisés principaleent en fonction des modèles, puis divisés à l'intérieur en fonction de l'`action` passée en paramètre GET (get, add, edit, delete...).  
Enfin, ces contrôleurs seront en relation avec les modèles présents dans `query/classes` pour pouvoir interagir avec la base de données.


Librairies externes
------------

>**Material Design Lite**  
>Le design utilisé pour cette application provient de Material Design Lite qui et à disposition des éléments permettant d'adopter facilement le Material Design. L'application est basée sur le template [Dashboard](https://getmdl.io/templates/dashboard/index.html).

>**GetMDL-Select**  
>La librairie MDL ne propose pas de version en Material Design pour l'élément `select`. GetMDL-Select permet de contourner ce problème.

>**JQuery**  
>Framework aujourd'hui incontournable pour simplifier l'utilisation du JavaScript.

>**JQuery Plugins**  
>Offre la possibilité de surcharger l'envoi des formulaires pour qu'il se fasse en Ajax et qu'il ne recharge pas la page entière.

>**Sweet Alert**  
>Remplace la fonction `alert` par défaut, peu esthétique, par une version beaucoup plus visuelle et avancée. Utilisé en appelant la fonction `swal`.
