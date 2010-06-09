<?
class NP_SQLObject {
    private $_ddbb;
    
    public function __construct($ddbb) {
        $this->_ddbb = $ddbb;
        
        $this->__guessSQLstructure();
    }
    
    private function _guessSQLstructure() {
        $className = get_class($this);
        
        $this->_ddbb->addTable($className);
    }
    
    public function load($id) {
    }
    public function store() {
        return $this->_ddbb->insertObject($this);
    }
    public function update() {
        return $this->_ddbb->updateObject($this);
    }
    public function delete() {
        return $this->_ddbb->deleteObject($this);
    }
    public function loadFromArray($data) {
        NP_loadDataInto($this, $data);
    }
    
    public function getSQL() {
        return array("table" => "");
    }
}
?>
