<?php

require_once "../classes/Components.class.php";
require_once '../classes/Cursus.class.php';



$cursus = null;

if (isset($_GET['id'])) {
  try {
    $cursus = Cursus::createFromId($_GET['id']);
  }
  catch(Exception $e) {}
}


if ($cursus) {

}
else {


  $inputId = Components::hidden("id");
  $inputEtuSearch = Components::textWithIcon("etu_search", "Rechercher un étudiant", 'search');
  // $inputNumEtu = Components::number("numero_etudiant", "Numéro étudiant");
  $inputNumEtu = Components::select("numero_etudiant", "Numéro étudiant", array(), true);
  $inputNomCursus = Components::text("nom", "Nom du cursus");




  echo <<<HTML

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--8-col">
      <div class="lo07-card-title">
        Liste des cursus
      </div>

      <div class="lo07-card-body">
        <table class="lo07-list" id="lo07-cursus">

        </table>
      </div>
    </div>

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--4-col mdl-cell--top lo07-card-add" id="lo07-cursus-card">
      <div class="lo07-card-title">
        Ajouter un cursus
      </div>

      <div class="lo07-card-body">
        <form id="lo07-form-add" method='post' action='query/actions/cursus.php?action=add' data-onresponse="formResponse">
          {$inputId}
          <div>{$inputEtuSearch}</div>
          <div>{$inputNumEtu}</div>
          <div>{$inputNomCursus}</div>
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
          if (/=add/.test(this.getAttribute('action'))) swal("Ajouté !", "Cursus ajouté avec succès", "success");
          else if (/=edit/.test(this.getAttribute('action'))) swal("Informations modifiées !", "Les informations de ce cursus ont été modifiées avec succès", "success");
          refreshList();
          resetForm();
        }
      };

      var refreshList = function() {
        $.ajax("./query/actions/cursus.php?action=get", {
          dataType: "json",
          success: function(result) {
            if (result.error) {
              console.error(result.error);
            }
            else {
              var etudiants = result.response;
              var etudiantTable = document.getElementById('lo07-cursus');
              etudiantTable.innerHTML = '';
              for (var id in etudiants) {
                var tr = getEtudiantRow(
                  etudiants[id].numero,
                  etudiants[id].nom,
                  etudiants[id].prenom,
                  etudiants[id].admission,
                  etudiants[id].filiere
                );
                etudiantTable.appendChild(tr);
                var tr = document.createElement('tr');
                var td = document.createElement('td');
                var cursusTable = document.createElement('table');
                td.setAttribute('colspan', '7');
                cursusTable.className ='lo07-list';
                for (var idC in etudiants[id].cursus) {
                  var trC = getCursusRow(
                    etudiants[id].cursus[idC].id,
                    etudiants[id].cursus[idC].nom
                  );
                  cursusTable.appendChild(trC);
                }
                td.appendChild(cursusTable);
                tr.appendChild(td);
                etudiantTable.appendChild(tr);
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
          <td class="lo07-list-right">' + admission + '</td>\
          <td class="lo07-list-right">' + filiere + '</td>\
        ';
        tr.id = 'lo07-etudiant-' + numero;
        return tr;
      };

      var getCursusRow = function(id, nom) {
        var tr = document.createElement('tr');
        tr.innerHTML = '\
          <td><i class="material-icons lo07-text-icon">trending_up</i>' + nom + '</td>\
          <td class="lo07-list-right lo07-list-icon"><a class="mdl-list__item-primary-action lo07-lightgrey lo07-hover-blue lo07-transition-faster" href="' + document.location.href + (document.location.href.indexOf('?') !== -1 ? '&' : '?') + 'id=' + id + '"><i class="material-icons">assignment</i></a></td>\
          <td class="lo07-list-right lo07-list-icon"><a class="mdl-list__item-primary-action lo07-lightgrey lo07-hover-yellow lo07-transition-faster" onclick="editObject(' + id + ');"><i class="material-icons">edit</i></a></td>\
          <td class="lo07-list-right lo07-list-icon"><a class="mdl-list__item-secondary-action lo07-lightgrey lo07-hover-red lo07-transition-faster" onclick="deleteObject(' + id + ');"><i class="material-icons">delete</i></a></td>\
        ';
        tr.id = 'lo07-cursus-' + id;
        return tr;
      };

      var deleteObject = function(id) {
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
          $.ajax("./query/actions/cursus.php?action=delete", {
            type: 'post',
            data: 'id=' + id,
            dataType: "json",
            success: function(result) {
              if (result.error) {
                this.error();
              }
              else {
                var cursusEl = document.getElementById(('lo07-cursus-' + id));
                if (cursusEl) {
                  cursusEl.parentElement.removeChild(cursusEl);
                }
                swal("Supprimé !", "Le cursus a bien été supprimé de la liste.", "success");
              }
            },
            error: function(res) {
              refreshList();
              swal("Oups...", "Une erreur est survenue lors de la suppression.", "error");
            }
          });
        });
      };

      var editObject = function(id) {
        $.ajax("./query/actions/cursus.php?action=get", {
          type: 'post',
          data: 'id=' + id,
          dataType: "json",
          success: function(result) {
            if (result.error) {
              this.error();
            }
            else {
              var addCard = $('#lo07-cursus-card');
              addCard[0].classList.add('lo07-card-edit');
              addCard[0].classList.remove('lo07-card-add');
              var buttonLabel = $('#lo07-button-add-label');
              buttonLabel.html('Modifier');
              var cardTitle = $('#lo07-cursus-card .lo07-card-title');
              cardTitle.html("Modifier un cursus");
              var form = $('#lo07-form-add')[0];
              form.setAttribute('action', form.getAttribute('action').replace(/=add/, '=edit'))
              var cursus = result.response;
              updateInput(form.id, cursus.id);
              updateInput(form.numero_etudiant, cursus.numero_etudiant);
              updateInput(form.nom, cursus.nom);
              form.id.setAttribute('readonly', 'readonly');
            }
          },
          error: function(res) {
            refreshList();
            swal("Oups...", "Une erreur est survenue !", "error");
          }
        });
      };

      var resetForm = function() {
        var addCard = document.getElementById('lo07-cursus-card');
        addCard.classList.remove('lo07-card-edit');
        addCard.classList.add('lo07-card-add');
        var buttonLabel = document.getElementById('lo07-button-add-label');
        buttonLabel.innerHTML = 'Ajouter';
        var cardTitle = addCard.getElementsByClassName('lo07-card-title')[0];
        cardTitle.innerHTML = "Ajouter un cursus";
        var form = $('#lo07-form-add')[0];
        form.setAttribute('action', form.getAttribute('action').replace(/=edit/, '=add'))
        updateInput(form.id, '');
        updateInput(form.numero_etudiant, '');
        updateInput(form.nom, '');
        form.id.removeAttribute('readonly');
        return false;
      };

      var fillSelect = function() {
        var form = $('#lo07-form-add')[0];
        var search = form.etu_search.value;
        $.ajax("./query/actions/etudiant.php?action=search", {
          type: 'post',
          data: 'q=' + search,
          dataType: "json",
          success: function(result) {
            if (result.error) {
              this.error();
            }
            else {
              console.log(result.response);
              var list = {};
              for (var id in result.response) {
                list[result.response[id].numero] = result.response[id].numero + ' ' + result.response[id].prenom + ' ' + result.response[id].nom;
              }
              updateSelect(form.numero_etudiant, list, search.length > 0);
            }
          },
          error: function(res) {
            swal("Oups...", "Une erreur est survenue !", "error");
          }
        });
      };


      refreshList();

      $('#lo07-form-add')[0].etu_search.addEventListener('change', fillSelect);
      $('#lo07-form-add')[0].etu_search.addEventListener('keyup', fillSelect);
      fillSelect();
    </script>

HTML;

}
