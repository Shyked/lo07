<?php

require_once "../classes/Components.class.php";

/* Formulaire visible à côté de la liste des étudiants */




$inputNumEtu = Components::number("numero", "Numéro étudiant");
$inputNom = Components::text("nom", "Nom");
$inputPrenom = Components::text("prenom", "Prenom");
$inputAdmission = Components::select("admission", "Admission", array(
  "TC",
  "BR"
));
$inputFiliere = Components::select("filiere", "Filière", array(
  "?",
  "MPL",
  "MSI",
  "MRI",
  "LIB"
));




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

  <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--4-col mdl-cell--top lo07-card-add" id="lo07-etudiant-card">
    <div class="lo07-card-title">
      Ajouter un étudiant
    </div>

    <div class="lo07-card-body">
      <form id="lo07-form-add" method='post' action='query/actions/etudiant.php?action=add' data-onresponse="formResponse">
        <div>{$inputNumEtu}</div>
        <div>{$inputNom}</div>
        <div>{$inputPrenom}</div>
        <div>{$inputAdmission}</div>
        <div>{$inputFiliere}</div>
        <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect lo07-only-edit lo07-button-submit lo07-button-cancel" onclick="return resetForm();">Annuler</button>
        <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent lo07-button lo07-button-submit" onclick="this.form.submit();"><span id="lo07-button-add-label">Ajouter</span></button>
        <div class='lo07-form-notice lo07-red'></div>
      </form>
    </div>
  </div>

  <script>
    var formResponse = function(response, error) {
      var notice = this.getElementsByClassName('lo07-form-notice')[0];
      if (error) {
        notice.innerHTML = error;
      }
      else {
        notice.innerHTML = '';
        if (/=add/.test(this.getAttribute('action'))) swal("Ajouté !", "Étudiant ajouté avec succès", "success");
        else if (/=edit/.test(this.getAttribute('action'))) swal("Informations modifiées !", "Les informations de cet étudiant ont été modifiées avec succès", "success");
        refreshList();
        resetForm();
      }
    };

    var refreshList = function() {
      $.ajax("./query/actions/etudiant.php?action=get", {
        dataType: "json",
        success: function(result) {
          if (result.error) {
            console.error(result.error);
          }
          else {
            var etudiants = result.response;
            var etudiantsTable = document.getElementById('lo07-etudiants');
            etudiantsTable.innerHTML = '';
            for (var id in etudiants) {
              var tr = getEtudiantRow(
                etudiants[id].numero,
                etudiants[id].nom,
                etudiants[id].prenom,
                etudiants[id].admission,
                etudiants[id].filiere
              );
              etudiantsTable.appendChild(tr);
            }
          }
        },
        error: function(res) {
          swal("Oups...", "Une erreur est survenue lors de la récupération de la liste...", "error");
        }
      });
    };

    var getEtudiantRow = function(numero, nom, prenom, admission, filiere) {
      var tr = document.createElement('tr');
      var color = getColorFromString(nom + prenom);
      tr.innerHTML = '\
        <td><i class="material-icons mdl-list__item-avatar" style="background-color: ' + color + ';">person</i></td>\
        <td>' + numero + '</td>\
        <td class="lo07-list-primary">' + prenom + ' ' + nom + '</td>\
        <td>' + admission + '</td>\
        <td>' + filiere + '</td>\
        <td class="lo07-list-right lo07-list-icon"><a class="mdl-list__item-primary-action lo07-lightgrey lo07-hover-yellow lo07-transition-faster" onclick="editObject(' + numero + ');"><i class="material-icons">edit</i></a></td>\
        <td class="lo07-list-right lo07-list-icon"><a class="mdl-list__item-secondary-action lo07-lightgrey lo07-hover-red lo07-transition-faster" onclick="deleteObject(' + numero + ');"><i class="material-icons">delete</i></a></td>\
      ';
      tr.id = 'lo07-etudiant-' + numero;
      return tr;
    };

    var deleteObject = function(numero) {
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
            if (result.error) {
              this.error();
            }
            else {
              var etuEl = document.getElementById(('lo07-etudiant-' + numero));
              if (etuEl) {
                etuEl.parentElement.removeChild(etuEl);
              }
              swal("Supprimé !", "L'étudiant a bien été supprimé de la liste.", "success");
            }
          },
          error: function(res) {
            refreshList();
            swal("Oups...", "Une erreur est survenue lors de la suppression.", "error");
          }
        });
      });
    };

    var editObject = function(numero) {
      $.ajax("./query/actions/etudiant.php?action=get", {
        type: 'post',
        data: 'numero=' + numero,
        dataType: "json",
        success: function(result) {
          if (result.error) {
            this.error();
          }
          else {
            var addCard = $('#lo07-etudiant-card');
            addCard[0].classList.add('lo07-card-edit');
            addCard[0].classList.remove('lo07-card-add');
            var buttonLabel = $('#lo07-button-add-label');
            buttonLabel.html('Modifier');
            var cardTitle = $('#lo07-etudiant-card .lo07-card-title');
            cardTitle.html("Modifier un étudiant");
            var form = $('#lo07-form-add')[0];
            form.setAttribute('action', form.getAttribute('action').replace(/=add/, '=edit'))
            var etudiant = result.response;
            updateInput(form.numero, etudiant.numero);
            updateInput(form.nom, etudiant.nom);
            updateInput(form.prenom, etudiant.prenom);
            updateInput(form.admission, etudiant.admission);
            updateInput(form.filiere, etudiant.filiere);
            form.numero.setAttribute('readonly', 'readonly');
          }
        },
        error: function(res) {
          refreshList();
          swal("Oups...", "Une erreur est survenue !", "error");
        }
      });
    };

    var resetForm = function() {
      var addCard = document.getElementById('lo07-etudiant-card');
      addCard.classList.remove('lo07-card-edit');
      addCard.classList.add('lo07-card-add');
      var buttonLabel = document.getElementById('lo07-button-add-label');
      buttonLabel.innerHTML = 'Ajouter';
      var cardTitle = addCard.getElementsByClassName('lo07-card-title')[0];
      cardTitle.innerHTML = "Ajouter un étudiant";
      var form = $('#lo07-form-add')[0];
      form.setAttribute('action', form.getAttribute('action').replace(/=edit/, '=add'))
      updateInput(form.numero, '');
      updateInput(form.nom, '');
      updateInput(form.prenom, '');
      updateInput(form.admission, '');
      updateInput(form.filiere, '');
      form.numero.removeAttribute('readonly');
      return false;
    };

    refreshList();
  </script>

HTML;
