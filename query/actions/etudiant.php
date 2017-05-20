<?php

require_once '../classes/Etudiant.class.php';
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
  if ($action == 'get') {
    if (!empty($_POST['numero'])) {
      $etudiant = Etudiant::createFromID($_POST['numero']);
      $result['response'] = $etudiant->export();
    }
    else {
      $etudiants = Etudiant::getAll();
      $etudiantsExport = array();
      foreach ($etudiants as $key => $etudiant) {
        array_push($etudiantsExport, $etudiant->export());
      }
      $result['response'] = $etudiantsExport;
    }
  }

  else if ($action == 'search') {
    if (isset($_POST['q'])) {
      $etudiants = Etudiant::search($_POST['q']);
      $etudiantsExport = array();
      foreach ($etudiants as $key => $etudiant) {
        array_push($etudiantsExport, $etudiant->export());
      }
      $result['response'] = $etudiantsExport;
    }
    else {
      $result['error'] = "Missing parameter";
    }
  }

  else if ($action == 'add') {
    if (requireParams('numero', 'nom', 'prenom', 'admission', 'filiere')) {
      if (Etudiant::exists($_POST['numero'])) $result['error'] = "Ce numéro est déjà associé à un étudiant";
      else {
        $result['response'] = Etudiant::createEtudiant($_POST['numero'], $_POST['nom'], $_POST['prenom'], $_POST['admission'], $_POST['filiere'])->export();
      }
    }
    else {
      $result['error'] = "Merci de compléter tous les champs ci-dessus";
    }
  }

  else if ($action == 'edit') {
    if (requireParams('numero', 'nom', 'prenom', 'admission', 'filiere')) {
      $etudiant = Etudiant::createFromID($_POST['numero']);
      $etudiant->setNom($_POST['nom']);
      $etudiant->setPrenom($_POST['prenom']);
      $etudiant->setAdmission($_POST['admission']);
      $etudiant->setFiliere($_POST['filiere']);
      $result['response'] = $etudiant->export();
    }
    else {
      $result['error'] = "Merci de compléter tous les champs ci-dessus";
    }
  }

  else if ($action == 'delete') {
    $etudiant = Etudiant::createFromID($_POST['numero']);
    $etudiant->delete();
  }

  else {
    $result['error'] = "Unknown action";
  }
}
catch (Exception $e) {
  $result['error'] = $e->getMessage();
}

echo json_encode($result);
