<?php

require_once "../classes/Components.class.php";

/* Formulaire visible à côté de la liste des étudiants */




$inputNumEtu = Components::number("numero", "Numéro étudiant");
$inputNom = Components::text("nom", "Nom");
$inputPrenom = Components::text("prenom", "Prenom");
$inputAdmission = Components::select("admission", "Admission", array(
  "TC",
  "BR"
), null);
$inputFiliere = Components::select("filiere", "Filière", array(
  "?",
  "MPL",
  "MSI",
  "MRI",
  "LIB"
), "");




echo <<<HTML

  <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--8-col">
    <div class="lo07-card-title">
      Liste des étudiants
    </div>

    <div class="lo07-card-body">
      
    </div>
  </div>

  <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--4-col mdl-cell--top">
    <div class="lo07-card-title lo07-card-background-accent">
      Ajouter un étudiant
    </div>

    <div class="lo07-card-body">
      <form method='post' action='query/actions/etudiant.php?action=add' data-onresponse="onresponse">
        <div>{$inputNumEtu}</div>
        <div>{$inputNom}</div>
        <div>{$inputPrenom}</div>
        <div>{$inputAdmission}</div>
        <div>{$inputFiliere}</div>
        <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent lo07-submit" onclick="this.form.submit();">Ajouter</button>
        <div class='lo07-form-notice'></div>
      </form>
    </div>
  </div>

  <script>
    var onresponse = function(response, error) {
      var notice = this.getElementsByClassName('lo07-form-notice')[0];
      if (error) {
        notice.classList.remove('lo07-form-notice-success');
        notice.classList.add('lo07-form-notice-error');
        notice.innerHTML = error;
      }
      else {
        notice.classList.remove('lo07-form-notice-error');
        notice.classList.add('lo07-form-notice-success');
        notice.innerHTML = 'Etudiant ajouté avec succès';
      }
    };
  </script>

HTML;
