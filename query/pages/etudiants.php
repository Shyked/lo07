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
      <table class="lo07-list" id="lo07-etudiants">
        <tr>
          <td><i class="material-icons mdl-list__item-avatar">person</i></td>
          <td>39959</td>
          <td class="lo07-list-primary">Rémy Haingue</td>
          <td>BR</td>
          <td>MPL</td>
          <td class="lo07-list-right"><a class="mdl-list__item-primary-action lo07-yellow" href="#"><i class="material-icons">edit</i></a></td>
          <td class="lo07-list-right"><a class="mdl-list__item-secondary-action lo07-red" href="#"><i class="material-icons">delete</i></a></td>
        </tr>
        <tr>
          <td><i class="material-icons mdl-list__item-avatar">person</i></td>
          <td>39959</td>
          <td class="lo07-list-primary">Rémy Haingue</td>
          <td>BR</td>
          <td>MPL</td>
          <td class="lo07-list-right"><a class="mdl-list__item-primary-action lo07-yellow" href="#"><i class="material-icons">edit</i></a></td>
          <td class="lo07-list-right"><a class="mdl-list__item-secondary-action lo07-red" href="#"><i class="material-icons">delete</i></a></td>
        </tr>
      </table>
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
        notice.classList.remove('lo07-green');
        notice.classList.add('lo07-red');
        notice.innerHTML = error;
      }
      else {
        notice.classList.remove('lo07-red');
        notice.classList.add('lo07-green');
        notice.innerHTML = 'Etudiant ajouté avec succès';
        refreshList();
      }
    };

    var refreshList = function() {
      $.ajax("./query/actions/etudiant.php?action=get", {
        dataType: "json",
        success: function(result) {
          if (result.error) {
            console.error(error);
          }
          else {
            var etudiants = result.response;
            var etudiantsTable = document.getElementById('lo07-etudiants');
            etudiantsTable.innerHTML = '';
            for (var id in etudiants) {
              var tr = document.createElement('tr');
              tr.innerHTML = '\
                <td><i class="material-icons mdl-list__item-avatar">person</i></td>\
                <td>' + etudiants[id].numero + '</td>\
                <td class="lo07-list-primary">' + etudiants[id].prenom + ' ' + etudiants[id].nom + '</td>\
                <td>' + etudiants[id].admission + '</td>\
                <td>' + etudiants[id].filiere + '</td>\
                <td class="lo07-list-right"><a class="mdl-list__item-primary-action lo07-yellow" href="#"><i class="material-icons">edit</i></a></td>\
                <td class="lo07-list-right"><a class="mdl-list__item-secondary-action lo07-red" href="#"><i class="material-icons">delete</i></a></td>\
              ';
              etudiantsTable.appendChild(tr);
            }
          }
        },
        error: function(res) {
          
        }
      });
    };

    refreshList();
  </script>

HTML;
