<?php

require_once '../myPDO.include.php';
require_once '../classes/Cursus_Element.class.php';

class Element {
  private $id = null;
  
  private $sigle = null;
  
  private $categorie = null;
  
  private $affectation = null;

  private $utt = null;

  private static $dependencies = array(
    "Cursus_Element" => "id_element"
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
    throw new Exception("Cet élément de formation n'existe pas");
  }

  public static function createFromSigle($sigle) {
    global $pdo;
    $class = __CLASS__;
    $stmt = $pdo->prepare(<<<SQL
      SELECT *
      FROM {$class}
      WHERE sigle = :sigle
SQL
    );
    $stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
    $stmt->execute(array(
      'sigle' => $sigle
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

  public static function existsFromSigle($sigle) {
    global $pdo;
    $class = __CLASS__;
    $stmt = $pdo->prepare(<<<SQL
      SELECT id
      FROM {$class}
      WHERE sigle = :sigle
SQL
    );
    $stmt->execute(array(
      'sigle' => $sigle
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
    $this->set('categorie', strtoupper($categorie));
  }
  
  public function setAffectation($affectation) {
    $this->set('affectation', strtoupper($affectation));
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

  public static function createElement($sigle, $categorie, $affectation, $utt) {
    $categorie = strtoupper($categorie);
    $affectation = strtoupper($affectation);
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

  public static function search($q) {
    $qArray = explode(' ', $q);
    $qSQL = '';
    foreach ($qArray as $key => $word) {
      $qArray['param' . $key] = '%' . $qArray[$key] . '%';
      $qSQL .= <<<SQL
        AND (LOWER(sigle) LIKE LOWER(:param{$key}))
SQL;
      unset($qArray[$key]);
    }
    $qSQL = preg_replace('/AND /', '', $qSQL);
    $class = __CLASS__;
    $stmt = myPDO::getInstance()->prepare(<<<SQL
      SELECT *
      FROM {$class}
      WHERE {$qSQL}
      ORDER BY sigle
      LIMIT 10;
SQL
    );
    $stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
    $stmt->execute($qArray);
    return $stmt->fetchAll();
  }


}
