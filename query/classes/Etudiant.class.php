<?php

require_once '../myPDO.include.php';
require_once '../classes/Cursus.class.php';

class Etudiant {
	private $numero = null;
	
	private $nom = null;
	
	private $prenom = null;
	
	private $admission = null;

	private $filiere = null;

	private static $dependencies = array(
		"Cursus" => "numero_etudiant"
	);

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
		global $pdo, $db_prefix;
		$class = __CLASS__;
  	$stmt = $pdo->prepare(<<<SQL
  		SELECT *
  		FROM {$db_prefix}{$class}
			WHERE numero = :numero
SQL
  	);
  	$stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
  	$stmt->execute(array(
  		'numero' => $numero
  	));
		if (($object = $stmt->fetch()) !== false) {
    	return $object;
  	}
  	throw new Exception("Cet étudiant n'existe pas");
 	}

 	public static function exists($numero) {
 		global $pdo, $db_prefix;
 		$class = __CLASS__;
 		$stmt = $pdo->prepare(<<<SQL
 			SELECT numero
 			FROM {$db_prefix}{$class}
 			WHERE numero = :numero
SQL
		);
  	$stmt->execute(array(
  		'numero' => $numero
  	));
  	if ($stmt->fetch()) return true;
  	else return false;
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
		return $this->admission;
	}

	public function getFiliere() {
		return $this->filiere;
	}

	public function getCursus() {
    global $pdo, $db_prefix;
		$stmt = $pdo->prepare(<<<SQL
      SELECT *
      FROM cursus
      WHERE numero_etudiant = :numero
      ORDER BY nom, id
SQL
    );
    $stmt->setFetchMode(PDO::FETCH_CLASS, 'Cursus');
    $stmt->execute(array(
    	'numero' => $this->numero
    ));
    return $stmt->fetchAll();
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
		global $pdo, $db_prefix;
		$class = __CLASS__;
		$stmt = $pdo->prepare(<<<SQL
			UPDATE {$db_prefix}{$class} SET {$attr} = :value WHERE numero = :numero
SQL
);
		$stmt->execute(array(
			"value" => $value,
			"numero" => $this->numero
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
			DELETE FROM {$db_prefix}{$class} WHERE numero = :numero
SQL
);
		$stmt->execute(array(
			"numero" => $this->numero
		));
	}
	
	public function deleteDependencies() {
    global $pdo, $db_prefix;
		foreach (self::$dependencies as $class => $attr) {
			$stmt = $pdo->prepare(<<<SQL
	      SELECT *
	      FROM {$db_prefix}{$class}
	      WHERE {$attr} = :numero
SQL
	    );
	    $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
	    $stmt->execute(array(
	    	"numero" => $this->numero
	    ));
	    $objects = $stmt->fetchAll();
	    foreach ($objects as $key => $obj) {
	    	$obj->delete();
	    }
	  }
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
		global $pdo, $db_prefix;
		$class = __CLASS__;
		$stmt = $pdo->prepare(<<<SQL
			INSERT INTO {$db_prefix}{$class} (numero, nom, prenom, admission, filiere)
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
		return self::createFromID($numero);
	}
	
	/** 
	 * getAll
	 *
	 * Retourne la totalité des Etudiants
	 * 
	 * @return array Tableau de Etudiant
	 */
	public static function getAll() {
    global $pdo, $db_prefix;
		$class = __CLASS__;
		$stmt = $pdo->prepare(<<<SQL
      SELECT *
      FROM {$db_prefix}{$class}
      ORDER BY numero
SQL
    );
    $stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
    $stmt->execute();
    return $stmt->fetchAll();
	}

	public static function search($q) {
    global $pdo, $db_prefix;
		$qArray = explode(' ', $q);
		$qSQL = '';
		foreach ($qArray as $key => $word) {
			$qArray['param' . $key] = '%' . $qArray[$key] . '%';
			$qSQL .= <<<SQL
				AND (numero LIKE :param{$key} OR LOWER(prenom) LIKE LOWER(:param{$key}) OR LOWER(nom) LIKE LOWER(:param{$key}))
SQL;
			unset($qArray[$key]);
		}
		$qSQL = preg_replace('/AND /', '', $qSQL);
		$class = __CLASS__;
		$stmt = $pdo->prepare(<<<SQL
      SELECT *
      FROM {$db_prefix}{$class}
      WHERE {$qSQL}
      ORDER BY numero
      LIMIT 10;
SQL
    );
    $stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
    $stmt->execute($qArray);
    return $stmt->fetchAll();
	}


}
