<?php

require_once "../classes/Components.class.php";
require_once '../classes/Reglement.class.php';



$reglement = null;

if (isset($_GET['id'])) {
  try {
    $reglement = Reglement::createFromID($_GET['id']);
  }
  catch(Exception $e) {}
}


if ($reglement) {


  $inputId = Components::hidden(array(
    "name" => "id"
  ));
  $inputRegle = Components::text(array(
    "name" => "id_regle",
    "label" => "ID de la règle"
  ));
  $inputAgregat = Components::select(array(
    "name" => "agregat",
    "label" => "Agrégat",
    "list" => array(
      'SUM',
      'EXIST'
    )
  ));
  $inputCategorie = Components::text(array(
    "name" => "categorie",
    "label" => "Catégorie"
  ));
  $inputAffectation = Components::select(array(
    "name" => "affectation",
    "label" => "Affectation",
    "list" => array(
      "",
      "TC",
      "BR",
      "TCBR",
      "FCBR",
      "UTT"
    )
  ));
  $inputCredit = Components::number(array(
    "name" => "credit",
    "label" => "Crédits"
  ));


  echo <<<HTML

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--8-col">
      <div class="lo07-card-title">
        <a href="?display=reglement" class="material-icons lo07-button-previous">arrow_back</a>{$reglement->getNom()}
      </div>

      <div class="lo07-card-body">
        <table class="lo07-list" id="lo07-reglement_element">
          <tr><td class="lo07-text-center"><div class="mdl-spinner mdl-js-spinner is-active"></div></td></tr>
        </table>
        <button id="lo07-export-csv" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored lo07-button lo07-button-submit"><span>Exporter CSV</span></button>
      </div>
    </div>

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--4-col mdl-cell--6-col-tablet mdl-cell--top lo07-card-add" id="lo07-reglement_element-card">
      <div class="lo07-card-title">
        Ajouter au règlement
      </div>

      <div class="lo07-card-body">
        <form id="lo07-form-add" method='post' action='query/actions/reglement.php?id={$reglement->getId()}&action=add' data-onresponse="formResponse" onsubmit="return false;">
          {$inputId}
          <div>{$inputRegle}</div>
          <div>{$inputAgregat}</div>
          <div>{$inputCategorie}</div>
          <div>{$inputAffectation}</div>
          <div>{$inputCredit}</div>
          <button class="hidden" onclick="this.form.submit();"></button>
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
          if (/=add/.test(this.getAttribute('action'))) swal("Ajouté !", "Règle ajoutée avec succès", "success");
          else if (/=edit/.test(this.getAttribute('action'))) swal("Informations modifiées !", "Les informations de cette règle ont été modifiées avec succès", "success");
          refreshList();
          resetForm();
        }
      };

      var refreshList = function() {
        $.ajax("./query/actions/reglement.php?id={$reglement->getId()}&action=get", {
          dataType: "json",
          success: function(result) {
            if (result.error) {
              console.error(result.error);
            }
            else {
              var reglementElements = result.response;
              var reglementElementTable = document.getElementById('lo07-reglement_element');
              reglementElementTable.innerHTML = '';
              for (var id in reglementElements) {
                var tr = getReglementElementRow(
                  reglementElements[id].id,
                  reglementElements[id].id_regle,
                  reglementElements[id].agregat,
                  reglementElements[id].affectation,
                  reglementElements[id].categorie,
                  reglementElements[id].credit,
                );
                reglementElementTable.appendChild(tr);
              }
            }
          },
          error: function(res) {
            swal("Oups...", "Une erreur est survenue lors de la récupération de la liste...", "error");
          }
        });
      };

      var getReglementElementRow = function(id, id_regle, agregat, affectation, categorie, credit) {
        var tr = document.createElement('tr');
        var color = getColorFromString(agregat);
        tr.innerHTML = '\
          <td class="lo07-list-icon"><i class="material-icons" style="color: ' + color + ';">label</i></td>\
          <td class="lo07-list-primary">' + id_regle + '</td>\
          <td>' + agregat + '</td>\
          <td>' + categorie + '</td>\
          <td>' + affectation + '</td>\
          <td>' + credit + '</td>\
          <td class="lo07-list-right lo07-list-icon"><a class="mdl-list__item-primary-action lo07-lightgrey lo07-hover-yellow lo07-transition-faster" onclick="editObject(' + id + ');"><i class="material-icons">edit</i></a></td>\
          <td class="lo07-list-right lo07-list-icon"><a class="mdl-list__item-secondary-action lo07-lightgrey lo07-hover-red lo07-transition-faster" onclick="deleteObject(' + id + ');"><i class="material-icons">delete</i></a></td>\
        ';
        tr.id = 'lo07-reglement_element-' + id;
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
          $.ajax("./query/actions/reglement.php?id={$reglement->getId()}&action=delete", {
            type: 'post',
            data: 'id=' + id,
            dataType: "json",
            success: function(result) {
              if (result.error) {
                this.error();
              }
              else {
                var reglementElEl = document.getElementById(('lo07-reglement_element-' + id));
                if (reglementElEl) {
                  reglementElEl.parentElement.removeChild(reglementElEl);
                }
                refreshList();
                swal("Supprimé !", "La règle a bien été supprimée de la liste.", "success");
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
        $.ajax("./query/actions/reglement.php?id={$reglement->getId()}&action=get", {
          type: 'post',
          data: 'id=' + id,
          dataType: "json",
          success: function(result) {
            if (result.error) {
              this.error();
            }
            else {
              var addCard = $('#lo07-reglement_element-card')[0];
              addCard.classList.add('lo07-card-edit');
              addCard.classList.remove('lo07-card-add');
              var buttonLabel = $('#lo07-button-add-label');
              buttonLabel.html('Modifier');
              var cardTitle = $('#lo07-reglement_element-card .lo07-card-title');
              cardTitle.html("Modifier un élément");
              var form = $('#lo07-form-add')[0];
              form.setAttribute('action', form.getAttribute('action').replace(/=add/, '=edit'));
              var reglement_element = result.response;
              updateInput(form.id, reglement_element.id);
              updateInput(form.id_regle, reglement_element.id_regle);
              updateInput(form.agregat_selectLabel, reglement_element.agregat);
              updateInput(form.categorie, reglement_element.categorie);
              updateInput(form.affectation_selectLabel, reglement_element.affectation);
              updateInput(form.credit, reglement_element.credit);
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
        var addCard = $('#lo07-reglement_element-card')[0];
        addCard.classList.remove('lo07-card-edit');
        addCard.classList.add('lo07-card-add');
        var buttonLabel = document.getElementById('lo07-button-add-label');
        buttonLabel.innerHTML = 'Ajouter';
        var cardTitle = addCard.getElementsByClassName('lo07-card-title')[0];
        cardTitle.innerHTML = "Ajouter un élément";
        var form = $('#lo07-form-add')[0];
        form.setAttribute('action', form.getAttribute('action').replace(/=edit/, '=add'));
        updateInput(form.id, '');
        updateInput(form.id_regle, '');
        updateInput(form.agregat_selectLabel, '');
        updateInput(form.categorie, '');
        updateInput(form.affectation_selectLabel, '');
        updateInput(form.credit, '');
        form.id.removeAttribute('readonly');
        return false;
      };


      $("#lo07-export-csv").click(function(e) {
        document.location.href = "./query/actions/reglement.php?id={$reglement->getId()}&action=export";
      });


      refreshList();
    </script>

HTML;













}
else {












  $inputId = Components::hidden(array(
    "name" => "id"
  ));
  $inputNomReglement = Components::text(array(
    "name" => "nom",
    "label" => "Nom du règlement"
  ));




  echo <<<HTML

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--8-col">
      <div class="lo07-card-title">
        Liste des règlement
      </div>

      <div class="lo07-card-body">
        <table class="lo07-list" id="lo07-reglement">
          <tr><td class="lo07-text-center"><div class="mdl-spinner mdl-js-spinner is-active"></div></td></tr>
        </table>
        <form action='query/actions/reglement.php?action=import' method="post" data-onresponse="importResponse" enctype="multipart/form-data" onsubmit="return false;">
          <input type="file" id="lo07-file" class="lo07-file" name="csv_import" />
          <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored lo07-button lo07-button-submit lo07-js-file"><span>Importer CSV</span></button>
        </form>
      </div>
    </div>

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--4-col mdl-cell--6-col-tablet mdl-cell--top lo07-card-add" id="lo07-reglement-card">
      <div class="lo07-card-title">
        Ajouter un règlement
      </div>

      <div class="lo07-card-body">
        <form id="lo07-form-add" method='post' action='query/actions/reglement.php?action=add' data-onresponse="formResponse" onsubmit="return false;">
          {$inputId}
          <div>{$inputNomReglement}</div>
          <button class="hidden" onclick="this.form.submit();"></button>
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
          if (/=add/.test(this.getAttribute('action'))) swal("Ajouté !", "Règlement ajouté avec succès", "success");
          else if (/=edit/.test(this.getAttribute('action'))) swal("Informations modifiées !", "Les informations de ce règlement ont été modifiées avec succès", "success");
          refreshList();
          resetForm();
        }
      };

      var refreshList = function() {
        $.ajax("./query/actions/reglement.php?action=get", {
          dataType: "json",
          success: function(result) {
            if (result.error) {
              console.error(result.error);
            }
            else {
              var reglements = result.response;
              var reglementTable = document.getElementById('lo07-reglement');
              reglementTable.innerHTML = '';
              for (var id in reglements) {
                var tr = getReglementRow(
                  reglements[id].id,
                  reglements[id].nom
                );
                reglementTable.appendChild(tr);
              }
            }
          },
          error: function(res) {
            swal("Oups...", "Une erreur est survenue lors de la récupération de la liste...", "error");
          }
        });
      };

      var getReglementRow = function(id, nom) {
        var tr = document.createElement('tr');
        tr.innerHTML = '\
          <td><i class="material-icons lo07-text-icon">check</i>' + nom + '</td>\
          <td class="lo07-list-right lo07-list-icon"><a class="mdl-list__item-primary-action lo07-lightgrey lo07-hover-blue lo07-transition-faster" href="' + document.location.href + (document.location.href.indexOf('?') !== -1 ? '&' : '?') + 'id=' + id + '"><i class="material-icons">assignment</i></a></td>\
          <td class="lo07-list-right lo07-list-icon"><a class="mdl-list__item-primary-action lo07-lightgrey lo07-hover-yellow lo07-transition-faster" onclick="editObject(' + id + ');"><i class="material-icons">edit</i></a></td>\
          <td class="lo07-list-right lo07-list-icon"><a class="mdl-list__item-secondary-action lo07-lightgrey lo07-hover-red lo07-transition-faster" onclick="deleteObject(' + id + ');"><i class="material-icons">delete</i></a></td>\
        ';
        tr.id = 'lo07-reglement-' + id;
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
          $.ajax("./query/actions/reglement.php?action=delete", {
            type: 'post',
            data: 'id=' + id,
            dataType: "json",
            success: function(result) {
              if (result.error) {
                this.error();
              }
              else {
                var reglementEl = document.getElementById(('lo07-reglement-' + id));
                if (reglementEl) {
                  reglementEl.parentElement.removeChild(reglementEl);
                }
                swal("Supprimé !", "Le reglement a bien été supprimé de la liste.", "success");
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
        $.ajax("./query/actions/reglement.php?action=get", {
          type: 'post',
          data: 'id=' + id,
          dataType: "json",
          success: function(result) {
            if (result.error) {
              this.error();
            }
            else {
              var addCard = $('#lo07-reglement-card');
              addCard[0].classList.add('lo07-card-edit');
              addCard[0].classList.remove('lo07-card-add');
              var buttonLabel = $('#lo07-button-add-label');
              buttonLabel.html('Modifier');
              var cardTitle = $('#lo07-reglement-card .lo07-card-title');
              cardTitle.html("Modifier un reglement");
              var form = $('#lo07-form-add')[0];
              form.setAttribute('action', form.getAttribute('action').replace(/=add/, '=edit'))
              var reglement = result.response;
              updateInput(form.id, reglement.id);
              updateInput(form.nom, reglement.nom);
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
        var addCard = document.getElementById('lo07-reglement-card');
        addCard.classList.remove('lo07-card-edit');
        addCard.classList.add('lo07-card-add');
        var buttonLabel = document.getElementById('lo07-button-add-label');
        buttonLabel.innerHTML = 'Ajouter';
        var cardTitle = addCard.getElementsByClassName('lo07-card-title')[0];
        cardTitle.innerHTML = "Ajouter un reglement";
        var form = $('#lo07-form-add')[0];
        form.setAttribute('action', form.getAttribute('action').replace(/=edit/, '=add'))
        updateInput(form.id, '');
        updateInput(form.nom, '');
        form.id.removeAttribute('readonly');
        return false;
      };


      var selectFile = function() {
        this.parentElement.getElementsByTagName('input')[0].click();
      };

      $('.lo07-js-file').each(function(id, element) {
        element.addEventListener('click', selectFile);
        var input = this.form.csv_import;
        input.addEventListener('change', function() {
          this.form.submit();
          input.form.reset();
        });
      });

      // Dès que l'import est terminé
      var importResponse = function(response, error) {
        if (error) {
          swal("Oups...", error, "error");
          console.error(error);
        }
        else {
          swal("Règlement importé !", 'Le règlement a été importé avec succès', "success");
          refreshList();
        }
      };


      refreshList();
    </script>

HTML;

}
