<?php
	// MySQL
	$DB_INFO = [
		"DB_TYPE" => "mysql",
		"HOST" => "localhost",
		"DB_NAME" => "TODO",
		"USER" => "TODO",
		"PASSWORD" => "TODO"
	];

	// Start the session to keep track of who's logged in
	session_start();
	
	class DBConn {
		private $conn;
		
		public function __construct($dbInfo) {
			try {
				$this->conn = new PDO($dbInfo["DB_TYPE"] . ":host=" . $dbInfo["HOST"] . ";dbname=" . $dbInfo["DB_NAME"], $dbInfo["USER"], $dbInfo["PASSWORD"]);
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			catch (PDOException $e) { // Database connection failed
				header("Location: /error.html?code=20");
				// TODO log the exception somewhere
				die();
			}
		}
		
		public function query($query, $parameters = []) {
			try {
				$stmt = $this->conn->prepare($query);
				$stmt->execute($parameters);
				$stmt->setFetchMode(PDO::FETCH_ASSOC);

				if (strtoupper(explode(' ',trim($query))[0]) == "SELECT") {
					return $stmt->fetchAll(); // Return results for select statements
				}
				return true;
			}
			catch (PDOException $e) { // Invalid SQL statement (at least that's usually the problem)
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
		}
	}
	
	$db = new DBConn($DB_INFO);
	if (!isset($GLOBALS["db"])) $GLOBALS["db"] = $db;
?>