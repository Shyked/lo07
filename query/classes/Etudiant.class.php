<?php

require_once '../myPDO.include.php';

class Etudiant {
	private $numero = null;
	
	private $nom = null;
	
	private $prenom = null;
	
	private $admission = null;

	private $filiere = null;

	/** 
	 * createFromID
	 *
	 * Instancie un Etudiant à partir de son ID
	 *
	 * @param int $numero L'identifiant dans la base de données
	 * 
	 * @return Etudiant L'étudiant à qui appartient l'ID renseigné
	 */
	public static function createFromID($numero) {
  	$stmt = myPDO::getInstance()->prepare(<<<SQL
  		SELECT *
  		FROM etudiant
			WHERE numero = ?
SQL
  	);
  	$stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
  	$stmt->bindValue(1, $numero);
  	$stmt->execute();
		if (($object = $stmt->fetch()) !== false) {
    	return $object;
  	}
  	throw new Exception(__CLASS__ . ' not found');
 	}

	public function getNumero() {
		return $this->numero;
	}
	
	public function getPrenom() {
		return $this->prenom;
	}
	
	public function getNom() {
		return $this->nom;
	}
	
	public function getAdmission() {
		return $this->admision;
	}

	public function getFiliere() {
		return $this->filiere;
	}

	
	public function setNom($nom) {
		$this->set('nom', $nom);
	}
	
	public function setPrenom($prenom) {
		$this->set('prenom', $prenom);
	}
	
	public function setAdmission($admission) {
		$this->set('admission', $admission);
	}
	
	public function setFiliere($filiere) {
		$this->set('filiere', $filiere);
	}

	private function set($attr, $value) {
		global $pdo;
		$class = __CLASS__;
		$stmt = $pdo->prepare(<<<SQL
			UPDATE {$class} SET :attr = :value WHERE numero = :numero
SQL
);
		$stmt->execute(array(
			"attr" => $attr
			"value" => $value,
			"numero" => $this->numero
		));
		$this->{$attr} = $value;
	}


	/** 
	 * createEtudiant
	 *
	 * Insert un Etudiant dans la base de donnée
	 *
	 * @param string $numero Le numéro de sa carte etu
	 * @param string $nom Son nom
	 * @param string $prenom Son prénom
	 * @param string $admission
	 * @param string $filiere
	 * 
	 * @return Etudiant Une instance de l'Etudiant insérée
	 */
	public static function createEtudiant($numero, $nom, $prenom, $admission, $filiere) {
		global $pdo;
		$stmt = $pdo->prepare(<<<SQL
			INSERT INTO Etudiant (numero, nom, prenom, admission, filiere)
			VALUES (:numero, :nom, :prenom, :admission, :filiere)
SQL
		);
		$stmt->execute(array(
		  "numero" => $numero,
		  "prenom" => $prenom,
		  "nom" => $nom,
		  "admission" => $admission,
		  "filiere" => $filiere
		));
		return self::createFromID($pdo->lastInsertId());
	}
	
	/** 
	 * getAll
	 *
	 * Retourne la totalité des Etudiants
	 * 
	 * @return array Tableau de Etudiant
	 */
	public static function getAll() {
		$stmt = myPDO::getInstance()->prepare(<<<SQL
      SELECT *
      FROM Etudiant
      ORDER BY numero
SQL
    );
    $stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
    $stmt->execute();
    return $stmt->fetchAll();
	}


}
