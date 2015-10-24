<?php 
	// if path is about qtag, we are showing block
	if ( strpos( current_path(), 'yourqtags/edit/') !== FALSE  ) :
?>

<div id="<?php print $block_html_id; ?>" class="chipingo-block" <?php print $attributes; ?>>
	
	<div class="chipingo-block-header">
		QTag Session List
	</div>
			
	<div class="chipingo-block-body" style="text-align: center;"> 
			<?php
				$result = db_select( 'qtag_sessions', 's' )
						->fields('s', array('session_name') )
						->condition('user_id', $GLOBALS['user']->uid )
						->condition('qtag_id', $_SESSION['qtag_id'] )
						->execute();
					
				while( $record = $result->fetchObject() ) {
			?>
					<div class="btn-group" style="display: inline-block;">
						<button type="button" class="btn btn-warning btn-sm" style="margin-bottom: 4px">
							<?php echo $record->session_name ?>
						</button>
						<button type="button" 
							class="btn btn-danger dropdown-toggle btn-sm" 
							data-toggle="dropdown" aria-expanded="false">
							<span class="caret"></span>
							<span class="sr-only">Toggle Dropdown</span>
						</button>
						
						<ul class="dropdown-menu" role="menu">
							<li style="text-align: center;">
								<a href="#">Question Type</a>
							</li>
							<li style="text-align: center;">
								<a href="#">Option Type</a>
							</li>
							<li style="text-align: center;">
								<a href="#">Restrictions</a>
							</li>
							<li class="divider"></li>
							<li style="text-align: center;">
								<a href="#">Options</a>
							</li>
							<li class="divider"></li>
							<li style="text-align: center;">
								<button type="button" class="vertical-align: top; btn btn-success btn-sm" >
									<i class="fa fa-cog fa-spin fa-1x fa-lg"></i>
									P u b l i s h
								</button>
							</li>
						</ul>
					</div>
					<button type="button" style="margin: 1px; position: relative; top: -2px;" 
						class="btn btn-danger btn-sm" aria-label="Left Align">
						<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
					</button>
			<?php
				}
			?>
	</div>
	<div style="text-align: right; margin-top: 5px;">
		<button type="button" 
			class="btn btn-primary btn-sm">Add new session</button>
	</div>
</div>

<?php endif ?>