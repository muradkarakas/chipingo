<?php

module_load_include( 'php', 'chipingo', 'menus_and_forms/api_common' );
module_load_include( 'php', 'chipingo', 'menus_and_forms/sidebar/quick_chipingo_list/api' );
module_load_include( 'php', 'chipingo', 'menus_and_forms/content/qtag/api' );


/**
 * 
 * @param type $form
 * @param type $form_state
 */
function quick_chipingo_list_form( $form, &$form_state ) {

  $conn = Cassandra::initializeCassandraSystem();
  
  $form['quick-chipingo-list-ajax-div-start'] = [
    '#markup' => '<div id="quick-chipingo-list-ajax-div">'
  ];
  
  // TODO mk000 connection nesnesi diğer fonksiyonlar tarafından kullanılsın bu fonksiyon içinde
  $form['block-start'] = [
    '#markup' =>  '<div class="chipingo-block">' .
                  '   <div class="chipingo-block-header">' .
                        t('Publisher') . ' / ' . t('Program') . ' / ' .
                  '     <font style="color:#F90">Chip</font>' .
                  '     <font style="color:#093">In</font>' .
                  '     <font style="color:#F00">Go</font>' .
                  '     List' .
                  '   </div>' .
                  '',
  ];
  
  $full_chipingo_email_list = _getCurrentUserQuickChipingoList($conn);
  $publisher_list = _getUniqueDomainNameFromChipingoEmailList($full_chipingo_email_list);
      
	foreach( $publisher_list as $publisher ) {
  
    $publisher_divname = 'publisher' . str_replace('.','',__convertToHTMLId($publisher['publisher']));
    
    $form['block-publisher-start-' . $publisher_divname ] = [
      '#markup' =>  '<a href="#' . $publisher_divname . '" '.
                        ' class="list-group-item list-group-item-success" '.
                        ' data-toggle="collapse" ' .
                        ' data-parent="#MainMenu">'.
                        __getPublisherSourcePath($publisher['chipingo_email']) .
                        '  ' . $publisher['publisher'] . '&nbsp;&nbsp;&nbsp;' .
                        '<i class="fa fa-caret-down"></i>' .
                    '</a>' .
                    '<div class="collapse" id="' . $publisher_divname . '">',
    ];
    
    $chipingo_list = _getUniqueEmailFromChipingoEmailList($publisher['publisher'], $conn);	
    
		foreach( $chipingo_list as $chipingo ) {
			
      $full_chipingo = $chipingo['chipingo_email'];// . '@' . $publisher;
      
      $publishedQTag = __getPublishedQTagCount($full_chipingo, $conn);
      
      $chipingo_divname = 'chipingo-' . __convertToHTMLId($chipingo['chipingo']);
      
      $form['block-chipingo-start-' . $chipingo_divname ] = [
         '#markup' => '<a href="#' . $chipingo_divname . '" class="list-group-item list-group-item-info" '.
                        ' data-toggle="collapse" '.
                        ' data-parent="#' . $chipingo_divname . '">'.
                        '<span style="block: inline-block; padding: 15px;"></span>' .
                         __getChipingoIconHtml( $chipingo['chipingo_status'], count($publishedQTag) ) . '&nbsp;&nbsp;' .
                        $chipingo['chipingo'] . 
                        ' <i class="fa fa-caret-down"></i>'.
                      '</a>' .
                      '<div class="collapse" id="' . $chipingo_divname . '">',
     ];
      
      $qtag_list = _getQTags($full_chipingo, $conn);	
      
      foreach( $qtag_list as $qtag ) {
          $form['block-qtag-start-' . $qtag['qtag'] ] = [
              '#markup' =>  '<a href="' . __getQTagEditPath($full_chipingo, $qtag['qtag'], 'last' ) . '" class="list-group-item">'.
                            '<span style="block: inline-block; padding: 27px;"></span>' . 
                              $qtag['qtag'] .  
                            '</a>',
          ];
      }
      
      $form['block-chipingo-end-' . $chipingo_divname ] = [
        '#markup' =>  '</div>   ',
      ]; 
    }   
    
    $form['block-publisher-end' . $publisher_divname ] = [
      '#markup' =>  '</div>   ',
    ];
    
  }   
  
  $form['block-end'] = [
    '#markup' => '</div>',
  ];
  
  $form['quick-chipingo-list-ajax-div-end'] = [
    '#markup' => '</div>'
  ];
  
  Cassandra::disConnect($conn);
  return $form;  
}