<?php

require_once '../classes/Etudiant.class.php';
require_once '../classes/Cursus.class.php';
require_once '../classes/Element.class.php';
require_once '../classes/Cursus_Element.class.php';
header('Content-Type: text/json');

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
  if (isset($_GET['id'])) {

    $cursus = Cursus::createFromID($_GET['id']);

    $checkboxProfil = isset($_POST['profil']) ? true : false;

    if ($action == 'get') {
      if (!empty($_POST['id'])) {
        $cursus_element = Cursus_Element::createFromID($_POST['id']);
        $element = Element::createFromID($cursus_element->getIdElement());
        $cursus_elementExport = $cursus_element->export();
        $cursus_elementExport['element'] = $element->export();
        $result['response'] = $cursus_elementExport;
      }
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

    else if ($action == 'add') {
      if (requireParams('id_element', 'sem_seq', 'sem_label', 'credit', 'resultat')) {
        $checkboxProfil = !$checkboxProfil;
        $result['response'] = Cursus_Element::createCursusElement($_GET['id'], $_POST['id_element'], $_POST['sem_seq'], $_POST['sem_label'], $checkboxProfil, $_POST['credit'], $_POST['resultat'])->export();
      }
      else {
        $result['error'] = "Merci de compléter tous les champs ci-dessus";
      }
    }

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

    else if ($action == 'delete') {
      $cursus_element = Cursus_Element::createFromID($_POST['id']);
      $cursus_element->delete();
    }

    else {
      $result['error'] = "Unknown action";
    }
  }




  else {
    if ($action == 'get') {
      if (requireParams('id')) {
        $cursus = Cursus::createFromID($_POST['id']);
        $result['response'] = $cursus->export();
      }
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

    else if ($action == 'add') {
      if (requireParams('nom', 'numero_etudiant')) {
        $result['response'] = Cursus::createCursus($_POST['nom'], $_POST['numero_etudiant'])->export();
      }
      else {
        $result['error'] = "Merci de compléter tous les champs ci-dessus";
      }
    }

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

    else if ($action == 'delete') {
      $cursus = Cursus::createFromID($_POST['id']);
      $cursus->delete();
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
