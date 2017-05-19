<?php

require_once "../classes/Components.class.php";



$inputId = Components::hidden("id");
$inputSigle = Components::text("sigle", "Sigle");
$inputCategorie = Components::select("categorie", "Catégorie", array(
  "CS",
  "TM",
  "EC",
  "HT",
  "ME",
  "ST",
  "SE",
  "HP",
  "NPML"
));
$inputAffectation = Components::select("affectation", "Affectation", array(
  "TC",
  "TCBR",
  "FCBR"
));
$inputUtt = Components::checkbox("utt", "Se suit à l'UTT", true);




echo <<<HTML

  <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--8-col">
    <div class="lo07-card-title">
      Liste des éléments de formation
    </div>

    <div class="lo07-card-body">
      <table class="lo07-list" id="lo07-elements">

      </table>
    </div>
  </div>

  <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--4-col mdl-cell--top lo07-card-add" id="lo07-element-card">
    <div class="lo07-card-title">
      Ajouter un élément
    </div>

    <div class="lo07-card-body">
      <form id="lo07-form-add" method='post' action='query/actions/element.php?action=add' data-onresponse="formResponse">
        {$inputId}
        <div>{$inputSigle}</div>
        <div>{$inputCategorie}</div>
        <div>{$inputAffectation}</div>
        <div class="lo07-checkbox-block">{$inputUtt}</div>
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
        if (/=add/.test(this.getAttribute('action'))) swal("Ajouté !", "Élément de formation ajouté avec succès", "success");
        else if (/=edit/.test(this.getAttribute('action'))) swal("Informations modifiées !", "Les informations de cet élément de formation ont été modifiées avec succès", "success");
        refreshList();
        resetForm();
      }
    };

    var refreshList = function() {
      $.ajax("./query/actions/element.php?action=get", {
        dataType: "json",
        success: function(result) {
          if (result.error) {
            console.error(result.error);
          }
          else {
            var elements = result.response;
            var elementsTable = document.getElementById('lo07-elements');
            elementsTable.innerHTML = '';
            for (var id in elements) {
              var tr = getElementRow(
                elements[id].id,
                elements[id].sigle,
                elements[id].categorie,
                elements[id].affectation,
                elements[id].utt
              );
              elementsTable.appendChild(tr);
            }
          }
        },
        error: function(res) {
          swal("Oups...", "Une erreur est survenue lors de la récupération de la liste...", "error");
        }
      });
    };

    var getElementRow = function(id, sigle, categorie, affectation, utt) {
      var tr = document.createElement('tr');
      var color = getColorFromString(categorie);
      tr.innerHTML = '\
        <td class="lo07-list-primary">'
          + '<i class="material-icons lo07-text-icon" style="color: ' + color + ';">fiber_manual_record</i>'
          + sigle
          + (utt && utt !== "0" ? '' : ' <span class="lo07-tooltip-container"><div class="material-icons lo07-text-icon" style="color: #888;" id="lo07-utt-' + id + '">directions_car</div><div class="mdl-tooltip">Se suit hors UTT</div></span>')
        + '</td>\
        <td>' + categorie + '</td>\
        <td>' + affectation + '</td>\
        <td class="lo07-list-right lo07-list-icon"><a class="mdl-list__item-primary-action lo07-lightgrey lo07-hover-yellow lo07-transition-faster" onclick="editObject(' + id + ');"><i class="material-icons">edit</i></a></td>\
        <td class="lo07-list-right lo07-list-icon"><a class="mdl-list__item-secondary-action lo07-lightgrey lo07-hover-red lo07-transition-faster" onclick="deleteObject(' + id + ');"><i class="material-icons">delete</i></a></td>\
      ';
      tr.id = 'lo07-element-' + id;
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
        $.ajax("./query/actions/element.php?action=delete", {
          type: 'post',
          data: 'id=' + id,
          dataType: "json",
          success: function(result) {
            if (result.error) {
              this.error();
            }
            else {
              var elementEl = document.getElementById(('lo07-element-' + id));
              if (elementEl) {
                elementEl.parentElement.removeChild(elementEl);
              }
              swal("Supprimé !", "L'élément de formation a bien été supprimé de la liste.", "success");
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
      $.ajax("./query/actions/element.php?action=get", {
        type: 'post',
        data: 'id=' + id,
        dataType: "json",
        success: function(result) {
          if (result.error) {
            this.error();
          }
          else {
            var addCard = $('#lo07-element-card');
            addCard[0].classList.add('lo07-card-edit');
            addCard[0].classList.remove('lo07-card-add');
            var buttonLabel = $('#lo07-button-add-label');
            buttonLabel.html('Modifier');
            var cardTitle = $('#lo07-element-card .lo07-card-title');
            cardTitle.html("Modifier un élément");
            var form = $('#lo07-form-add')[0];
            form.setAttribute('action', form.getAttribute('action').replace(/=add/, '=edit'))
            var element = result.response;
            updateInput(form.id, element.id);
            updateInput(form.sigle, element.sigle);
            updateInput(form.categorie, element.categorie);
            updateInput(form.affectation, element.affectation);
            // updateInput(form.utt, element.utt);
            if (element.utt && element.utt !== "0") form.utt.parentElement.MaterialCheckbox.check();
            else form.utt.parentElement.MaterialCheckbox.uncheck();
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
      var addCard = document.getElementById('lo07-element-card');
      addCard.classList.remove('lo07-card-edit');
      addCard.classList.add('lo07-card-add');
      var buttonLabel = document.getElementById('lo07-button-add-label');
      buttonLabel.innerHTML = 'Ajouter';
      var cardTitle = addCard.getElementsByClassName('lo07-card-title')[0];
      cardTitle.innerHTML = "Ajouter un élément";
      var form = $('#lo07-form-add')[0];
      form.setAttribute('action', form.getAttribute('action').replace(/=edit/, '=add'))
      updateInput(form.id, '');
      updateInput(form.sigle, '');
      updateInput(form.categorie, '');
      updateInput(form.affectation, '');
      form.utt.parentElement.MaterialCheckbox.check();
      form.id.removeAttribute('readonly');
      return false;
    };


    var updateInput = function(input, value) {
      input.value = value;
      if (value.toString().length > 0) {
        input.parentElement.classList.add('is-dirty');
      }
      else {
        input.parentElement.classList.remove('is-dirty');
      }
    };

    var getColorFromString = function(str) {
      var nameSum = 0;
      for (var idS in str) nameSum += str.charCodeAt(idS);
      var nameSum2 = nameSum + str.charCodeAt(0);
      return 'hsl(' + Math.floor(nameSum * 40 % 360) + ', ' + Math.floor(30 + (nameSum2 * 20) % 60) + '%, ' + Math.floor(55 + (nameSum2 * 10) % 20) + '%)';
    };

    refreshList();
  </script>

HTML;
