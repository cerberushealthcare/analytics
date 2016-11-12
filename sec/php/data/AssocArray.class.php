<?php
/*
	This is an extension of the Database class used for quick and easy creation of an associative array to use in a page.
	Example usage:
	
	$assocArray = new assocArray($query, $vars, false, false, false, 'dbName', 'Mssql');
	Last param must be either Mssql, Mysql or Telnet (because those are the three classes found in Database.class.php)
*/

class AssocArray extends Database {
	public $query,
	$vars,
	$updateOrDelete,
	$specialChars,
	$returnInsertedRow,
	$db,
	$dbObjType;
	//$dbObj;
	
	public function __construct($query, $vars, $updateOrDelete = false, $specialChars = false, $returnInsertedRow = false, $db = '', $dbObjType = 'mysql') {
		$this->query = $query;
		$this->vars = $vars;
		$this->updateOrDelete = $updateOrDelete;
		$this->specialChars = $specialChars;
		$this->returnInsertedRow = $returnInsertedRow;
		$this->db = $db;
		$this->dbObjType = $dbObjType;
	}
	
	//Must be declared here because it's an abstract function from the Database class.
	public function create($dbname) { }
	
	public function getAssoc() {
		try {
			if ($this->dbObjType == 'oracle') {
				$conn = oci_connect($this->db, 'Cin123','208.187.161.81/pdborcl');
				if($conn)
					echo "Connection succeeded";
				else
				{
					echo "Connection failed";
					//$err = oci_error();
					trigger_error(htmlentities($err['message'], ENT_QUOTES), E_USER_ERROR);	
				}
			}
			else {
				$dbObject = new Database(); //Not to be confused with $_CONFIG
				$PDOobj = $dbObject->getObject($this->dbObjType); //expects Database:: object to exist but since we don't have Database.class.php, it can't work.
				echo 'dbo is a ' . gettype($PDOobj) . '<br>';
				//$PDOobj->create($this->db);
				echo 'PDOobj is a ' . gettype($PDOobj);
				$PDO = $PDOobj->connect();
				$stmt = $PDOobj->prepare($this->query); //PDO statement object.
				
				if (!$stmt) {
					$arr = $stmt->errorInfo();
					echo "Prepare failed (" . $arr[0] . ":" . $arr[1] . "): " . $arr[2];
					$stmt->closeCursor();
					unset($PDO);
					return false;
				}
				
				$i = 0;
				if (!is_array($this->vars) && !is_null($this->vars)) {
					throw new RuntimeException('SqltoAssoc: Fatal error: - varArray is not an array nor null.');
				}
				
				while ($i < sizeof($this->vars)) {
					$type = gettype($this->vars[$i]);
					$value = $this->vars[$i];
					$paramType = "";
					//bind_param only accepts i, d, s or b.
					switch($type) {
						case "boolean":
							$paramType = PDO::PARAM_BOOL;
						break;
						case "integer":
							$paramType = PDO::PARAM_INT;
						break;
						case "double":
							$paramType = PDO::PARAM_STR; //PDO does not have a PARAM_FLOAT-like option....
						break;
						case "string":
							$paramType = PDO::PARAM_STR;
						break;
						case "array":
							$paramType = "";
						break;
						case "object":
							$paramType = PDO::PARAM_LOB;
						break;
						case "resource":
							$paramType = "";
						break;
						case "NULL":
							$paramType = PDO::PARAM_NULL;
						break;
					}
					if (!($paramType === "")) { //Allows null values to be bound. This is necessary when using stored procedures.
						try {
							$stmt->bindValue($i + 1, $value, $paramType);
						}
						catch (Exception $e) {
							throw new RuntimeException('SqltoAssoc: Could not bind ' . $value . ' with param type ' . $paramType . ' and i of ' . $i . ': ' . $e->getMessage() . ', query: ' . $query . ', vars: ' . print_r($vars));
						}
					}
					$i++;
				}
			}
		}
		catch (Exception $e) {
			throw new RuntimeException('SqltoAssoc: Could not create DBO in sqltoassoc 2: ' . $e->getMessage());
		}
		
		
		
		
			
		try {
			//echo 'executing statement. Final query is ' . $stmt-><br>';
			//$PDOobj->beginTransaction();
			//$PDOobj->commit();
			//echo 'Executed!<br>';
			
			if ($this->dbObjType == 'oracle') {
				$stid = oci_parse($conn, $this->query);
				oci_execute($stid);
				//echo 'We have ' . oci_fetch_all($stid, $res) . ' rows!';
				$err = oci_error($stid);
				if (!empty($err)) {
					echo 'Error: ' . $err['message'] . '. Query: ' . $err['sqltext'] . '<br>';
					exit;
				}
				
				$result = array();
				
				if ($this->returnInsertedRow) {
					OCIBindByName($stid,":ID",$result,32);
				}
				/*else if (!$this->updateOrDelete) {
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				}*/
				else {
					$i = 0;
					while (($row = oci_fetch_array($stid, OCI_ASSOC)) != false) {
					    //echo 'The row is <span style="color: blue;">' . print_r($row, true) . '</span><br><br>';
						$result[$i] = $row;
						$i++;
					}
				}
				
				oci_free_statement($stid);
				oci_close($conn);
			}
			else {
				$stmt->execute();
				
				$result = null;
				if ($this->returnInsertedRow) {
					$result = intval($PDOobj->lastInsertId());
				}
				else if (!$this->updateOrDelete) {
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				}
				
				$stmt = null;
				unset($PDOobj);
			}
		}
		catch (Exception $e) {
			throw new RuntimeException('SqlToAssoc: Could not execute query: ' . $e->getMessage() . ', query = ' . $this->query . ', vars = ' . print_r($this->vars, true));
		}
		
		
		//echo 'assocArray: Returning ' . gettype($result) . ' ' . $result . '<br>';
		return $result;
	}
}