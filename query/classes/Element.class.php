<?php

require_once '../myPDO.include.php';

class Element {
  private $id = null;
  
  private $sigle = null;
  
  private $categorie = null;
  
  private $affectation = null;

  private $utt = null;


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
    throw new Exception("Cet élément de formation n'existe pas");
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
  
  public function getSigle() {
    return $this->sigle;
  }
  
  public function getCategorie() {
    return $this->categorie;
  }
  
  public function getAffectation() {
    return $this->affectation;
  }

  public function getUtt() {
    return $this->utt;
  }

  
  public function setSigle($sigle) {
    $this->set('sigle', $sigle);
  }
  
  public function setCategorie($categorie) {
    $this->set('categorie', $categorie);
  }
  
  public function setAffectation($affectation) {
    $this->set('affectation', $affectation);
  }
  
  public function setUtt($utt) {
    $this->set('utt', $utt);
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
  }

  public static function createElement($sigle, $categorie, $affectation, $utt) {
    global $pdo;
    $class = __CLASS__;
    $stmt = $pdo->prepare(<<<SQL
      INSERT INTO {$class} (sigle, categorie, affectation, utt)
      VALUES (:sigle, :categorie, :affectation, :utt)
SQL
    );
    $stmt->execute(array(
      "sigle" => $sigle,
      "categorie" => $categorie,
      "affectation" => $affectation,
      "utt" => $utt
    ));
    return self::createFromID($pdo->lastInsertId());
  }
  
  public static function getAll() {
    $class = __CLASS__;
    $stmt = myPDO::getInstance()->prepare(<<<SQL
      SELECT *
      FROM {$class}
      ORDER BY sigle
SQL
    );
    $stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
    $stmt->execute();
    return $stmt->fetchAll();
  }


}
