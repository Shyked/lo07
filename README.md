Projet LO07 : Gestion des cursus
==========

Arborescence
--------
```
│   index.html                     Fichier principal qui inclus tous les autres
│   lo07 - base with prefix.sql
│   lo07 - base.sql                Export SQL de la structure
│   lo07.sql                       Export SQL avec exemples
│
├───css                            Fichiers de style
│       styles.css
│
├───images                         Images de l'application
│       favicon.png
│
├───include                        Code externe, librairies/frameworks utilisés
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
├───js                             Fichiers Javascript de l'application
│       script.js                  Script global, contient le PageManager
│       utils.js                   Fonctions utilitaires utilisées un peu partout
│
└───query                          Fichiers php
    │   myPDO.class.php
    │   myPDO.include.php          Accès à la base de données
    │
    ├───actions                    API pour gérer les ressources en frontend
    │       cursus.php
    │       element.php
    │       etudiant.php
    │       reglement.php
    │       test.php
    │
    ├───classes                    Déclaration des classes
    │       Components.class.php
    │       Cursus.class.php
    │       Cursus_Element.class.php
    │       Element.class.php
    │       Etudiant.class.php
    │       Reglement.class.php
    │       Reglement_Element.class.php
    │
    └───pages                      Contient le code HTML et JavaScript des pages
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


Material Design Lite
------------
