<?php

require_once '../myPDO.include.php';

class Cursus_Element {
  private $id = null;

  private $id_cursus = null;

  private $id_element = null;

  private $sem_seq = null;

  private $sem_label = null;

  private $profil = null;

  private $credit = null;

  private $resultat = null;

  private static $dependencies = array(
  );


  public static function createFromID($id) {
    global $pdo, $db_prefix;
    $class = __CLASS__;
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
    throw new Exception("Cet élément du cursus n'existe pas");
  }

  public static function exists($id) {
    global $pdo, $db_prefix;
    $class = __CLASS__;
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
  
  public function getIdCursus() {
    return $this->id_cursus;
  }

  public function getIdElement() {
    return $this->id_element;
  }

  public function getSemSeq() {
    return $this->sem_seq;
  }

  public function getSemLabel() {
    return $this->sem_label;
  }

  public function getProfil() {
    return $this->profil;
  }

  public function getCredit() {
    return $this->credit;
  }

  public function getResultat() {
    return $this->resultat;
  }

  
  public function setIdCursus($id_cursus) {
    if (!Cursus::exists($id_cursus)) {
      throw new Exception("Ce cursus n'existe pas");
    }
    $this->set('id_cursus', $id_cursus);
  }

  public function setIdElement($id_element) {
    if (!Element::exists($id_element)) {
      throw new Exception("Cet élément de formation n'existe pas");
    }
    $this->set('id_element', $id_element);
  }

  public function setSemSeq($sem_seq) {
    $this->set('sem_seq', $sem_seq);
  }

  public function setSemLabel($sem_label) {
    $this->set('sem_label', $sem_label);
  }

  public function setProfil($profil) {
    $this->set('profil', $profil);
  }

  public function setCredit($credit) {
    $this->set('credit', $credit);
  }

  public function setResultat($resultat) {
    $this->set('resultat', $resultat);
  }

  private function set($attr, $value) {
    global $pdo, $db_prefix;
    $class = __CLASS__;
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
    $class = __CLASS__;
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

  public static function createCursusElement($id_cursus, $id_element, $sem_seq, $sem_label, $profil, $credit, $resultat) {
    global $pdo, $db_prefix;
    $class = __CLASS__;
    $stmt = $pdo->prepare(<<<SQL
      INSERT INTO {$db_prefix}{$class} (id_cursus, id_element, sem_seq, sem_label, profil, credit, resultat)
      VALUES (:id_cursus, :id_element, :sem_seq, :sem_label, :profil, :credit, :resultat)
SQL
    );
    $stmt->execute(array(
      "id_cursus" => $id_cursus,
      "id_element" => $id_element,
      "sem_seq" => $sem_seq,
      "sem_label" => $sem_label,
      "profil" => $profil,
      "credit" => $credit,
      "resultat" => $resultat
    ));
    return self::createFromID($pdo->lastInsertId());
  }
  

  public static function getAll($id_cursus = null) {
    global $pdo, $db_prefix;
    $class = __CLASS__;
    $where = $id_cursus ? 'WHERE id_cursus = :id_cursus' : '';
    $stmt = $pdo->prepare(<<<SQL
      SELECT ce.*
      FROM {$db_prefix}{$class} ce JOIN {$db_prefix}Element e ON (ce.id_element = e.id)
      {$where}
      ORDER BY ce.id_cursus, ce.sem_seq, e.categorie, e.sigle, ce.id_element, ce.id
SQL
    );
    $stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
    if ($id_cursus) $stmt->execute(array(
      "id_cursus" => $id_cursus
    ));
    else $stmt->execute();
    return $stmt->fetchAll();
  }


}
