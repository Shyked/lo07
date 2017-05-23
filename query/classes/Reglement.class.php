<?php

require_once '../myPDO.include.php';
require_once '../classes/Reglement_Element.class.php';

class Reglement {
  private $id = null;
  
  private $nom = null;

  private static $dependencies = array(
    "Reglement_Element" => "id_reglement"
  );


  public static function createFromID($id) {
    global $pdo;
    $class = __CLASS__;
    $stmt = $pdo->prepare(<<<SQL
      SELECT *
      FROM {$class}
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
    throw new Exception("Ce règlement n'existe pas");
  }

  public static function exists($id) {
    global $pdo;
    $class = __CLASS__;
    $stmt = $pdo->prepare(<<<SQL
      SELECT id
      FROM {$class}
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

  
  public function setNom($nom) {
    $this->set('nom', $nom);
  }
  

  private function set($attr, $value) {
    global $pdo;
    $class = __CLASS__;
    $stmt = $pdo->prepare(<<<SQL
      UPDATE {$class} SET {$attr} = :value WHERE id = :id
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
    global $pdo;
    $this->deleteDependencies();
    $class = __CLASS__;
    $stmt = $pdo->prepare(<<<SQL
      DELETE FROM {$class} WHERE id = :id
SQL
);
    $stmt->execute(array(
      "id" => $this->id
    ));
  }
  
  public function deleteDependencies() {
    foreach (self::$dependencies as $class => $attr) {
      $stmt = myPDO::getInstance()->prepare(<<<SQL
        SELECT *
        FROM {$class}
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

  public function checkCursus($cursus) {
    $cursus_elements = Cursus_Element::getAll($cursus->getId());
    $etudiant = Etudiant::createFromID($cursus->getNumeroEtudiant());
    $elementsArray = array();
    foreach ($cursus_elements as $key => $c_e) {
      $cursus_elementArray = $c_e->export();
      $cursus_elementArray['element'] = Element::createFromID($c_e->getIdElement())->export();
      array_push($elementsArray, $cursus_elementArray);
    }
    $reglement_elements = Reglement_Element::getAll($this->id);
    $results = array();
    foreach ($reglement_elements as $key => $reglement_element) {
      array_push($results, $reglement_element->checkCursus($cursus, $elementsArray));
    }
    return $results;
  }

  public static function createReglement($nom) {
    global $pdo;
    $class = __CLASS__;
    $stmt = $pdo->prepare(<<<SQL
      INSERT INTO {$class} (nom)
      VALUES (:nom)
SQL
    );
    $stmt->execute(array(
      "nom" => $nom
    ));
    return self::createFromID($pdo->lastInsertId());
  }
  
  /** 
   * getAll
   *
   * Retourne la totalité des Reglement
   * 
   * @return array Tableau de Reglement
   */
  public static function getAll() {
    $class = __CLASS__;
    $stmt = myPDO::getInstance()->prepare(<<<SQL
      SELECT *
      FROM {$class}
      ORDER BY nom
SQL
    );
    $stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
    $stmt->execute();
    return $stmt->fetchAll();
  }


}
