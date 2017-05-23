<?php

require_once 'myPDO.class.php';


$CONST = array(

 // INFORMATIONS À MODIFIER

 "host"   => "localhost", // Adresse de la base de données (ex: mysql.example.com)
 "dbname" => "lo07",      // Nom de la base de données
 "charset"  => "utf8",    // Encodage
 "id"   => "root",        // Identifiant de connexion à la base de données
 "password" => ""         // Mot de Passe pour la connexion
 
);



$db_prefix = "";

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/db/id.php')) {
  require_once $_SERVER['DOCUMENT_ROOT'] . '/db/id.php';
  if ($db['dbname']) {
    $db_prefix = $CONST['dbname'] . '_';
  }
  $CONST = array(
   "host"   => $db['host'],
   "dbname" => ($db['dbname']) ? $db['dbname'] : $CONST['dbname'],
   "charset"  => $db['charset'],
   "id"   => $db['id'],
   "password" => $db['password']
  );
}



myPDO::setConfiguration("mysql:host={$CONST['host']};dbname={$CONST['dbname']};charset={$CONST['charset']}",$CONST['id'],$CONST['password']);

unset($CONST);

$pdo = myPDO::getInstance();
