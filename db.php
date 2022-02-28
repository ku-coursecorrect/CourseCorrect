<?php
	require_once 'db_creds.php';

	class DBConn {
		private $conn;

		public function __construct($dbInfo) {
			try {
				$this->conn = new PDO($dbInfo["DB_TYPE"] . ":host=" . $dbInfo["HOST"] . ";dbname=" . $dbInfo["DB_NAME"], $dbInfo["USER"], $dbInfo["PASSWORD"]);
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			catch (PDOException $e) { // Database connection failed
				crash(ErrorCode::DBConnectionFailed, $e);
			}
		}

		public function query($query, $parameters = []) {
			try {
				$stmt = $this->conn->prepare($query);
				$stmt->execute($parameters);
				$stmt->setFetchMode(PDO::FETCH_ASSOC);

				$operation = strtoupper(explode(' ',trim($query))[0]);
				if ($operation == "SELECT") {
					return $stmt->fetchAll(); // Return results for select statements
				}
				elseif ($operation == "INSERT" || $operation == "UPDATE" || $operation == "DELETE") {
					return $stmt->rowCount(); // Return number of rows affected
				}
				return true;
			}
			catch (PDOException $e) { // Invalid SQL statement (at least that's usually the problem)
				if (DEBUG) {
					// Dev/test code
					echo "<div style='border: 4px dashed black; background: #faa; display: inline-block; font-size: 14px; text-shadow: none; color: black;'>";
					echo "<h1 style='color: red; text-shadow: none;'>Something is probably wrong with this SQL statement.</h1>";

					$file =  $e->getTrace()[1]["file"];
					$shortFile = substr($file, strrpos($file, "/")+1);

					echo "<b>File: </b>" . $shortFile . "<br>";
					echo "<b>Line: </b>" .  $e->getTrace()[1]["line"] . "<br>";
					echo '<b>SQL statement: </b>"' .  $e->getTrace()[1]["args"][0] . '"<br>';

					echo "<b>Error message: </b> <span style='font-family: monospace'>" .$e->getMessage() . "</span>";

					echo "</div>";
					return false;
				}
				else {
					// Production code
					crash(ErrorCode::DBQueryFailed, $e);
				}
			}
		}

		public function lastInsertId() { return $this->conn->lastInsertId(); }
	}

	$db = new DBConn($DB_INFO);
	if (!isset($GLOBALS["db"])) $GLOBALS["db"] = $db;
?>
