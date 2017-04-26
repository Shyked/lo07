<?php

require_once 'myPDO.class.php';




$CONST = array(

 // INFORMATIONS À MODIFIER

 "host"		=> "localhost", // Adresse de la base de données (ex: mysql.example.com)
 "dbname"	=> "lo07",      // Nom de la base de données
 "charset"	=> "utf8",    // Encodage
 "id"		=> "root",        // Identifiant de connexion à la base de données
 "password"	=> ""         // Mot de Passe pour la connexion
 
);



myPDO::setConfiguration("mysql:host={$CONST['host']};dbname={$CONST['dbname']};charset={$CONST['charset']}",$CONST['id'],$CONST['password']);

$pdo = myPDO::getInstance();
