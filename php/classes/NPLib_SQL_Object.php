<?
class NP_SQL_Object {
	private static $metadata = array();
	private $objectAttributes;


	private static function staticInitialization($connection, $toolkit, $auto_binding) {
		if (!self::isInitialized()) {

			$tableName = is_string($auto_binding) ? $auto_binding : ($auto_binding === false ? null : get_called_class());

			self::$metadata['connection'] = $connection;
			self::$metadata['toolkit'] = $toolkit;
			self::setTable($tableName);

			if ($auto_binding !== false && $tableName !== null) {
				self::$metadata['structure'] = $toolkit::describeTable($tableName);
				//print_r(self::$metadata['structure']);
			} else {
				if (!array_key_exists("structure", self::$metadata)) {
					self::$metadata['structure'] = array();
				}
			}
		}
	}

	public function __construct($connection, $toolkit, $auto_binding = null) {
		if (!self::isInitialized()) {
			self::staticInitialization($connection, $toolkit, $auto_binding);
		}

		$this->objectAttributes = array();

		foreach (self::$metadata['structure'] as $fName => $data) {
			$this->$fName = null;
		}
	}

	public static function isInitialized() {
		return (self::$metadata !== null &&
		array_key_exists("connection", self::$metadata) &&
		array_key_exists("toolkit", self::$metadata) &&
		array_key_exists("structure", self::$metadata) &&
		count(self::$metadata["structure"]) > 0);
	}

	public static function getConnection() {
		return self::getMetadata('connection');
	}

	public static function getMetadata($key) {
		return self::$metadata[$key];
	}

	public static function setTable($table) {
		if ($table !== null) {
			self::$metadata['tableName'] = $table;
		}
	}

	public static function addField($fName, $fInfo) {
		if (!array_key_exists("structure", self::$metadata)) {
			self::$metadata = array();
		}
		self::$metadata["structure"][$fName] = $fInfo;
	}

	public function __get($key){
		$value = null;
		if (array_key_exists($key, $this->objectAttributes)) {
			$type = self::$metadata["structure"][$key]["TYPE"];
			if ($type === "STRING_I18N" || $type === "TEXT_I18N") {
				$value = NP_get_i18n($this->objectAttributes[$key]);
			} else {
				$value = $this->objectAttributes[$key];
			}
		}
		return $value;
	}

	public function __set($key, $value){
		if (!is_array($value)) {
			$type = self::$metadata["structure"][$key]["TYPE"];
			if ($type === "STRING_I18N" || $type === "TEXT_I18N") {
				if (!array_key_exists($key, $this->objectAttributes)) {
					$this->objectAttributes[$key] = array();
				}
				$value = NP_set_i18n($this->objectAttributes[$key], $value);
			}
		}
		$this->objectAttributes[$key] = $value;
	}

	public function walk($f, $params = null) {
		foreach ($this->objectAttributes as $fName => $fValue) {
			$func = new ReflectionFunction($f);
			$p = array($fName, $fValue, self::$metadata['structure'][$fName]);
			if ($params !== null) {
				$p = array_merge($p, $params);
			}
			$func->invokeArgs($p);
		}
	}

	public function load() {
		$toolkit = self::getMetadata("toolkit");
		return $toolkit::loadObject($this);
	}

	public function store() {
		$toolkit = self::getMetadata("toolkit");
		return $toolkit::insertObject($this);
	}

	public function update() {
		$toolkit = self::getMetadata("toolkit");
		return $toolkit::updateObject($this);
	}

	public function delete() {
		$toolkit = self::getMetadata("toolkit");
		return $toolkit::deleteObject($this);
	}
	
	public static function listObjects($query = null) {
		$toolkit = self::getMetadata("toolkit");
		return $toolkit::listObjects(get_called_class(), $query);
	}
}
?>
