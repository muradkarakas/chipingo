<?php
/**
 * @file
 * Chipingo project ORM classes (this classes are base on FluentCQL library)
 */
use Cassandra\Request\Request;

/**
 *  There are 2 variations
 *  --------------------------
 *    chipingo_by_userid
 *    chipingo_by_chipingo
 */

/**
 *   Chipingo table ORM class
 */
class ChipingoTable extends \FluentCQL\Table{
  
  public function __construct($conn=NULL){	
    if ( is_null($conn) ) {
      $conn = Cassandra::initializeCassandraSystem();
    }
    Cassandra::setDefaultShema($conn, 'chipingo');
    parent::__construct($conn);
    
    $this->_name = NULL; //has variation
    $this->_primary = []; // has variation 
    $this->_readConsistency = Request::CONSISTENCY_ONE;
    $this->_writeConsistency = Request::CONSISTENCY_ALL;
    
    $this->_columns = array(
      'chipingo_email' => Cassandra\Type\Base::VARCHAR,
      'chipingo' => Cassandra\Type\Base::VARCHAR,
      'publisher' => Cassandra\Type\Base::VARCHAR,
      'user_id' => Cassandra\Type\Base::INT,
      'chipingo_status' => Cassandra\Type\Base::INT, // 0=>Not an e-mail, 1=>Not Validated, 2=>Validated' default:1
      'default_chipingo' => Cassandra\Type\Base::INT, // 0=>NO, 1=>YES, default:0
      'created' => Cassandra\Type\Base::INT,
      'chipingo_logo_content' => Cassandra\Type\Base::BLOB,
      'chipingo_logo_content_width' => Cassandra\Type\Base::INT,
      'chipingo_logo_content_height' => Cassandra\Type\Base::INT,
      'chipingo_logo_content_mime' => Cassandra\Type\Base::VARCHAR,
      'publisher_logo_content' => Cassandra\Type\Base::BLOB,
    );
	}
  
  /**
   * Creates database objects
   * 
   * @param $conn Connection object. Should be an instance of Cassandra\Connection
   */
  public function create_db_objects() {    
    $this->_create_chipingo_by_chipingo();
    $this->_create_chipingo_by_userid();
  }
  
  /**
  * Creates "chipingo_by_chipingo" table (if it is not exists) 
  * 
  */
  private function _create_chipingo_by_chipingo() {

    // STEP 1: table creation
    $cql =  "create table if not exists chipingo_by_chipingo ( " .
            "  chipingo_email text, " .
            "  chipingo text, " .
            "  publisher text, " .
            "  user_id int, " .            
            "  chipingo_status int, " .
            "  default_chipingo int, " .
            "  created int, " .
            "  changed int, " .
            "  chipingo_logo_content blob, " .
            "  chipingo_logo_content_width int, " .
            "  chipingo_logo_content_height int, " .
            "  chipingo_logo_content_mime text, " .
            "  publisher_logo_content blob, " . 
            "  primary key ( (chipingo_email), user_id ) " .
            ");";  
    $this->_dbAdapter->querySync( $cql );

    // STEP 2: secondary index creation

    // STEP 3: insert built-in rows
    
    // STEP 4: done
  }
  
  /**
  * Creates "chipingo_by_chipingo" table (if it is not exists) 
  */
  private function _create_chipingo_by_userid() {

    // STEP 1: table creation
    $cql =  "create table if not exists chipingo_by_userid ( " .
            "  user_id int, " .            
            "  chipingo_email text, " .
            "  chipingo text, " .
            "  publisher text, " .
            "  chipingo_status int, " .
            "  default_chipingo int, " .
            "  created int, " .
            "  changed int, " .
            "  chipingo_logo_content blob, " .
            "  chipingo_logo_content_width int, " .
            "  chipingo_logo_content_height int, " .
            "  chipingo_logo_content_mime text, " .
            "  publisher_logo_content blob, " . 
            "  primary key ((user_id), chipingo_email) " .
            ");";  
    $this->_dbAdapter->querySync( $cql );

    // STEP 2: secondary index creation

    // STEP 3: done
  }
  
  /**
   * Inserts a new row into all variations
   * 
   * @param array $data Column names and values to be inserted
   */
  public function db_insert_all($data) {    
    // mandatory values
    if (!isset($data['user_id'])) {
      throw new Exception('user_id value required');
    }
    if (!isset($data['chipingo'])) {
      throw new Exception('chipingo value required');
    }
    if (!isset($data['publisher'])) {
      throw new Exception('publisher value required');
    }
    if (!isset($data['chipingo_email'])) {
      throw new Exception('chipingo_email value required');
    }
    // defaults
    if (!isset($data['default_chipingo'])) {
      $data['default_chipingo'] = (int) 0;
    }
    if (!isset($data['chipingo_status'])) {
      $data['chipingo_status'] = (int) 2;
    }
    $this->_db_insert_chipingo_by_userid($data);   
    $this->_db_insert_chipingo_by_chipingo($data);   
  }
  
  /**
   * Inserts a new row into variation
   * @param array $data
   */
  public function _db_insert_chipingo_by_userid($data) {
    $this->_name = 'chipingo_by_userid'; 
    $this->_primary = ['user_id']; 
    parent::insertRow($data)->querySync();
  }
  
  /**
   * Inserts a new row into variation
   * @param array $data
   */
  public function _db_insert_chipingo_by_chipingo($data) {
    $this->_name = 'chipingo_by_chipingo'; 
    $this->_primary = ['chipingo_email']; 
    parent::insertRow($data)->querySync();
  }
  
  /**
   * Deletes a new row from all variations
   * should contain "uid" and "chipingo"
   * 
   * @param array $data Column names and values to be inserted
   */
  public function db_delete_all($data) {    
    $this->_db_delete_chipingo_by_userid($data);   
    $this->_db_delete_chipingo_by_chipingo($data);   
  }
  
  /**
   *  Deletes a new row from variation
   * @param array $data
   */
  public function _db_delete_chipingo_by_userid($data) {
    $this->_name = 'chipingo_by_userid'; 
    $this->_primary = ['user_id', 'chipingo_email']; 
    parent::deleteRow( 
        [ $data['user_id'], $data['chipingo_email'] ]
        )
        ->querySync();
  }
  
  /**
   * Deletes a new row from variation
   * @param array $data
   */
  public function _db_delete_chipingo_by_chipingo($data) {
    $this->_name = 'chipingo_by_chipingo'; 
    $this->_primary = ['chipingo_email']; 
    parent::deleteRow($data['chipingo_email'])->querySync();
  }
  
  /**
   * Returns all chipingos of a user
   * 
   * @param int $uid
   * @return array Chipingo list
   */
  public function getChipingosByUid($uid) {
    $this->_name = 'chipingo_by_userid';
    $this->_primary = [ 'user_id' ]; 
    $response = parent::find($uid)->fetchAll();
    return $response;
  }
 
  /**
   * Returns a chipingo by user id
   * 
   * @param int $chipingo
   * @return array Chipingo
   */
  public function getChipingoByChipingo($chipingo_email) {
    $this->_name = 'chipingo_by_chipingo';
    $this->_primary = [ 'chipingo_email' ]; 
    $response = parent::find($chipingo_email)->fetchRow();
    return $response;
  }
  
  /**
   * 
   * @param type $chipingo_email
   * @return type
   */
  public function readAllLogos($chipingo_email) {
    $this->_name = 'chipingo_by_chipingo';
    $this->_primary = [ 'chipingo_email' ]; 
    $response = parent::find($chipingo_email)->fetchRow();
    return $response;
  }
    
  /**
   * Write published logo image file to cassandra.
   * $filename should contain full local path of the file.
   * 
   * @param text $chipingo_email
   * @param text $filename
   * @param int $user_id
   */
  public function writePublisherLogo($chipingo_email, $filename, $user_id) {      
    $handle = fopen($filename, "r");
    $content = bin2hex( fread($handle, filesize($filename)) );
    fclose($handle);    
    $this->_write__publisher_logo_chipingo_by_chipingo($content,$chipingo_email, $user_id);
    $this->_write__publisher_logo_chipingo_by_user_id($content,$chipingo_email, $user_id);
  }
  
  /** 
   * @param blob $content
   * @param text $chipingo_email
   * @param text $user_id
   */
  private function _write__publisher_logo_chipingo_by_chipingo($content,$chipingo_email, $user_id) {
    $this->_name = 'chipingo_by_chipingo';
    $this->_primary = [ 'chipingo_email' ]; 
    $query = parent::update();
    $query = $query->set( 'publisher_logo_content = ?', $content); 
    $query = $query->where('chipingo_email = ? and user_id = ?', $chipingo_email, $user_id); 
    $query = $query->ifExists();
    $query->assemble();
    $query->querySync();
  }

  /** 
   * @param blob $content
   * @param text $chipingo_email
   * @param text $user_id
   */
  private function _write__publisher_logo_chipingo_by_user_id($content,$chipingo_email, $user_id) {
    $this->_name = 'chipingo_by_userid';
    $this->_primary = [ 'user_id' ]; 
    $query = parent::update();
    $query = $query->set( 'publisher_logo_content = ?', $content); 
    $query = $query->where('chipingo_email = ? and user_id = ?', $chipingo_email, $user_id); 
    $query = $query->ifExists();
    $query->assemble();
    $query->querySync();
  }
  
    
  
  
  /**
   * Write published logo image file to cassandra.
   * $filename should contain full local path of the file.
   * 
   * @param text $chipingo_email
   * @param text $filename
   * @param int $user_id
   */
  public function writeChipingoLogo($chipingo_email, $filename, $user_id) {      
    $handle = fopen($filename, "r");
    $size = getimagesize($filename, $info);
    $data['width'] = $size[0];
    $data['height'] = $size[1];
    $data['mime'] = $size['mime'];
    $data['content'] = bin2hex( fread($handle, filesize($filename)) );
    $data['chipingo_email'] = $chipingo_email;
    $data['user_id'] = $user_id;
    fclose($handle);    
    $this->_write__chipingo_logo_chipingo_by_chipingo($data);
    $this->_write__chipingo_logo_chipingo_by_user_id($data);
  }
  
  /** 
   * @param blob $content
   * @param text $chipingo_email
   * @param int width
   * @param int height
   * @param text $user_id
   */
  private function _write__chipingo_logo_chipingo_by_chipingo($data) {
    //$content,$chipingo_email, $user_id
    $this->_name = 'chipingo_by_chipingo';
    $this->_primary = [ 'chipingo_email' ]; 
    $query = parent::update();
    $query = $query->set( 
        'chipingo_logo_content = ?, '
        . 'chipingo_logo_content_width = ?, '
        . 'chipingo_logo_content_height = ?, '
        . 'chipingo_logo_content_mime = ?',
        $data['content'],
        $data['width'],
        $data['height'],
        $data['mime']); 
    $query = $query->where('chipingo_email = ? and user_id = ?', 
        $data['chipingo_email'], $data['user_id']); 
    $query = $query->ifExists();
    $query->assemble();
    $query->querySync();
  }

  /** 
   * @param blob $content
   * @param text $chipingo_email
   * @param int width
   * @param int height
   * @param text $user_id
   */
  private function _write__chipingo_logo_chipingo_by_user_id($data) {
    //$content,$chipingo_email, $user_id
    $this->_name = 'chipingo_by_userid';
    $this->_primary = [ 'user_id' ]; 
    $query = parent::update();
    $query = $query->set( 
        'chipingo_logo_content = ?, '
        . 'chipingo_logo_content_width = ?, '
        . 'chipingo_logo_content_height = ?, '
        . 'chipingo_logo_content_mime = ?',
        $data['content'],
        $data['width'],
        $data['height'],
        $data['mime']); 
    $query = $query->where('chipingo_email = ? and user_id = ?', 
        $data['chipingo_email'], $data['user_id']); 
    $query = $query->ifExists();
    $query->assemble();
    $query->querySync();
  }
}
