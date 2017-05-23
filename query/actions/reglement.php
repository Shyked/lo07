<?php

require_once '../classes/Reglement.class.php';
require_once '../classes/Reglement_Element.class.php';
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

    $reglement = Reglement::createFromID($_GET['id']);
    $affectation = isset($_POST['affectation']) ? $_POST['affectation'] : '';

    if ($action == 'get') {
      if (requireParams('id')) {
        $reglement_element = Reglement_Element::createFromID($_POST['id']);
        $reglement_elementExport = $reglement_element->export();
        $result['response'] = $reglement_elementExport;
      }
      else {
        $reglement_elements = Reglement_Element::getAll($reglement->getId());
        $reglement_elementsExport = array();
        foreach ($reglement_elements as $key => $reglement_element) {
          array_push($reglement_elementsExport, $reglement_element->export());
        }
        $result['response'] = $reglement_elementsExport;
      }
    }

    else if ($action == 'add') {
      if (requireParams('id_regle', 'agregat', 'categorie', 'credit')) {
        $result['response'] = Reglement_Element::createReglementElement($_GET['id'], $_POST['id_regle'], $_POST['agregat'], $_POST['categorie'], $affectation, $_POST['credit'])->export();
      }
      else {
        $result['error'] = "Merci de compléter tous les champs ci-dessus";
      }
    }

    else if ($action == 'edit') {
      if (requireParams('id', 'id_regle', 'agregat', 'categorie', 'credit')) {
        $reglement_element = Reglement_Element::createFromID($_POST['id']);
        $reglement_element->setIdRegle($_POST['id_regle']);
        $reglement_element->setAgregat($_POST['agregat']);
        $reglement_element->setCategorie($_POST['categorie']);
        $reglement_element->setAffectation($affectation);
        $reglement_element->setCredit($_POST['credit']);
        $result['response'] = $reglement_element->export();
      }
      else {
        $result['error'] = "Merci de compléter tous les champs ci-dessus";
      }
    }

    else if ($action == 'delete') {
      $reglement_element = Reglement_Element::createFromID($_POST['id']);
      $reglement_element->delete();
    }

    else if ($action == 'export') {
      $reglement_elements = Reglement_Element::getAll($reglement->getId());
      $csvExport = "LABEL;" . $reglement->getNom() . ";;;\n";
      foreach ($reglement_elements as $key => $r_e) {
        $csvExport .= "{$r_e->getIdRegle()};{$r_e->getAgregat()};{$r_e->getCategorie()};{$r_e->getAffectation()};{$r_e->getCredit()}\n";
      }
      header("Content-type: text/csv");
      header("Content-Disposition: attachment; filename={$reglement->getNom()}.csv");
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
        $reglement = Reglement::createFromID($_POST['id']);
        $result['response'] = $reglement->export();
      }
      else {
        $reglements = Reglement::getAll();
        $reglementsExport = array();
        foreach ($reglements as $key => $reglement) {
          array_push($reglementsExport, $reglement->export());
        }
        $result['response'] = $reglementsExport;
      }
    }

    else if ($action == 'add') {
      if (requireParams('nom')) {
        $result['response'] = Reglement::createReglement($_POST['nom'])->export();
      }
      else {
        $result['error'] = "Merci de compléter tous les champs ci-dessus";
      }
    }

    else if ($action == 'edit') {
      if (requireParams('id', 'nom')) {
        $reglement = Reglement::createFromID($_POST['id']);
        $reglement->setNom($_POST['nom']);
        $result['response'] = $reglement->export();
      }
      else {
        $result['error'] = "Merci de compléter tous les champs ci-dessus";
      }
    }

    else if ($action == 'delete') {
      $reglement = Reglement::createFromID($_POST['id']);
      $reglement->delete();
    }

    else if ($action == 'import') {
      if ($_FILES['csv_import']['size'] < 1048576) {
        $csv = file_get_contents($_FILES['csv_import']['tmp_name']);
        $csvLines = preg_split('/\\r\\n|\\r|\\n/', $csv);
        $label = null;
        $regles = array();
        foreach ($csvLines as $key => $line) {
          $data = explode(';', $line);
          if (strtoupper($data[0]) == 'LABEL') $label = $data[1];
          else if (count($data) > 4) {
            array_push($regles, array(
              'id_regle' => $data[0],
              'agregat' => $data[1],
              'categorie' => $data[2],
              'affectation' => (strtoupper($data[2]) == "ALL") ? "" : $data[3],
              'credit' => (strtoupper($data[2]) == "ALL") ? ($data[4] ? $data[4] : $data[3]) : $data[4]
            ));
          }
        }
        if ($label) {
          $reglement = Reglement::createReglement($label);
          foreach ($regles as $key => $regle) {
            Reglement_Element::createReglementElement($reglement->getId(), $regle['id_regle'], $regle['agregat'], $regle['categorie'], $regle['affectation'], $regle['credit']);
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
