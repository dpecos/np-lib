<?
class NP_SQL_Object {
	private $metadata;
	private $objectAttributes;

	public function __construct($connection, $toolkit, $auto_binding = null) {
		$this->metadata = array();
		$this->objectAttributes = array();
		
		$this->metadata['connection'] = $connection;
		$this->metadata['toolkit'] = $toolkit;

		if ($auto_binding !== null) {
			if ($auto_binding !== false) {
				$this->metadata['tableName'] = $auto_binding;
				$this->metadata['structure'] = $toolkit::describeTable($this);
			} else {
				// manual mapping -> nothing todo here
			}
		} else {
			$this->metadata['tableName'] = get_class($this);
			$this->metadata['structure'] = $toolkit::describeTable($this);
		}
	}

	public function __get($key){
		return array_key_exists($key, $this->objectAttributes) ? $this->objectAttributes[$key] : null;
	}

	public function __set($key, $value){
		$this->objectAttributes[$key] = $value;
	}

	public function walk($f, $params = null) {
		foreach ($this->objectAttributes as $fName => $fValue) {
			$func = new ReflectionFunction($f);
			$p = array($fName, $fValue, $this->metadata['structure'][$fName]);
			if ($params !== null) {
				$p = array_merge($p, $params);	
			}
			$func->invokeArgs($p);
		}
	}

	public function getConnection() {
		return $this->getMetadata('connection');
	}
	
	public function getMetadata($key) {
		return $this->metadata[$key];
	}

	public function load() {
		$toolkit = $this->getMetadata("toolkit");
		return $toolkit::loadObject($this);
	}

	public function store() {
		$toolkit = $this->getMetadata("toolkit");
		return $toolkit::insertObject($this);
	}
	
	public function update() {
		$toolkit = $this->getMetadata("toolkit");
		//return $toolkit::updateObject($this, $this->connection);
	}
	
	public function delete() {
		$toolkit = $this->getMetadata("toolkit");
		//return $toolkit::deleteObject($this, $this->connection);
	}
}
?>
