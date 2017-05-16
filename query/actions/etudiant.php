<?php

require_once '../classes/Etudiant.class.php';
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
    $etudiants = Etudiant::getAll();
    $etudiantsExport = array();
    foreach ($etudiants as $key => $etudiant) {
      array_push($etudiantsExport, $etudiant->export());
    }
    $result['response'] = $etudiantsExport;
  }

  else if ($action == 'add') {
    if (requireParams('numero', 'nom', 'prenom', 'admission', 'filiere')) {
      if (Etudiant::exists($_POST['numero'])) $result['error'] = "Ce numéro est déjà associé à un étudiant";
      else {
        $result['response'] = Etudiant::createEtudiant($_POST['numero'], $_POST['nom'], $_POST['prenom'], $_POST['admission'], $_POST['filiere'])->export();
      }
    }
    else {
      $result['error'] = "Merci de compléter tous les champs non-optionnels";
    }
  }

  else if ($action == 'delete') {
    $etudiant = Etudiant::createFromID($_POST['numero']);
    $etudiant->delete();
  }
}
catch (Exception $e) {
  $result['error'] = $e->getMessage();
}

echo json_encode($result);
