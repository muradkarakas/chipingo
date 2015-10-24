<?php


/**
*  @file
*		This file contains ChipInGo constants (or global static variables)
*
*   $GLOBALS['user']->uid     :   Drupal user id 
 * 
*  Session variables:
*   $_SESSION["user_link_item_switch_item"]  		:  	User menu link object of "Switch Simple/Advanced View" user menu link
*		$_SESSION["user_view_mode"]						: 	Current user view mode. Possible values are
*															S -> Simple view
*															A -> Advanced view 
*	  $_SESSION['user_link_item_publisher_chipingo']	:	User menu link object of "Publisher & ChipInGo" user menu link
*		$_SESSION['destination_url']          :	after form submit to which url user will be directed
*		$_SESSION['qtag_id']                  :	active qtag_id
*   $_SESSION['page_name']                : requested qtag session pages such as restriction, question type, option type, options
*		$_SESSION['session_id'] 							:	active session_id
*/

class ChipInGoConstants {
	
  // TODO mk080 aşağıdaki seçenek değerlerini kolay okulabilirlik adına karakterlere cevir
  
	/**
	*   Open: Default mode
	*   Published: user can give an answer
	* 	Banned: Question is banned to be published. Cannot be published/deleted/edited. And it is not visible to anyone expect admin	
	*/
	public static $QTAG_SESSION_STATUS = array( 0=>'Open', 1=>'Published', 2=>'Banned' );
	
	/**
	*   Normal: Until end of the finish date question remains open
	*   Race: This is the "First xx right answer's owner is the winner" mode.
	* 		  When reached to specific number defined by question owner, question closes automatically. 
	*/
	public static $QTAG_SESSION_QUESTION_TYPE = array( 0=>'Normal', 1=>'Race' );
	
	/**
	*   Strict: Only question owner defines the options. 
	*   Free:   Owner or clients can define their own option
	*   Restricted: Same as free but there is a count limit. Ex option count count more than XX
	*/
	public static $QTAG_SESSION_OPTION_TYPE = array( 'S'=>'Strict', 'F'=>'Free', 'R'=>'Restricted' );
	
	/**
	*   
	*/
	public static $QTAG_SESSION_RESTRICTION_GENDER = array( 0=>'No Restriction', 1=>'Male', 2=>'Female', 3=>'Other' );
	
	/**
	*   
	*/
	public static $QTAG_SESSION_RESTRICTION_LANGUAGE = array( 0=>'No Restriction', 1=>'Apply Language Restriction' );
	
	/**
	*   
	*/
	public static $QTAG_SESSION_RESTRICTION_COUNTRY = array( 0=>'No Restriction', 1=>'Apply Country Restriction' );
	
	/**
	*   
	*/
	public static $QTAG_SESSION_RESTRICTION_CITY = array( 0=>'No Restriction', 1=>'Apply City Restriction' );
	
	/**
	*
	*/
	public static $QTAG_CHIPINGO_STATUS = array( 0=>'Not an e-mail', 1=>'Not Validated', 2=>'Validated' );
	
	/**
	*   
	*/
	public static $MENU_NAME = 'main-menu'; 
	
	/**
	*   
	*/
	public static $QTAG_SESSION_RESTRICTION_AGE = array( 0=>'No Restriction', 1=>'Teenage', 2=>'Adult', 3=>'Senior' );
	
	public static $CHIPINGO_DATE_FORMAT = 'd M Y H:i:s';
	
	public static $CHIPINGO_SESSION_NAME_DATE_FORMAT = 'd M Y';
	
	/**
	*   QTAG Global Variables Arrays
	*/
	public static $QTAG_OVERVIEW_PAGER_LIMIT = 5;

}

