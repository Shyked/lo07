<?php

require_once '../myPDO.include.php';
require_once '../classes/Cursus_Element.class.php';

class Cursus {
  private $id = null;
  
  private $nom = null;
  
  private $numero_etudiant = null;

  private static $dependencies = array(
    "Cursus_Element" => "id_cursus"
  );


  public static function createFromID($id) {
    global $pdo, $db_prefix;
    $class = strtolower(__CLASS__);
    $stmt = $pdo->prepare(<<<SQL
      SELECT *
      FROM {$db_prefix}{$class}
      WHERE id = :id
SQL
    );
    $stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
    $stmt->execute(array(
      'id' => $id
    ));
    if (($object = $stmt->fetch()) !== false) {
      return $object;
    }
    throw new Exception("Ce cursus n'existe pas");
  }

  public static function exists($id) {
    global $pdo, $db_prefix;
    $class = strtolower(__CLASS__);
    $stmt = $pdo->prepare(<<<SQL
      SELECT id
      FROM {$db_prefix}{$class}
      WHERE id = :id
SQL
    );
    $stmt->execute(array(
      'id' => $id
    ));
    if ($stmt->fetch()) return true;
    else return false;
  }

  public function getId() {
    return $this->id;
  }
  
  public function getNom() {
    return $this->nom;
  }
  
  public function getNumeroEtudiant() {
    return $this->numero_etudiant;
  }

  
  public function setNom($nom) {
    $this->set('nom', $nom);
  }
  
  public function setNumeroEtudiant($numero_etudiant) {
    if (!Etudiant::exists($numero_etudiant)) {
      throw new Exception("Cet étudiant n'existe pas");
    }
    $this->set('numero_etudiant', $numero_etudiant);
  }

  private function set($attr, $value) {
    global $pdo, $db_prefix;
    $class = strtolower(__CLASS__);
    $stmt = $pdo->prepare(<<<SQL
      UPDATE {$db_prefix}{$class} SET {$attr} = :value WHERE id = :id
SQL
);
    $stmt->execute(array(
      "value" => $value,
      "id" => $this->id
    ));
    $this->{$attr} = $value;
  }


  public function export() {
    return get_object_vars($this);
  }


  public function delete() {
    global $pdo, $db_prefix;
    $this->deleteDependencies();
    $class = strtolower(__CLASS__);
    $stmt = $pdo->prepare(<<<SQL
      DELETE FROM {$db_prefix}{$class} WHERE id = :id
SQL
);
    $stmt->execute(array(
      "id" => $this->id
    ));
  }
  
  public function deleteDependencies() {
    global $pdo, $db_prefix;
    foreach (self::$dependencies as $class => $attr) {
      $class = strtolower($class);
      $stmt = $pdo->prepare(<<<SQL
        SELECT *
        FROM {$db_prefix}{$class}
        WHERE {$attr} = :id
SQL
      );
      $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
      $stmt->execute(array(
        "id" => $this->id
      ));
      $objects = $stmt->fetchAll();
      foreach ($objects as $key => $obj) {
        $obj->delete();
      }
    }
  }

  public static function createCursus($nom, $numero_etudiant) {
    global $pdo, $db_prefix;
    $class = strtolower(__CLASS__);
    $stmt = $pdo->prepare(<<<SQL
      INSERT INTO {$db_prefix}{$class} (nom, numero_etudiant)
      VALUES (:nom, :numero_etudiant)
SQL
    );
    $stmt->execute(array(
      "nom" => $nom,
      "numero_etudiant" => $numero_etudiant
    ));
    return self::createFromID($pdo->lastInsertId());
  }
  
  /** 
   * getAll
   *
   * Retourne la totalité des Cursus
   * 
   * @return array Tableau de Cursus
   */
  public static function getAll() {
    $class = strtolower(__CLASS__);
    $stmt = $pdo->prepare(<<<SQL
      SELECT *
      FROM {$db_prefix}{$class}
      ORDER BY numero_etudiant, id
SQL
    );
    $stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
    $stmt->execute();
    return $stmt->fetchAll();
  }


}
