<?php

require_once "../classes/Components.class.php";
require_once '../classes/Cursus.class.php';
require_once '../classes/Etudiant.class.php';



$cursus = null;

if (isset($_GET['id'])) {
  try {
    $cursus = Cursus::createFromID($_GET['id']);
    $etudiant = Etudiant::createFromID($cursus->getNumeroEtudiant());
  }
  catch(Exception $e) {}
}


if ($cursus) {


  $inputId = Components::hidden(array(
    "name" => "id"
  ));
  $inputElementSearch = Components::textWithIcon(array(
    "name" => "element_search",
    "label" => "Rechercher un élément",
    "icon" => 'search'
  ));
  $inputIdElement = Components::select(array(
    "name" => "id_element",
    "label" => "Élément de formation",
    "list" => array(),
    "fullwidth" => true
  ));
  $inputSemSeq = Components::number(array(
    "name" => "sem_seq",
    "label" => "N° de semestre (1, 2...)"
  ));
  $inputSemLabel = Components::text(array(
    "name" => "sem_label",
    "label" => "Nom du semestre (TC2, ISI1...)"
  ));
  $inputProfil = Components::checkbox(array(
    "name" => "profil",
    "label" => "Hors profil",
    "checked" => false
  ));
  $inputCredit = Components::number(array(
    "name" => "credit",
    "label" => "Crédits"
  ));
  $inputResultat = Components::select(array(
    "name" => "resultat",
    "label" => "Résultat",
    "list" => array(
      'A',
      'B',
      'C',
      'D',
      'E',
      'F',
      'ABS',
      'RES',
      'ADM'
    )
  ));


  echo <<<HTML

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--8-col">
      <div class="lo07-card-title">
        <a href="?display=cursus" class="material-icons lo07-button-previous">arrow_back</a>{$etudiant->getPrenom()} {$etudiant->getNom()} - {$cursus->getNom()}
      </div>

      <div class="lo07-card-body">
        <table class="lo07-list" id="lo07-cursus_element">

        </table>
        <button id="lo07-export-csv" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored lo07-button lo07-button-submit"><span>Exporter CSV</span></button>
      </div>
    </div>

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--4-col mdl-cell--top lo07-card-add" id="lo07-cursus_element-card">
      <div class="lo07-card-title">
        Ajouter au cursus
      </div>

      <div class="lo07-card-body">
        <form id="lo07-form-add" method='post' action='query/actions/cursus.php?id={$cursus->getId()}&action=add' data-onresponse="formResponse" onsubmit="return false;">
          {$inputId}
          <div>{$inputElementSearch}</div>
          <div>{$inputIdElement}</div>
          <div>{$inputSemSeq}</div>
          <div>{$inputSemLabel}</div>
          <div class="lo07-checkbox-block">{$inputProfil}</div>
          <div>{$inputCredit}</div>
          <div>{$inputResultat}</div>
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
          if (/=add/.test(this.getAttribute('action'))) swal("Ajouté !", "Élément de cursus ajouté avec succès", "success");
          else if (/=edit/.test(this.getAttribute('action'))) swal("Informations modifiées !", "Les informations de cet élément de cursus ont été modifiées avec succès", "success");
          refreshList();
          resetForm();
        }
      };

      var refreshList = function() {
        $.ajax("./query/actions/cursus.php?id={$cursus->getId()}&action=get", {
          dataType: "json",
          success: function(result) {
            if (result.error) {
              console.error(result.error);
            }
            else {
              var cursusElements = result.response;
              var cursusElementTable = $('#lo07-cursus_element')[0];
              cursusElementTable.innerHTML = '';
              var sem_seq = null;
              var sem_labels = [];
              var rowBuffer = [];
              for (var id in cursusElements) {
                if (sem_seq != cursusElements[id].sem_seq) {
                  if (sem_seq !== null) cursusElementTable.appendChild(getSemRow(sem_seq, sem_labels.join(' - ')));
                  for (var idR in rowBuffer) {
                    cursusElementTable.appendChild(rowBuffer[idR]);
                  }
                  rowBuffer = [];
                  sem_seq = cursusElements[id].sem_seq;
                  sem_labels = [];
                }
                if (sem_labels.indexOf(cursusElements[id].sem_label) == -1) sem_labels.push(cursusElements[id].sem_label);
                var tr = getCursusElementRow(
                  cursusElements[id].id,
                  cursusElements[id].sem_seq,
                  cursusElements[id].sem_label,
                  cursusElements[id].element.sigle,
                  cursusElements[id].element.categorie,
                  cursusElements[id].element.affectation,
                  cursusElements[id].element.utt,
                  cursusElements[id].profil,
                  cursusElements[id].credit,
                  cursusElements[id].resultat
                );
                rowBuffer.push(tr);
              }
              if (sem_seq !== null) cursusElementTable.appendChild(getSemRow(sem_seq, sem_labels.join(' - ')));
              for (var idR in rowBuffer) {
                cursusElementTable.appendChild(rowBuffer[idR]);
              }
            }
          },
          error: function(res) {
            swal("Oups...", "Une erreur est survenue lors de la récupération de la liste...", "error");
          }
        });
      };

      var getSemRow = function(sem_seq, sem_label) {
        var tr = document.createElement('tr');
        tr.innerHTML = '\
          <td colspan="7"><span class="lo07-sem-label">' + sem_label + '</span> <span class="lo07-number-container"><span>' + sem_seq + '</span></span></td>\
        ';
        return tr;
      };

      var getCursusElementRow = function(id, sem_seq, sem_label, sigle, categorie, affectation, utt, profil, credit, resultat) {
        var tr = document.createElement('tr');
        var color = getColorFromString(categorie);
        tr.innerHTML = '\
          <td></td>\
          <td class="lo07-list-icon"><i class="material-icons" style="color: ' + color + ';">fiber_manual_record</i></td>\
          <td class="lo07-list-primary">'
            + sigle
            + (utt && utt !== "0" ? '' : ' <span class="lo07-tooltip-container"><div class="material-icons lo07-text-icon" style="color: #888;" id="lo07-utt-' + id + '">directions_car</div><div class="mdl-tooltip">Se suit hors UTT</div></span>')
            + '<div class="lo07-caption">' + resultat +  ' : ' + credit + ' crédit' + ((credit > 1) ? 's' : '') + '</div>'
          + '</td>\
          <td>' + categorie + '</td>\
          <td>' + affectation + (profil && profil != 0 ? '' : ' <div class="lo07-caption">Hors profil</div>') + '</td>\
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
          $.ajax("./query/actions/cursus.php?id={$cursus->getId()}&action=delete", {
            type: 'post',
            data: 'id=' + id,
            dataType: "json",
            success: function(result) {
              if (result.error) {
                this.error();
              }
              else {
                var cursusElEl = document.getElementById(('lo07-cursus_element-' + id));
                if (cursusElEl) {
                  cursusElEl.parentElement.removeChild(cursusElEl);
                }
                refreshList();
                swal("Supprimé !", "L'élément de cursus a bien été supprimé de la liste.", "success");
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
        $.ajax("./query/actions/cursus.php?id={$cursus->getId()}&action=get", {
          type: 'post',
          data: 'id=' + id,
          dataType: "json",
          success: function(result) {
            if (result.error) {
              this.error();
            }
            else {
              var addCard = $('#lo07-cursus_element-card')[0];
              addCard.classList.add('lo07-card-edit');
              addCard.classList.remove('lo07-card-add');
              var buttonLabel = $('#lo07-button-add-label');
              buttonLabel.html('Modifier');
              var cardTitle = $('#lo07-cursus_element-card .lo07-card-title');
              cardTitle.html("Modifier un élément");
              var form = $('#lo07-form-add')[0];
              form.setAttribute('action', form.getAttribute('action').replace(/=add/, '=edit'));
              var cursus_element = result.response;
              updateInput(form.id, cursus_element.id);
              updateInput(form.element_search, '');
              updateInput(form.id_element, cursus_element.id_element, cursus_element.element.sigle);
              updateInput(form.sem_seq, cursus_element.sem_seq);
              updateInput(form.sem_label, cursus_element.sem_label);
              if (cursus_element.profil && cursus_element.profil !== "0") form.profil.parentElement.MaterialCheckbox.uncheck();
              else form.profil.parentElement.MaterialCheckbox.check();
              updateInput(form.credit, cursus_element.credit);
              updateInput(form.resultat, cursus_element.resultat);
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
        var addCard = $('#lo07-cursus_element-card')[0];
        addCard.classList.remove('lo07-card-edit');
        addCard.classList.add('lo07-card-add');
        var buttonLabel = document.getElementById('lo07-button-add-label');
        buttonLabel.innerHTML = 'Ajouter';
        var cardTitle = addCard.getElementsByClassName('lo07-card-title')[0];
        cardTitle.innerHTML = "Ajouter un élément";
        var form = $('#lo07-form-add')[0];
        form.setAttribute('action', form.getAttribute('action').replace(/=edit/, '=add'));
        updateInput(form.id, '');
        updateInput(form.element_search, '');
        updateInput(form.id_element, '');
        updateInput(form.sem_seq, '');
        updateInput(form.sem_label, '');
        form.profil.parentElement.MaterialCheckbox.uncheck();
        updateInput(form.credit, '');
        updateInput(form.resultat, '');
        form.id.removeAttribute('readonly');
        return false;
      };

      var fillSelect = function() {
        var form = $('#lo07-form-add')[0];
        var search = form.element_search.value;
        $.ajax("./query/actions/element.php?action=search", {
          type: 'post',
          data: 'q=' + search,
          dataType: "json",
          success: function(result) {
            if (result.error) {
              this.error();
            }
            else {
              var list = {};
              for (var id in result.response) {
                list[result.response[id].id] = result.response[id].sigle;
              }
              updateSelect(form.id_element, list, search.length > 0);
            }
          },
          error: function(res) {
            swal("Oups...", "Une erreur est survenue !", "error");
          }
        });
      };


      $("#lo07-export-csv").click(function(e) {
        document.location.href = "./query/actions/cursus.php?id={$cursus->getId()}&action=export";
      });


      refreshList();

      $('#lo07-form-add')[0].element_search.addEventListener('change', fillSelect);
      $('#lo07-form-add')[0].element_search.addEventListener('keyup', fillSelect);
      fillSelect();
    </script>

HTML;













}
else {












  $inputId = Components::hidden(array(
    "name" => "id"
  ));
  $inputEtuSearch = Components::textWithIcon(array(
    "name" => "etu_search",
    "label" => "Rechercher un étudiant",
    "icon" => 'search'
  ));
  $inputNumEtu = Components::select(array(
    "name" => "numero_etudiant",
    "label" => "Numéro étudiant",
    "list" => array(),
    "fullwidth" => true
  ));
  $inputNomCursus = Components::text(array(
    "name" => "nom",
    "label" => "Nom du cursus"
  ));




  echo <<<HTML

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--8-col">
      <div class="lo07-card-title">
        Liste des cursus
      </div>

      <div class="lo07-card-body">
        <table class="lo07-list" id="lo07-cursus">

        </table>
        <form action='query/actions/cursus.php?action=import' method="post" data-onresponse="importResponse" enctype="multipart/form-data" onsubmit="return false;">
          <input type="file" id="lo07-file" class="lo07-file" name="csv_import" />
          <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored lo07-button lo07-button-submit lo07-js-file"><span>Importer CSV</span></button>
        </form>
      </div>
    </div>

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--4-col mdl-cell--top lo07-card-add" id="lo07-cursus-card">
      <div class="lo07-card-title">
        Ajouter un cursus
      </div>

      <div class="lo07-card-body">
        <form id="lo07-form-add" method='post' action='query/actions/cursus.php?action=add' data-onresponse="formResponse" onsubmit="return false;">
          {$inputId}
          <div>{$inputEtuSearch}</div>
          <div>{$inputNumEtu}</div>
          <div>{$inputNomCursus}</div>
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

      var importResponse = function(response, error) {
        if (error) {
          console.error(error);
        }
        else {
          refreshList();
        }
      };


      refreshList();

      $('#lo07-form-add')[0].etu_search.addEventListener('change', fillSelect);
      $('#lo07-form-add')[0].etu_search.addEventListener('keyup', fillSelect);
      fillSelect();
    </script>

HTML;

}
