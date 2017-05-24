<?php

/* Script regroupant les actions effectuée par le serveur (backend) pour les cursus */

require_once '../classes/Etudiant.class.php';
require_once '../classes/Cursus.class.php';
require_once '../classes/Element.class.php';
require_once '../classes/Cursus_Element.class.php';
header('Content-Type: text/json'); // La réponse sera toujours en JSON

// Fonction pour raccourcir les
//   if (isset($_POST['a']) && isset($_POST['b']))
// en
//   requireParams('a', 'b')
function requireParams() {
  $arguments = func_get_args();
  $areAllSet = true;
  foreach ($arguments as $key => $param) {
    if (!isset($_POST[$param]) || $_POST[$param] == '') $areAllSet = false;
  }
  return $areAllSet;
}


$action = $_GET['action'];
$result = array(
  'response' => null,
  'error' => null
);


try {
  // Si on passe un paramètre 'id' en GET, alors c'est que la requête concerne les éléments d'un cursus
  // Sinon, c'est qu'on effectue la requête sur un ou des cursus
  if (isset($_GET['id'])) {

    $cursus = Cursus::createFromID($_GET['id']);

    // Si une checkbox n'est pas check, elle ne retourne rien du tout. Cette ligne défini une variable booléenne en fonction de ça
    $checkboxProfil = isset($_POST['profil']) ? true : false;

    // Si on demande un 'get' pour le cursus, on retourne un/les élément(s)
    if ($action == 'get') {
      // Si on passe en plus un paramètre 'id' en POST, il correspond à l'id de l'élément du cursus, on ne retourne donc qu'un élément
      // Utile pour la récupération des données pour l'édition d'un élément
      if (!empty($_POST['id'])) {
        $cursus_element = Cursus_Element::createFromID($_POST['id']);
        $element = Element::createFromID($cursus_element->getIdElement());
        $cursus_elementExport = $cursus_element->export();
        $cursus_elementExport['element'] = $element->export();
        $result['response'] = $cursus_elementExport;
      }
      // Sinon, on retourne la liste de tous les éléments
      // Utile pour l'affichage de la liste
      else {
        $cursus_elements = Cursus_Element::getAll($cursus->getId());
        $cursus_elementsExport = array();
        foreach ($cursus_elements as $key => $cursus_element) {
          $element = Element::createFromID($cursus_element->getIdElement());
          $cursus_elementExport = $cursus_element->export();
          $cursus_elementExport['element'] = $element->export();
          array_push($cursus_elementsExport, $cursus_elementExport);
        }
        $result['response'] = $cursus_elementsExport;
      }
    }

    // L'action 'add' permet de créer un nouvel élément de Cursus
    else if ($action == 'add') {
      if (requireParams('id_element', 'sem_seq', 'sem_label', 'credit', 'resultat')) {
        $checkboxProfil = !$checkboxProfil;
        $result['response'] = Cursus_Element::createCursusElement($_GET['id'], $_POST['id_element'], $_POST['sem_seq'], $_POST['sem_label'], $checkboxProfil, $_POST['credit'], $_POST['resultat'])->export();
      }
      else {
        $result['error'] = "Merci de compléter tous les champs ci-dessus";
      }
    }

    // L'action 'edit' permet d'éditer un élément du cursus déjà existant
    else if ($action == 'edit') {
      if (requireParams('id', 'id_element', 'sem_seq', 'sem_label', 'credit', 'resultat')) {
        $cursus_element = Cursus_Element::createFromID($_POST['id']);
        $cursus_element->setIdElement($_POST['id_element']);
        $cursus_element->setSemSeq($_POST['sem_seq']);
        $cursus_element->setSemLabel($_POST['sem_label']);
        $cursus_element->setCredit($_POST['credit']);
        $cursus_element->setResultat($_POST['resultat']);
        $result['response'] = $cursus_element->export();
      }
      else {
        $result['error'] = "Merci de compléter tous les champs ci-dessus";
      }
    }

    // L'action 'delete' supprime un élément du cursus
    else if ($action == 'delete') {
      $cursus_element = Cursus_Element::createFromID($_POST['id']);
      $cursus_element->delete();
    }

    // L'action 'export' va générer le fichier CSV du cursus
    else if ($action == 'export') {
      $cursus_elements = Cursus_Element::getAll($cursus->getId());
      $etudiant = Etudiant::createFromID($cursus->getNumeroEtudiant());
      $csvExport = <<<CSV
ID;{$etudiant->getNumero()};;;;;;;;
NO;{$etudiant->getNom()};;;;;;;;
PR;{$etudiant->getPrenom()};;;;;;;;
AD;{$etudiant->getAdmission()};;;;;;;;
FI;{$etudiant->getFiliere()};;;;;;;;
==;s_seq;s_label;sigle;categorie;affectation;utt;profil;credit;resultat

CSV;
      foreach ($cursus_elements as $key => $c_e) {
        $element = Element::createFromID($c_e->getIdElement());
        $utt = $element->getUtt() ? 'Y' : 'N';
        $profil = $c_e->getProfil() ? 'Y' : 'N';
        $csvExport .= "EL;{$c_e->getSemSeq()};{$c_e->getSemLabel()};{$element->getSigle()};{$element->getCategorie()};{$element->getAffectation()};{$utt};{$profil};{$c_e->getCredit()};{$c_e->getResultat()}\n";
      }
      $csvExport .= "END;;;;;;;;;\n";
      header("Content-type: text/csv");
      header("Content-Disposition: attachment; filename={$etudiant->getNom()}_{$etudiant->getPrenom()}.csv");
      header("Pragma: no-cache");
      header("Expires: 0");
      echo $csvExport;
      exit;
    }

    // L'action 'check' va générer les données relative à la vérification de la conformité d'un cursus par rapport à un règlement
    else if ($action == "check") {
      require_once '../classes/Reglement.class.php';
      $reglement = Reglement::createFromID($_POST['reglement']);
      $result['response'] = $reglement->checkCursus($cursus);
    }

    else {
      $result['error'] = "Unknown action";
    }
  }




  else {
    if ($action == 'get') {
      // Récupre les données d'un cursus (id, nom) (notamment pour l'édition des champs)
      if (requireParams('id')) {
        $cursus = Cursus::createFromID($_POST['id']);
        $result['response'] = $cursus->export();
      }
      // Récupère la liste des cursus
      else {
        $etudiants = Etudiant::getAll();
        $cursusGroupByEtu = array();
        foreach ($etudiants as $key => $etudiant) {
          $cursus = $etudiant->getCursus();
          if (count($cursus) > 0) {
            $etudiantArray = $etudiant->export();
            $etudiantArray['cursus'] = array();
            foreach ($cursus as $keyC => $cursusC) {
              array_push($etudiantArray['cursus'], $cursusC->export());
            }
            array_push($cursusGroupByEtu, $etudiantArray);
          }
        }
        $result['response'] = $cursusGroupByEtu;
      }
    }

    // Ajout d'un nouveau cursus
    else if ($action == 'add') {
      if (requireParams('nom', 'numero_etudiant')) {
        $result['response'] = Cursus::createCursus($_POST['nom'], $_POST['numero_etudiant'])->export();
      }
      else {
        $result['error'] = "Merci de compléter tous les champs ci-dessus";
      }
    }

    // Édition du cursus
    else if ($action == 'edit') {
      if (requireParams('id', 'nom', 'numero_etudiant')) {
        $cursus = Cursus::createFromID($_POST['id']);
        $cursus->setNom($_POST['nom']);
        $cursus->setNumeroEtudiant($_POST['numero_etudiant']);
        $result['response'] = $cursus->export();
      }
      else {
        $result['error'] = "Merci de compléter tous les champs ci-dessus";
      }
    }

    // Supression du cursus
    else if ($action == 'delete') {
      $cursus = Cursus::createFromID($_POST['id']);
      $cursus->delete();
    }

    // Importer un fichier CSV pour créer un nouveau cursus
    else if ($action == 'import') {
      if ($_FILES['csv_import']['size'] < 1048576) { // Interdiction des fichiers trop gros (> 1 Mo)
        $csv = file_get_contents($_FILES['csv_import']['tmp_name']); // Récupération du contenu du fichier
        $csvLines = preg_split('/\\r\\n|\\r|\\n/', $csv); // On créé un tableau de ligne en divisant le contenu du fichier à chaque caractère de retour à la ligne
        $etudiantArray = array(
          "numero" => null,
          "nom" => null,
          "prenom" => null,
          "admission" => null,
          "filiere" => null
        );
        $indexes = null;
        $elements = array();
        // Pour chaque lignes du fichier
        foreach ($csvLines as $key => $line) {
          $data = explode(';', $line);
          if (strtoupper($data[0]) == 'ID') $etudiantArray['numero'] = $data[1];         // Numéro de l'étudiant
          else if (strtoupper($data[0]) == 'NO') $etudiantArray['nom'] = $data[1];       // Nom de l'étudiant
          else if (strtoupper($data[0]) == 'PR') $etudiantArray['prenom'] = $data[1];    // Prénom de l'étudiant
          else if (strtoupper($data[0]) == 'AD') $etudiantArray['admission'] = $data[1]; // Admission
          else if (strtoupper($data[0]) == 'FI') $etudiantArray['filiere'] = $data[1];   // Filière
          else if (strtoupper($data[0]) == '==') { // Définision des noms des colonnes
            $indexes = array_flip($data);
          }
          else if (strtoupper($data[0]) == 'EL') { // Élément de formation
            if ($indexes == null) { // Pour savoir quelle colonne correspond à quel champ, il faut déjà avoir parsé la définition des noms de colonne (==)
              $result['error'] = "Merci de définir le nom des colonnes avant de déclarer les éléments";
              break;
            }
            array_push($elements, array(
              'sem_seq' => $data[$indexes['s_seq']],
              'sem_label' => $data[$indexes['s_label']],
              'sigle' => $data[$indexes['sigle']],
              'categorie' => $data[$indexes['categorie']],
              'affectation' => $data[$indexes['affectation']],
              'utt' => $data[$indexes['utt']],
              'profil' => $data[$indexes['profil']],
              'credit' => $data[$indexes['credit']],
              'resultat' => $data[$indexes['resultat']]
            ));
          }
        }
        // Si on a pas rencontré d'erreur dans le parsing
        if ($result['error'] == null) {
          $etudiant = null;
          $alreadyExists = array(); // Contiendra la liste des éléments de formation qui existaient déjà pour avertir qu'ils ont été mis à jour (et non pas dupliqués)
          if (!Etudiant::exists($etudiantArray['numero'])) { // Si l'étudiant spécifié n'existe pas, on le créé
            $etudiant = Etudiant::createEtudiant($etudiantArray['numero'], $etudiantArray['nom'], $etudiantArray['prenom'], $etudiantArray['admission'], $etudiantArray['filiere']);
          }
          else { // Sinon, on le met à jour avec les information fournies
            $etudiant = Etudiant::createFromID($etudiantArray['numero']);
            $etudiant->setNom($etudiantArray['nom']);
            $etudiant->setPrenom($etudiantArray['prenom']);
            $etudiant->setAdmission($etudiantArray['admission']);
            $etudiant->setFiliere($etudiantArray['filiere']);
          }
          $cursus = Cursus::createCursus("Import CSV", $etudiant->getNumero()); // Création d'un nouveau cursus (un nouveau cursus est créé pour chaque import)
          foreach ($elements as $key => $elementArray) { // On parcours tous les éléments qui ont été parsé
            $element = null;
            if (!Element::existsFromSigle($elementArray['sigle'])) { // S'il n'existe pas, on créé l'élément de formation
              $element = Element::createElement($elementArray['sigle'], $elementArray['categorie'], $elementArray['affectation'], strtoupper($elementArray['utt']) == "Y");
            }
            else { // Sinon, on le met à jour
              $element = Element::createFromSigle($elementArray['sigle']);
              $element->setCategorie($elementArray['categorie']);
              $element->setAffectation($elementArray['affectation']);
              $element->setUtt(strtoupper($elementArray['utt']) == "Y");
              array_push($alreadyExists, $element->getSigle());
            }
            // Dans tous les cas, on créé un nouvel élément de cursus pour le cursus qui vient tout juste d'être créé
            Cursus_Element::createCursusElement($cursus->getId(), $element->getId(), $elementArray['sem_seq'], $elementArray['sem_label'], strtoupper($elementArray['profil']) == "Y", $elementArray['credit'], $elementArray['resultat'])->export();
          }
          $result['response'] = "";
          if (count($alreadyExists) > 0) {
            $result['response'] .= "Les éléments de formation suivants existaient déjà et ont été actualisés : " . implode(', ', $alreadyExists);
          }
        }
      }
      else {
        $result['error'] = "Fichier trop lourd (doit être inférieur à 1 Mo)";
      }
    }

    else {
      $result['error'] = "Unknown action";
    }
  }
}
catch (Exception $e) {
  $result['error'] = $e->getMessage();
}

echo json_encode($result);
