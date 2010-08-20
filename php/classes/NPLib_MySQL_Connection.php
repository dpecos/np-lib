<?php
class NP_MySQL_Connection {

	private $config;
	private $connection;

	function __construct($host, $port, $dbName, $user, $password) {
		if (isset($port) && $port !== null) {
			$host .= ":".$port;
		}
		$this->config = $this->config = array("HOST" => $host, "NAME" => $dbName, "USER" => $user, "PASSWD" => $password);
	}

	function isConnected() {
		return $this->connection != null;
	}

	function connect () {
		if ($this->isConnected()) {
			return true;
		} else {
			if ($this->config != null) {
				try {
					$this->connection = mysql_connect($this->config["HOST"], $this->config["USER"], $this->config["PASSWD"]);
					if (!$this->connection) {
						throw new NP_Exception("Could not connect with the database server: ".mysql_error());
					}

					if (!mysql_select_db($this->config["NAME"])) {
						throw new NP_Exception("Database name '".$this->config["NAME"]."' not found on server: ".mysql_error());	
					}
				} catch (NP_Exception $ex) {
					throw $ex;
				} catch (Exception $ex) {
					throw new NP_Exception("Unknown error while connecting with database: ".$ex->getMessage()."\n".$ex->getTraceAsString());
				}
			} else {
				throw new Exception("Database not initialized yet");
			}

			return true;
		}
	}

	function disconnect () {
		if ($this->isConnected()) {
			try {
				if ($this->connection != null) {
					mysql_close($this->connection);
					$this->connection = null;
				}
			} catch (Exception $ex) {
				throw new NP_Exception("Unknown error while connecting with database: ".$ex->getMessage()."\n".$ex->getTraceAsString());
			}
		}
		return true;
	}

	function q($sql, $callbackFunc = null, $params = null) {
		return $this->query($sql, $callbackFunc, $params);
	}

	/**
	 *
	 * Execute a SQL query.
	 * @param $sql
	 * @param $callbackFunc
	 * @param $params
	 *
	 * @return SELECT query => Array of rows, null if zero rows where returned;
	 * INSERT/UPDATE query => Last generated AUTO_INCREMENT when inserting data (if applies), null if none where inserted;
	 * DELETE query => Number of deleted rows
	 */
	function query($sql, $callbackFunc = null, $params = null) {

		if ($this->connect()) {
			$result = null;

			
			$result = mysql_query($sql);
			if (!$result) {
				throw new Exception("Errors in query execution ($sql): " . mysql_error());
			}

			$error = null;
			
			if (!is_bool($result)) {
				// select query that run ok
				$data = null;
				if (isset($callbackFunc) && $callbackFunc != null && function_exists($callbackFunc)) {
					if ($params == null) {
						$params = array();
					}
					while ($datos = mysql_fetch_assoc($result)) {
						$func = new ReflectionFunction($callbackFunc);
						$p = array_merge(array($datos), $params);
						$func->invokeArgs($p);
						//$f($datos, $params);
					}
				} else {
					$data = array();
					while ($datos = mysql_fetch_assoc ($result)) {
						$data = array_merge($data, array($datos));
					}
				}

				mysql_free_result($result);

			} else {
				// insert / update / delete query or query with errors
				if ($result === true) {
					$data = mysql_insert_id();
					if ($data === 0) {
						// maybe is a delete query -> number of deleted rows
						$data = mysql_affected_rows();
					}
					if ($data === 0) {
						$data = null;
					}
				} else {
					$error = mysql_error();
				}
			}

			$this->disconnect();
			
			if ($error != null) {
				throw new NP_Exception("SQL error (".$sql."):".$error);
			} else {
				return $data;
			}
		}
	}
}
?>