<?php
/**
 * @file
 * Chipingo project ORM classes (this classes are base on FluentCQL library)
 */
use Cassandra\Request\Request;

/**
 *  There are 2 variations
 *  --------------------------
 *    session_by_chipingo_email
 */

/**
 *   Chipingo table ORM class
 */
class SessionTable extends \FluentCQL\Table{
  
  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'chipingo');
    parent::__construct($conn);
    
    $this->_name = 'session_by_chipingo_email'; //has 1 variation
    $this->_primary = ['chipingo_email','qtag','session_name']; // has 1 variation
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;
    
    // TODO mk080 aşağıdaki seçenek değerlerini kolay okulabilirlik adına karakterlere cevir
    $this->_columns = array(
      'chipingo_email' => Cassandra\Type\Base::VARCHAR,
      'qtag' => Cassandra\Type\Base::VARCHAR,
      'session_name' => Cassandra\Type\Base::VARCHAR, //default: date of creation
      'user_id' => Cassandra\Type\Base::INT,
      'session_status' => Cassandra\Type\Base::INT, //'0=>Open, 1=>Published, 2=>Banned', default:0
      'publish_start_date' => Cassandra\Type\Base::INT, //'Questionnaire start date',
			'publish_end_date' => Cassandra\Type\Base::INT, //'Questionnaire end date',
      'session_question_type' => Cassandra\Type\Base::INT, //'0->Normal, 1->Race', default:0
			'session_option_type' => Cassandra\Type\Base::VARCHAR, //'S->Strict, F->Free, R->Restricted' default:0
			'session_restriction_gender' => Cassandra\Type\Base::INT, //''0=>No Restriction, 1=>Male, 2=>Female, 3=>Other', default:0
			'session_restriction_language' => Cassandra\Type\Base::INT, //'0=>No Restriction, 1=>Apply Language Restriction', default:0
			'session_restriction_country' => Cassandra\Type\Base::INT, //'0=>No Restriction, 1=>Apply Country Restriction', default:0
			'session_restriction_city' => Cassandra\Type\Base::INT, //'0=>No Restriction, 1=>Apply City Restriction',   default:0
			'session_restriction_ages' => Cassandra\Type\Base::INT, //0=>No Restriction, 1=>Teenage, 2=>Adult, 3=>Senior',  default:0
    );
	}
 
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_session_by_chipingo_email();
  }
  
  /**
  * Creates "qtag_by_uid" table (if it is not exists) 
  */
  private function _create_session_by_chipingo_email() {

    // STEP 1: table creation
    $cql =  "create table if not exists session_by_chipingo_email ( " .
            " chipingo_email text, " .     
            " qtag text, " .
            " session_name text," .     
            " user_id int, " .
            " publish_start_date int, " .
            " publish_end_date int, " .
            " session_status int, " .
            " session_question_type int, " .
            " session_option_type int, " .
            " session_restriction_gender int, " .
            " session_restriction_language int, " .
            " session_restriction_country int, " .
            " session_restriction_city int, " .
            " session_restriction_ages int, " .            
            " primary key ( (chipingo_email), qtag, session_name ) " .
            ");";  
    $this->_dbAdapter->querySync( $cql );

    // STEP 2: secondary index creation

    // STEP 3: insert built-in rows
    
    // STEP 4: done
  }   
  
  /**
   * Returns one row from session table
   * 
   * @param text $chipingo_email
   * @param text $qtag
   * @param text $sessionName
   * @return array
   */
  function getSession($chipingo_email, $qtag, $sessionName) {
    $response = parent::select()
        ->where('chipingo_email = ? and qtag = ? and session_name = ?', $chipingo_email, $qtag, $sessionName)
        ->querySync();    
    $result = $response->fetchAll();
    return $result; 
  }
  
  /**
   * Returns list of sessions
   * 
   * @param text $chipingo_email
   * @param text $qtag
   */
  function getSessions($chipingo_email, $qtag) {
    $response = parent::select()
        ->where('chipingo_email = ? and qtag = ?', $chipingo_email, $qtag)
        ->querySync();    
    $result = $response->fetchAll();
    return $result; 
  }
  
  /**
   * Returns QTags of a given $chipingo_email
   * 
   * @param int $chipingo_email
   * @return int
   */
  function getQTags($chipingo_email) {
    $response = parent::select()
        ->where('chipingo_email = ?', $chipingo_email)
        ->querySync();    
    $result = $response->fetchAll();
    return $result; 
  }
  

  
  /**
   * Inserts a new row into all variations
   * 
   * @param array $data Column names and values to be inserted
   */
  public function db_insert_all($data) {    
    // mandatory values
    if (!isset($data['chipingo_email'])) {
      throw new Exception('chipingo_email value required');
    }
    if (!isset($data['qtag'])) {
      throw new Exception('qtag value required');
    }
    if (!isset($data['session_name'])) {
      throw new Exception('session_name value required');
    }
    if (!isset($data['user_id'])) {
      throw new Exception('user_id value required');
    }
    // defaults
    if (!isset($data['session_status'])) {
      $data['session_status'] = (int) 0;
    }
    if (!isset($data['session_question_type'])) {
      $data['session_question_type'] = (int) 0;
    }
    if (!isset($data['session_option_type'])) {
      $data['session_option_type'] = 'S';
    }
    if (!isset($data['session_restriction_gender'])) {
      $data['session_restriction_gender'] = (int) 0;
    }
    if (!isset($data['session_restriction_language'])) {
      $data['session_restriction_language'] = (int) 0;
    }
    if (!isset($data['session_restriction_country'])) {
      $data['session_restriction_country'] = (int) 0;
    }
    if (!isset($data['session_restriction_city'])) {
      $data['session_restriction_city'] = (int) 0;
    }
    if (!isset($data['session_restriction_ages'])) {
      $data['session_restriction_ages'] = (int) 0;
    }      
    // create table variations
    $this->_db_insert_session_by_chipingo_email($data);  
  }
  
  /**
   * Inserts a new row into variation
   * @param array $data
   */
  public function _db_insert_session_by_chipingo_email($data) { 
    parent::insertRow($data)->querySync();
  }
  
  /**
   * Deletes a new row from all variations
   * should contain "uid" and "chipingo"
   * 
   * @param array $data Column names and values to be inserted
   */
  public function db_delete_all($data) {    
    $this->_db_delete_session_by_chipingo_email($data); 
  }
  
  /**
   * Deletes a new row from variation
   * @param array $data
   */
  public function _db_delete_session_by_chipingo_email($data) {
    parent::deleteRow([ $data['chipingo_email'], $data['qtag'], $data['session_name'] ])
        ->querySync();
  }

  /**
   * 
   * @param array $data
   */
  public function deleteByChipingoAndQTag($data) {
    parent::deleteRow([ $data['chipingo_email'], $data['qtag'] ])
        ->querySync();    
  }
  
  /**
   * 
   * @param array $data
   */
  public function publishSession($data) {
    $query = parent::update();
    $query = $query->set('publish_start_date = ?, publish_end_date = ?', 
        $data['publish_start_date'],
        $data['publish_end_date']
    ); 
    $query = $query->where('chipingo_email = ? and qtag = ? and session_name = ?', 
        $data['chipingo_email'],
        $data['qtag'],
        $data['session_name']
    ); 
    $query = $query->ifExists();
    $query->assemble();
    $query->querySync();
  }
 
}
