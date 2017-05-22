<?php

require_once '../classes/Element.class.php';
header('Content-Type: text/json');

function requireParams() {
  $arguments = func_get_args();
  $areAllSet = true;
  foreach ($arguments as $key => $param) {
    if (!isset($_POST[$param]) || $_POST[$param] == '') $areAllSet = false;
  }
  return $areAllSet;
}

$checkboxUtt = isset($_POST['utt']) ? true : false;


$action = $_GET['action'];
$result = array(
  'response' => null,
  'error' => null
);

try {
  if ($action == 'get') {
    if (!empty($_POST['id'])) {
      $element = Element::createFromID($_POST['id']);
      $result['response'] = $element->export();
    }
    else {
      $elements = Element::getAll();
      $elementsExport = array();
      foreach ($elements as $key => $element) {
        array_push($elementsExport, $element->export());
      }
      $result['response'] = $elementsExport;
    }
  }

  else if ($action == 'search') {
    if (isset($_POST['q'])) {
      $elements = Element::search($_POST['q']);
      $elementsExport = array();
      foreach ($elements as $key => $element) {
        array_push($elementsExport, $element->export());
      }
      $result['response'] = $elementsExport;
    }
    else {
      $result['error'] = "Missing parameter";
    }
  }

  else if ($action == 'add') {
    if (requireParams('sigle', 'categorie', 'affectation')) {
      if (!Element::existsFromSigle($_POST['sigle'])) {
        $result['response'] = Element::createElement($_POST['sigle'], $_POST['categorie'], $_POST['affectation'], $checkboxUtt)->export();
      }
      else {
        $result['error'] = "Un élément de formation portant ce sigle existe déjà";
      }
    }
    else {
      $result['error'] = "Merci de compléter tous les champs ci-dessus";
    }
  }

  else if ($action == 'edit') {
    if (requireParams('id', 'sigle', 'categorie', 'affectation')) {
      $element = Element::createFromID($_POST['id']);
      $element->setSigle($_POST['sigle']);
      $element->setCategorie($_POST['categorie']);
      $element->setAffectation($_POST['affectation']);
      $element->setUtt($checkboxUtt);
      $result['response'] = $element->export();
    }
    else {
      $result['error'] = "Merci de compléter tous les champs ci-dessus";
    }
  }

  else if ($action == 'delete') {
    $element = Element::createFromID($_POST['id']);
    $element->delete();
  }

  else {
    $result['error'] = "Unknown action";
  }
}
catch (Exception $e) {
  $result['error'] = $e->getMessage();
}

echo json_encode($result);
