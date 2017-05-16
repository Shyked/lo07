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
        <div class='lo07-form-notice lo07-red'></div>
      </form>
    </div>
  </div>

  <script>
    var onresponse = function(response, error) {
      var notice = this.getElementsByClassName('lo07-form-notice')[0];
      if (error) {
        notice.innerHTML = error;
      }
      else {
        notice.innerHTML = '';
        swal("Ajouté !", "Étudiant ajouté avec succès", "success");
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
                <td class="lo07-list-right"><a class="mdl-list__item-primary-action lo07-lightgrey lo07-hover-yellow"><i class="material-icons">edit</i></a></td>\
                <td class="lo07-list-right"><a class="mdl-list__item-secondary-action lo07-lightgrey lo07-hover-red" onclick="deleteObject(' + etudiants[id].numero + ', event);"><i class="material-icons">delete</i></a></td>\
              ';
              tr.id = 'lo07-etudiant-' + etudiants[id].numero;
              etudiantsTable.appendChild(tr);
            }
          }
        },
        error: function(res) {
          
        }
      });
    };

    var deleteObject = function(numero, event) {
      swal({
        title: "Êtes-vous sûr ?",
        text: "La suppression ne peut être annulée, il vous faudra saisir les informations à nouveau.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#FF5252",
        confirmButtonText: "Oui, supprimer !",
        closeOnConfirm: false
      },
      function() {
        $.ajax("./query/actions/etudiant.php?action=delete", {
          type: 'post',
          data: 'numero=' + numero,
          dataType: "json",
          success: function(result) {
            var etuEl = document.getElementById(('lo07-etudiant-' + numero));
            if (etuEl) {
              etuEl.parentElement.removeChild(etuEl);
            }
            swal("Supprimé !", "L'étudiant a bien été supprimé de la liste.", "success");
          },
          error: function(res) {
            swal("Oups...", "Une erreur est survenue lors de la suppression.", "error");
          }
        });
      });
    };

    refreshList();
  </script>

HTML;
