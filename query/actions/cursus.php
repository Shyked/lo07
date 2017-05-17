<?php

require_once '../classes/Etudiant.class.php';
require_once '../classes/Cursus.class.php';
header('Content-Type: text/json');

function requireParams() {
  $arguments = func_get_args();
  $areAllSet = true;
  foreach ($arguments as $key => $param) {
    if (empty($_POST[$param])) $areAllSet = false;
  }
  return $areAllSet;
}


$action = $_GET['action'];
$result = array(
  'response' => null,
  'error' => null
);

try {
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
catch (Exception $e) {
  $result['error'] = $e->getMessage();
}

echo json_encode($result);
