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

    else if ($action == 'import') {
      if ($_FILES['csv_import']['size'] < 1048576) {
        $csv = file_get_contents($_FILES['csv_import']['tmp_name']);
        $csvLines = preg_split('/\\r\\n|\\r|\\n/', $csv);
        $etudiantArray = array(
          "numero" => null,
          "nom" => null,
          "prenom" => null,
          "admission" => null,
          "filiere" => null
        );
        $indexes = null;
        $elements = array();
        foreach ($csvLines as $key => $line) {
          $data = explode(';', $line);
          if (strtoupper($data[0]) == 'ID') $etudiantArray['numero'] = $data[1];
          else if (strtoupper($data[0]) == 'NO') $etudiantArray['nom'] = $data[1];
          else if (strtoupper($data[0]) == 'PR') $etudiantArray['prenom'] = $data[1];
          else if (strtoupper($data[0]) == 'AD') $etudiantArray['admission'] = $data[1];
          else if (strtoupper($data[0]) == 'FI') $etudiantArray['filiere'] = $data[1];
          else if (strtoupper($data[0]) == '==') {
            $indexes = array_flip($data);
          }
          else if (strtoupper($data[0]) == 'EL') {
            if ($indexes == null) {
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
        if ($result['error'] == null) {
          $etudiant = null;
          if (!Etudiant::exists($etudiantArray['numero'])) {
            $etudiant = Etudiant::createEtudiant($etudiantArray['numero'], $etudiantArray['nom'], $etudiantArray['prenom'], $etudiantArray['admission'], $etudiantArray['filiere']);
          }
          else {
            $etudiant = Etudiant::createFromID($etudiantArray['numero']);
          }
          $cursus = Cursus::createCursus("Import CSV", $etudiant->getNumero());
          foreach ($elements as $key => $elementArray) {
            $element = null;
            if (!Element::existsFromSigle($elementArray['sigle'])) {
              $element = Element::createElement($elementArray['sigle'], $elementArray['categorie'], $elementArray['affectation'], strtoupper($elementArray['utt']) == "Y");
            }
            else {
              $element = Element::createFromSigle($elementArray['sigle']);
            }
            Cursus_Element::createCursusElement($cursus->getId(), $element->getId(), $elementArray['sem_seq'], $elementArray['sem_label'], strtoupper($elementArray['profil']) == "Y", $elementArray['credit'], $elementArray['resultat'])->export();
          }
          $result['response'] = "OK";
        }
        else {
          $result['error'] = "Aucun label n'a été définit dans le règlement";
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
