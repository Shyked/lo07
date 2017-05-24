<?php

require_once '../myPDO.include.php';

class Reglement_Element {
  private $id = null;

  private $id_reglement = null;

  private $id_regle = null;

  private $agregat = null;

  private $categorie = null;

  private $affectation = null;

  private $credit = null;

  private static $dependencies = array(
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
    throw new Exception("Cette règle n'existe pas");
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
  
  public function getIdReglement() {
    return $this->id_reglement;
  }

  public function getIdRegle() {
    return $this->id_regle;
  }

  public function getAgregat() {
    return $this->agregat;
  }

  public function getCategorie() {
    return $this->categorie;
  }

  public function getAffectation() {
    return $this->affectation;
  }

  public function getCredit() {
    return $this->credit;
  }

  
  public function setIdReglement($id_reglement) {
    if (!Cursus::exists($id_reglement)) {
      throw new Exception("Ce règlement n'existe pas");
    }
    $this->set('id_reglement', $id_reglement);
  }

  public function setIdRegle($id_regle) {
    $this->set('id_regle', $id_regle);
  }

  public function setAgregat($agregat) {
    $this->set('agregat', strtoupper($agregat));
  }

  public function setCategorie($categorie) {
    $this->set('categorie', strtoupper($categorie));
  }

  public function setAffectation($affectation) {
    $this->set('affectation', strtoupper($affectation));
  }

  public function setCredit($credit) {
    $this->set('credit', $credit);
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

  public function checkCursus($cursus, $elementsArray) {
    if ($this->agregat == "SUM" || $this->agregat == "EXIST") {
      // Parse
      $utt = false;
      $categorie = array();

      $categoriesStr = $this->categorie;
      if (preg_match('/UTT\(.*\)/', $categoriesStr)) {
        $utt = true;
        $categoriesStr = preg_replace("/UTT\((.*)\)/", "$1", $categoriesStr);
      }
      $categories = explode('+', $categoriesStr);

      // Check
      $credits = 0;
      $exists = false;
      foreach ($elementsArray as $key => $elementArray) {
        if (
            ( // Check categorie
              in_array($elementArray['element']['categorie'], $categories)
              && ( // Check affectation
                $this->affectation == "UTT"
                || $this->affectation == ""
                || (
                  (
                    $elementArray['element']['affectation'] == $this->affectation
                    || $this->affectation == 'BR' && preg_match('/BR$/', $elementArray['element']['affectation'])
                  )
                  && $elementArray['profil']
                )
              )
            )
            || in_array('ALL', $categories)
          ) {
          $credits += $elementArray['credit'];
        $exists = true;
        }
      }
      if ($this->agregat == "SUM") {
        return array(
          "agregat" => $this->agregat,
          "credits" => $credits,
          "creditsNeeded" => $this->credit,
          "categories" => $categories,
          "affectation" => $this->affectation,
          "utt" => $utt
        );
      }
      else if ($this->agregat == "EXIST") {
        return array(
          "agregat" => $this->agregat,
          "exists" => $exists,
          "categories" => $categories,
          "affectation" => $this->affectation,
          "utt" => $utt
        );
      }
    }
    else throw Exception("Agregat {$this->agregat} inconnu pour {$this->id_regle}");
  }

  public static function createReglementElement($id_reglement, $id_regle, $agregat, $categorie, $affectation, $credit) {
    global $pdo, $db_prefix;
    $agregat = strtoupper($agregat);
    $categorie = strtoupper($categorie);
    $affectation = strtoupper($affectation);
    $class = strtolower(__CLASS__);
    $stmt = $pdo->prepare(<<<SQL
      INSERT INTO {$db_prefix}{$class} (id_reglement, id_regle, agregat, categorie, affectation, credit)
      VALUES (:id_reglement, :id_regle, :agregat, :categorie, :affectation, :credit)
SQL
    );
    $stmt->execute(array(
      "id_reglement" => $id_reglement,
      "id_regle" => $id_regle,
      "agregat" => $agregat,
      "categorie" => $categorie,
      "affectation" => $affectation,
      "credit" => $credit
    ));
    return self::createFromID($pdo->lastInsertId());
  }
  

  public static function getAll($id_reglement = null) {
    global $pdo, $db_prefix;
    $class = strtolower(__CLASS__);
    $where = $id_reglement ? 'WHERE id_reglement = :id_reglement' : '';
    $stmt = $pdo->prepare(<<<SQL
      SELECT *
      FROM {$db_prefix}{$class}
      {$where}
      ORDER BY id_reglement, id_regle, id
SQL
    );
    $stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
    if ($id_reglement) $stmt->execute(array(
      "id_reglement" => $id_reglement
    ));
    else $stmt->execute();
    return $stmt->fetchAll();
  }


}
