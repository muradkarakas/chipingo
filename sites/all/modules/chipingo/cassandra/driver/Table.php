<?php 

namespace FluentCQL;

use Cassandra\Type;

class Table extends \ArrayObject
{
	/**
	 * Adapter object.
	 *
	 * @var \Cassandra\Connection
	 */
	protected $_dbAdapter;

	/**
	 * The keyspace name (default null means current keyspace)
	 *
	 * @var string
	 */
	protected $_keyspace;
	
	/**
	 * 
	 * @var string
	 */
	protected $_name;
	
	/**
	 * 
	 * @var array
	 */
	protected $_primary;
	
	/**
	 * @var array
	 */
	protected $_columns;
	
	/**
	 * 
	 * @var int
	 */
	protected $_writeConsistency;
	
	/**
	 * 
	 * @var int
	 */
	protected $_readConsistency;
	
	/**
	 * 
	 * @param \Cassandra\Connection $adapter
	 */
	public function setDefaultDbAdapter(\Cassandra\Connection $adapter){
		$this->_dbAdapter = $adapter;
	}
	
	/**
	 * 
	 * @return \Cassandra\Connection
	 */
    public function getDefaultDbAdapter(){
      return $this->_dbAdapter;
    }
	
	/**
	 * 
	 * @param \Cassandra\Connection $adapter
	 */
	public function setDbAdapter(\Cassandra\Connection $adapter){
		$this->_dbAdapter = $adapter;
	}
	
	/**
	 * 
	 * @return \Cassandra\Connection
	 */
    public function getDbAdapter(){
      return $this->_dbAdapter;
    }
	
	/**
	 * 
	 * @return \Cassandra\Response\Response
	 */
	public function find(){
    
		$args = func_get_args();
    
    $keyNames = array_values($this->_primary);
		
		$whereList = [];
        
    $qry = new \FluentCQL\Query($this->_dbAdapter);
		$query = $qry->select('*')
			->from( $this->_name );
		    
    $conditions = [];
		foreach($args as $index => $arg){
			$conditions[] = $this->_primary[$index] . ' = ?'; 
		}
    
		$bind = [];
		foreach($args as $index => $arg){
			$type = $this->_columns[$this->_primary[$index]];
			$bind[] = Type\Base::getTypeObject($type, $arg);
		}
		
    $query = $query->where(implode(' AND ', $conditions))
      ->bind($bind)
      ->setDbAdapter($this->_dbAdapter)
      ->setConsistency($this->_readConsistency);
    $response = $query->querySync();
		
    return $response;
	}
	
	/**
	 * 
	 * @return Query
	 */
	public function select($cols = null){  
    $qry = new Query($this->_dbAdapter);
    return $qry->select($cols ?: '*')
              ->from($this->_name)
              ->setDbAdapter($this->_dbAdapter)
              ->setConsistency($this->_readConsistency);
	}
	
	public function insert(){
		$qry = new Query($this->_dbAdapter);
    return $qry->insertInto($this->_name)
			->setDbAdapter($this->_dbAdapter)
			->setConsistency($this->_writeConsistency);
	}
	
	/**
	 * 
	 * @return Query
	 */
	public function update(){
		$qry = new Query($this->_dbAdapter);
    return $qry->update($this->_name)
			->setDbAdapter($this->_dbAdapter)
			->setConsistency($this->_writeConsistency);
	}
	
	/**
	 * 
	 * @return Query
	 */
	public function delete(){
		$qry = new Query($this->_dbAdapter);
    return $qry->delete()
			->from($this->_name)
			->setDbAdapter($this->_dbAdapter)
			->setConsistency($this->_writeConsistency);
	}
	

	/**
	 * 
	 * @return Query
	 */
	public function insertRow(array $data){
		$bind = [];
		foreach($data as $key => $value) {
      $bind[] = Type\Base::getTypeObject($this->_columns[$key], $value);
    }    
    
    $qry = new Query($this->_dbAdapter);
		$query = $qry->insertInto($this->_name)
			->clause('(' . \implode(', ', \array_keys($data)) . ')')
			->values('(' . \implode(', ', \array_fill(0, count($data), '?')) . ')')
			->bind($bind)
			->setDbAdapter($this->_dbAdapter)
			->setConsistency($this->_writeConsistency);
		
		return $query;
	}
	
	/**
	 *
	 * @param array $primary
	 * @param array $data
	 * @return Query
	 */
	public function updateRow($primary, array $data){
       
		$assignments = [];
		foreach($data as $columnName => $value){
			$assignments[] = $columnName . ' = ?';
			$bind[] = Type\Base::getTypeObject($this->_columns[$columnName], $value);
		}
		
		$conditions = [];
		foreach((array)$primary as $index => $value){
			$columnName = $this->_primary[$index];
			$conditions[] = $columnName . ' = ?';
			$bind[] = Type\Base::getTypeObject($this->_columns[$columnName], $value);
		}
		
    $qry = new Query($this->_dbAdapter);
		$query = $qry->update($this->_name)
			->set(implode(', ', $assignments))
			->where(implode(' AND ', $conditions))
			->bind($bind)
			->setDbAdapter($this->_dbAdapter)
			->setConsistency($this->_writeConsistency);
		
		return $query;
	}
	
	/**
	 * 
	 * @param array $primary
	 * @return Query
	 */
	public function deleteRow($primary){
		$conditions = [];
		$bind = [];
		foreach((array)$primary as $index => $value){
      $columnName = $this->_primary[$index];
			$conditions[] = $columnName . ' = ?';
			$bind[] = Type\Base::getTypeObject($this->_columns[$columnName], $value);
		}
		
    $qry = new Query($this->_dbAdapter);
		$query = $qry->delete()
			->from($this->_name)
			->where(implode(' AND ', $conditions))
			->bind($bind)
			->setDbAdapter($this->_dbAdapter)
			->setConsistency($this->_writeConsistency);
		
		return $query;
	}
	
	/**
	 * 
	 * @var array
	 */
	protected $_cleanData = [];
	
	/**
	 * Tracks columns where data has been updated. Allows more specific insert and
	 * update operations.
	 *
	 * @var array
	 */
	protected $_modifiedData = [];
	
	/**
	 * 构造函数
	 * @param array $data
	 * @param int $timestamp
	 * @param int $ttl
	 */
	public function __construct($conn, $data = [], $stored = null){
		parent::__construct($data);
    $this->_dbAdapter = $conn;
    
    if ($stored === true) {
			$this->_cleanData = $this->getArrayCopy();
		}	
	}
	
	public function offsetGet($columnName)
	{
		return parent::offsetExists($columnName) ? parent::offsetGet($columnName) : null;
	}
	
	/**
	 * Set row field value
	 *
	 * @param  string $columnName The column key.
	 * @param  mixed  $value	  The value for the property.
	 * @return void
	 */
	public function offsetSet($columnName, $value)
	{
		if (!in_array($columnName, $_primary)){
			$this->_modifiedData[$columnName] = $value;
		}
		
		parent::offsetSet($columnName, $value);
	}
	
	public function offsetUnset($columnName)
	{
		parent::offsetUnset($columnName);
		$this->_modifiedData[$columnName] = null;
	}
	
	/**
	 * 
	 * @return self
	 */
	public function save()
	{
		$bind = [];
		if (empty($this->_cleanData)) {
			$data = $this->getArrayCopy();
			$bind = [];
			foreach($data as $key => $value) {
				$bind[] = Type\Base::getTypeObject($this->_columns[$key], $value);
      }
      
      $qry = new Query($this->_dbAdapter);
			$query = $qry->insertInto($_name)
				->clause('(' . \implode(', ', \array_keys($data)) . ')')
				->values('(' . \implode(', ', \array_fill(0, count($data), '?')) . ')')
				->bind($bind);
		}
		else{
			$assignments = [];
			
			foreach($this->_modifiedData as $key => $value){
				$assignments[] = $key . ' = ?';
				$bind[] = Type\Base::getTypeObject($this->_columns[$key], $value);
			}
			
			$conditions = [];
			foreach($_primary as $key){
				$conditions[] = $key . ' = ?';
				$bind[] = Type\Base::getTypeObject($this->_columns[$key], $this[$key]);
			}
			$qry = new Query($this->_dbAdapter);
			$query = $qry->update($this->_name)
				->set(implode(', ', $assignments))
				->where(implode(' AND ', $conditions))
				->bind($bind);
		}
		
		$query->setDbAdapter($this->_dbAdapter)
			->setConsistency($this->_writeConsistency)
			->querySync();
		
		return $this;
	}
	
	/**
	 * 删除一整行
	 * @return \Cassandra\Response\Response
	 */
	public function remove(){
    $qry = new Query($this->_dbAdapter);
		$query = $qry->delete()
			->from($this->_name);
		
		$conditions = [];
		$bind = [];
		
		foreach($_primary as $key){
			$conditions[] = $key . ' = ?';
			$bind[] = Type\Base::getTypeObject($this->_columns[$key], $this[$key]);
		}
		
		$query->where(implode(' AND ', $conditions))
			->bind($bind)
			->setDbAdapter($this->_dbAdapter)
			->setConsistency($this->_writeConsistency);
		
		return $query->querySync();
	}
}
