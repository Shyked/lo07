<?php


/* Génère la page (frontend) de gestion des cursus */


require_once "../classes/Components.class.php";
require_once '../classes/Cursus.class.php';
require_once '../classes/Etudiant.class.php';
require_once '../classes/Reglement.class.php';



// Récupération du cursus si un 'id' est passé en paramètre GET
$cursus = null;
if (isset($_GET['id'])) {
  try {
    $cursus = Cursus::createFromID($_GET['id']);
    $etudiant = Etudiant::createFromID($cursus->getNumeroEtudiant());
  }
  catch(Exception $e) {}
}


// Si un cursus en particulier est demandé, on affiche la page des détails d'un cursus
// Sinon (deuxième partie du fichier) on affiche la liste des cursus
if ($cursus) {


  // Génération du code HTML pour les input
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
    "fullwidth" => true,
    "associative" => true
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
      'ADM',
      'EQU'
    )
  ));


  $reglements = Reglement::getAll();
  $reglementsArray = array();
  foreach($reglements as $key => $reglement) {
    $reglementsArray[$reglement->getId()] = $reglement->getNom();
  }

  $inputReglement = Components::select(array(
    "name" => "reglement",
    "label" => "Règlement",
    "list" => $reglementsArray,
    "associative" => true
  ));


  // Contenu HTML de la page
  echo <<<HTML

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--8-col">
      <div class="lo07-card-title">
        <a href="?display=cursus" class="material-icons lo07-button-previous">arrow_back</a>{$etudiant->getPrenom()} {$etudiant->getNom()} - {$cursus->getNom()}
      </div>

      <div class="lo07-card-body">
        <table class="lo07-list" id="lo07-cursus_element">
          <tr><td class="lo07-text-center"><div class="mdl-spinner mdl-js-spinner is-active"></div></td></tr>
        </table>
        <button id="lo07-export-csv" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored lo07-button lo07-button-submit"><span>Exporter CSV</span></button>
      </div>
    </div>

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--4-col mdl-cell--6-col-tablet mdl-cell--top lo07-card-add" id="lo07-cursus_element-card">
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

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--8-col lo07-card-reglement">
      <div class="lo07-card-title">
        Conformité du cursus
      </div>

      <div class="lo07-card-body">
        <form id="lo07-form-reglement" method='post' action='query/actions/cursus.php?id={$cursus->getId()}&action=check' data-onresponse="formCheckResponse" onsubmit="return false;">
          <div>{$inputReglement}</div>
          <button id="lo07-button-reglement" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored lo07-button lo07-button-submit" onclick="this.form.submit();"><span>Vérifier</span></button>
        </form>
        <div id="lo07-reglement-result">
        </div>
      </div>
    </div>







    <script>
    // Fonction appelée en retour de l'envoi du formulaire pour l'ajout ou l'édition
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

      // Actualiser la zone d'affichage des éléments du cursus
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

      // Récupération du code HTML pour afficher le passage à un nouveau semestre dans la liste
      var getSemRow = function(sem_seq, sem_label) {
        var tr = document.createElement('tr');
        tr.innerHTML = '\
          <td colspan="7"><span class="lo07-sem-label">' + sem_label + '</span> <span class="lo07-number-container"><span>' + sem_seq + '</span></span></td>\
        ';
        return tr;
      };

      // Récupération du code HTML pour une ligne représentant un élément de formation
      var getCursusElementRow = function(id, sem_seq, sem_label, sigle, categorie, affectation, utt, profil, credit, resultat) {
        var tr = document.createElement('tr');
        // Génère une couleur en fonction de la catégorie
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

      // Lorsque l'on clique le bouton de suppression d'une ligne (c'est à dire d'un élément du cursus)
      var deleteObject = function(id) {
        // On demande confirmation pour l'opération
        swal({
          title: "Êtes-vous sûr ?",
          text: "La suppression ne peut être annulée, il vous faudra saisir les informations à nouveau.",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#FF5252",
          confirmButtonText: "Oui, supprimer !",
          closeOnConfirm: false
        },
        // Si oui
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
                // Retrait de l'affichage l'élément dans la liste
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

      // Lors du clic sur le bouton d'édition d'un élément du cursus
      var editObject = function(id) {
        // On récupère les données de l'élément en question
        $.ajax("./query/actions/cursus.php?id={$cursus->getId()}&action=get", {
          type: 'post',
          data: 'id=' + id,
          dataType: "json",
          success: function(result) {
            if (result.error) {
              this.error();
            }
            else {
              // On transforme la carte d'ajout en une carte d'édition
              var addCard = $('#lo07-cursus_element-card')[0];
              addCard.classList.add('lo07-card-edit');
              addCard.classList.remove('lo07-card-add');
              var buttonLabel = $('#lo07-button-add-label');
              buttonLabel.html('Modifier');
              var cardTitle = $('#lo07-cursus_element-card .lo07-card-title');
              cardTitle.html("Modifier un élément");
              var form = $('#lo07-form-add')[0];
              form.setAttribute('action', form.getAttribute('action').replace(/=add/, '=edit'));
              // Puis on complète automatiquement les champs pour que les valeurs correspondent aux données de l'élément à éditer
              var cursus_element = result.response;
              updateInput(form.id, cursus_element.id);
              updateInput(form.element_search, '');
              updateInput(form.id_element_selectLabel, cursus_element.id_element, cursus_element.element.sigle);
              updateInput(form.sem_seq, cursus_element.sem_seq);
              updateInput(form.sem_label, cursus_element.sem_label);
              if (cursus_element.profil && cursus_element.profil !== "0") form.profil.parentElement.MaterialCheckbox.uncheck();
              else form.profil.parentElement.MaterialCheckbox.check();
              updateInput(form.credit, cursus_element.credit);
              updateInput(form.resultat_selectLabel, cursus_element.resultat);
              form.id.setAttribute('readonly', 'readonly');
            }
          },
          error: function(res) {
            refreshList();
            swal("Oups...", "Une erreur est survenue !", "error");
          }
        });
      };

      // Reset le formulaire : Le repasse en mode ajout et vide tous les champs
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
        updateInput(form.id_element_selectLabel, '');
        updateInput(form.sem_seq, '');
        updateInput(form.sem_label, '');
        form.profil.parentElement.MaterialCheckbox.uncheck();
        updateInput(form.credit, '');
        updateInput(form.resultat_selectLabel, '');
        form.id.removeAttribute('readonly');
        return false;
      };

      // Effectue une recherche à partir de l'input element_search pour compléter le select avec le sigle recherché
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
              updateSelect(form, 'id_element', list, search.length > 0);
            }
          },
          error: function(res) {
            swal("Oups...", "Une erreur est survenue !", "error");
          }
        });
      };


      // Lors d'un clic su le bouton d'export CSV, on accède à la "page" d'export qui returne le fichier CSV (ne recharge pas la page courante vu qu'il s'agit d'un fichier)
      $("#lo07-export-csv").click(function(e) {
        document.location.href = "./query/actions/cursus.php?id={$cursus->getId()}&action=export";
      });



      // Validation du règlement
      // Le serveur effectue les vérifications dans un premier temps et retourne une structure de données contenant, par exemple, le nombre de crédits cumulés pour un règle avec le nombre de crédits nécessaire pour la valider.
      // La fonction ci-dessous génère le texte qui correspond à la structure de données retournée
      var formCheckResponse = function(response, error) {
        var valid = []; // Contient la liste des phrases pour les règles validées
        var needed = []; //                                              non validées
        for (var id in response) {
          if (response[id].agregat == "SUM") {
            // SUM valide
            if (response[id].credits >= response[id].creditsNeeded) {
              valid.push("" + response[id].credits + " crédits sur " + response[id].creditsNeeded + " " + (response[id].categories.indexOf('ALL') != -1 ? "en tout" : "de " + response[id].categories.join("+") + " en " + response[id].affectation) + (response[id].utt ? " à l'UTT" : ""));
            }
            // SUM non valide
            else {
              needed.push("" + (response[id].creditsNeeded - response[id].credits) + " crédits sur " + response[id].creditsNeeded + " " + (response[id].categories.indexOf('ALL') != -1 ? "en tout" : "de " + response[id].categories.join("+") + " en " + response[id].affectation) + (response[id].utt ? " à l'UTT" : ""));
            }
          }
          else if (response[id].agregat == "EXIST") {
            // EXIST valide
            if (response[id].exists) {
              valid.push("le " + (response[id].categories.indexOf('ALL') != -1 ? "tout" : response[id].categories.join("+") + " en " + response[id].affectation) + (response[id].utt ? " à l'UTT" : ""));
            }
            // EXIST non valide
            else {
              needed.push("le " + (response[id].categories.indexOf('ALL') != -1 ? "tout" : response[id].categories.join("+") + " en " + response[id].affectation) + (response[id].utt ? " à l'UTT" : ""));
            }
          }
        }
        html = "";
        // Affichage des règles validées
        if (valid.length > 0) {
          html += '<h4 class="lo07-green">{$etudiant->getPrenom()} {$etudiant->getNom()} a validé</h4><ul>';
          for (var id in valid) {
            html += '<li>' + valid[id] + '</li>';
          }
          html += '</ul>'
        }
        // Affichage des règles non validées
        if (needed.length > 0) {
          html += '<h4 class="lo07-red">Il manque</h4><ul>';
          for (var id in needed) {
            html += '<li>' + needed[id] + '</li>';
          }
          html += '</ul>';
        }
        $("#lo07-reglement-result").html(html);
      };


      // On refresh la liste pour l'afficher au chargement de la page
      refreshList();

      // Event listeners pour effectuer une recherche en fonction du sigle entré dans l'input
      $('#lo07-form-add')[0].element_search.addEventListener('change', fillSelect);
      $('#lo07-form-add')[0].element_search.addEventListener('keyup', fillSelect);
      fillSelect();
    </script>

HTML;













} // Page pour l'affichage de la liste des cursus
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
    "fullwidth" => true,
    "associative" => true
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
          <tr><td class="lo07-text-center"><div class="mdl-spinner mdl-js-spinner is-active"></div></td></tr>
        </table>
        <form action='query/actions/cursus.php?action=import' method="post" data-onresponse="importResponse" enctype="multipart/form-data" onsubmit="return false;">
          <input type="file" id="lo07-file" class="lo07-file" name="csv_import" />
          <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored lo07-button lo07-button-submit lo07-js-file"><span>Importer CSV</span></button>
        </form>
      </div>
    </div>

    <div class="mdl-color--white mdl-shadow--2dp mdl-cell mdl-cell--4-col mdl-cell--6-col-tablet mdl-cell--top lo07-card-add" id="lo07-cursus-card">
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
                refreshList();
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
              updateInput(form.numero_etudiant_selectLabel, cursus.numero_etudiant);
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
        updateInput(form.numero_etudiant_selectLabel, '');
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
              updateSelect(form, 'numero_etudiant', list, search.length > 0);
            }
          },
          error: function(res) {
            swal("Oups...", "Une erreur est survenue !", "error");
          }
        });
      };


      // Le style du <input type="file" /> n'est pas customisable
      // Pour le changer, il faut créer un fake button à côté qui, au clic, déclenchera le vrai input file
      var selectFile = function() {
        this.parentElement.getElementsByTagName('input')[0].click();
      };

      // Pour tous les champs pour importer des fichiers...
      $('.lo07-js-file').each(function(id, element) {
        // On relie la fonction du dessus à l'évènement click du bouton
        element.addEventListener('click', selectFile);
        var input = this.form.csv_import;
        // Lorsque le contenu de l'input file a changé (on a délectionné un fichier)
        input.addEventListener('change', function() {
          this.form.submit(); // On l'envoi directement au serveur
          input.form.reset(); // On reset les champs pour que l'évènement "change" soit déclenché à nouveau, même si on sélectionne le même fichier deux fois de suite
        });
      });

      // Dès que l'import est terminé
      var importResponse = function(response, error) {
        if (error) {
          swal("Oups...", error, "error");
          console.error(error);
        }
        else {
          swal("Cursus importé !", response, "success");
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
