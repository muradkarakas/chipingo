<?php
/**
 * @file
 * Chipingo project ORM classes (this classes are base on FluentCQL library)
 */
use Cassandra\Request\Request;

/**
 *  There are 1 variation
 *  --------------------------
 *    search_paths
 *    
 */

/**
 *   Chipingo table ORM class
 */
class SearchPathsTable extends \FluentCQL\Table{
  
  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'chipingo');
    parent::__construct($conn);
    
    $this->_name = 'search_paths'; //has 1 variation
    $this->_primary = ['part']; // has 1 variation
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;
    
    $this->_columns = array(
      'part' => Cassandra\Type\Base::VARCHAR,
      'whole' => Cassandra\Type\Base::VARCHAR,
      'publish_end_date' => Cassandra\Type\Base::INT,
      'publish_start_date' => Cassandra\Type\Base::INT,
      'session_name' => Cassandra\Type\Base::VARCHAR,
      'qtag' => Cassandra\Type\Base::VARCHAR,
    );
	}
  
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_search_paths();
  }
  
  /**
  * Creates "search_paths" table (if it is not exists) 
  */
  private function _create_search_paths() {

    // STEP 1: table creation
    $cql =  "create table if not exists search_paths (" .
            "  part text,	" .
            "  whole text," .            
            "  publish_end_date int," .
            "  publish_start_date int," .
            "  session_name text, " . 
            "  qtag text, " . 
            "  primary key ((part), publish_end_date, publish_start_date)" .
            ") with clustering order by (publish_end_date desc);";  
    $this->_dbAdapter->querySync( $cql );

    // STEP 2: secondary index creation
    $cql = "create index if not exists search_paths_whole on search_paths(whole);";
    $this->_dbAdapter->querySync( $cql );
    
    // STEP 3: insert built-in rows
    
    // STEP 4: done
  }
    
  /**
   * Inserts a new row into all variations
   * $data = [
   *   'chipingo_email',
   *   'publish_start_date'
   * ];
   * @param array $data Column names and values to be inserted
   */
  public function db_insert_all($data) {    
    // mandatory values
    if (!isset($data['chipingo_email'])) {
      throw new Exception('chipingo_email value required');
    }
    if (!isset($data['publish_start_date'])) {
      throw new Exception('publish_start_date value required');
    }
    if (!isset($data['publish_end_date'])) {
      throw new Exception('publish_end_date value required');
    }
    if (!isset($data['qtag'])) {
      throw new Exception('QTag value required');
    }
    if (!isset($data['session_name'])) {
      throw new Exception('session_name value required');
    }
    
    $chipingo_email = $data['chipingo_email'];
    unset($data['chipingo_email']);
    $arr = explode('@', $chipingo_email);
    $chipingo_length = strlen($arr[0]);
    $data = $data + [
      'publish_start_date' => $data['publish_start_date'],
      'publish_end_date' => $data['publish_end_date']
    ];
        
    // defaults
    for($i = 2; $i <= $chipingo_length; $i++) {
      $data['part'] = substr($chipingo_email,0,$i);
      $data['whole'] = $chipingo_email;  
      $this->_db_insert_search_paths($data);
    }          
    unset($data['part']);
    unset($data['whole']);
    $data['chipingo_email'] = $chipingo_email;
  }
  
    
  /**
   * Inserts a new row into variation
   * @param array $data
   */
  public function _db_insert_search_paths($data) {
    $this->_name = 'search_paths'; 
    $this->_primary = ['part']; 
    parent::insertRow($data)->querySync();
  }
  
  
  
  /**
   * Deletes a new row from all variations
   * should contain "uid" and "chipingo"
   * 
   * @param array $data Column names and values to be inserted
   */
  public function db_delete_all($data) {    
    $this->_db_delete_search_paths($data);   
  }
  
  /**
   *  Deletes a new row from variation
   * @param array $data
   */
  public function _db_delete_search_paths($data) {
    $this->_name = 'search_paths'; 
    $this->_primary = ['part']; 
    parent::deleteRow( 
        [ $data['part'], $data['part'] ]
        )
        ->querySync();
  }
  
  /**
   * Returns searched chipingos for ajax call
   * 
   * @param int $part
   */
  public function getWhole($part) {
    $this->_name = 'search_paths'; 
    $this->_primary = ['part']; 
    $response = parent::select('whole as label, whole as id')
        ->where('part = ? and publish_end_date > ?', $part, time() )
        ->querySync();    
    $result = $response->fetchAll();
    return (array) $result; 
  }
 

  /**
   * Returns QTag count of a given $chipingo_email
   * 
   * @param int $chipingo_email
   * @return int
   */
  public function getPublishedQTagCount($chipingo_email) {
    $this->_name = 'search_paths'; 
    $this->_primary = ['part'];
    $arr = explode('@', $chipingo_email);
    $response = parent::select()
        ->where('part = ? and ' .
                'publish_end_date > ? and ' .
                'whole = ?',  $arr[0], 
                              time(),
                              $chipingo_email )
        ->querySync();    
    $result = $response->fetchAll();
    return (array) $result; 
  }
  
}
