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

  /** 
   * createFromID
   *
   * Instancie un Cursus à partir de son ID
   *
   * @param int $numero L'identifiant dans la base de données
   * 
   * @return Cursus L'étudiant à qui appartient l'ID renseigné
   */
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
    throw new Exception("Ce cursus n'existe pas");
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

  public static function createCursus($nom, $numero_etudiant) {
    global $pdo;
    $class = __CLASS__;
    $stmt = $pdo->prepare(<<<SQL
      INSERT INTO {$class} (nom, numero_etudiant)
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
    $class = __CLASS__;
    $stmt = myPDO::getInstance()->prepare(<<<SQL
      SELECT *
      FROM {$class}
      ORDER BY numero_etudiant, id
SQL
    );
    $stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
    $stmt->execute();
    return $stmt->fetchAll();
  }


}
