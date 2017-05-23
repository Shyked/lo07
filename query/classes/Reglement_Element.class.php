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
    throw new Exception("Cette règle n'existe pas");
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
                  $elementArray['element']['affectation'] == $this->affectation
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
      /*if ($credits < $this->credit) {
        return array(
          'valid' => false,
          'msg' => "Il vous manque " . ($this->credit - $credits) . " crédits sur " . $this->credit . " " . (in_array('ALL', $categories) ? "en tout" : "de " . implode("+", $categories) . " en " . $this->affectation) . ($utt ? " à l'UTT" : "") . "."
        );
      }
      else {
        return array(
          'valid' => true,
          'msg' => "Vous avez " . ($credits) . " crédits sur " . $this->credit . " " . (in_array('ALL', $categories) ? "en tout" : "de " . implode("+", $categories) . " en " . $this->affectation) . ($utt ? " à l'UTT" : "") . "."
        );
      }*/
    }
    else throw Exception("Agregat {$this->agregat} inconnu pour {$this->id_regle}");
  }

  public static function createReglementElement($id_reglement, $id_regle, $agregat, $categorie, $affectation, $credit) {
    $agregat = strtoupper($agregat);
    $categorie = strtoupper($categorie);
    $affectation = strtoupper($affectation);
    global $pdo;
    $class = __CLASS__;
    $stmt = $pdo->prepare(<<<SQL
      INSERT INTO {$class} (id_reglement, id_regle, agregat, categorie, affectation, credit)
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
    $class = __CLASS__;
    $where = $id_reglement ? 'WHERE id_reglement = :id_reglement' : '';
    $stmt = myPDO::getInstance()->prepare(<<<SQL
      SELECT *
      FROM {$class}
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
