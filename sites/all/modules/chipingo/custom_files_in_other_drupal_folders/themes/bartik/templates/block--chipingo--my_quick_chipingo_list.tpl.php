<script type="text/javascript">
	
	var Accordion = function( el ) {
						this.el = el || {};
						//this.multiple = multiple || false;
						
						var links = this.el.find( '.link' );
						var sublinks = this.el.find( '.sublink' );
						
						links.on('click', {el: this.el, a:'murad'}, this.dropdown);	
						sublinks.on('click', {el: this.el, a:'ozgul'}, this.dropdown);
					}
	
	Accordion.prototype.dropdown = 	function( e ) {
										var $el = e.data.el;
											$this = $(this),
											$next = $this.next();
										
										$next.slideToggle();
										$this.parent().toggleClass( e.data.a );
									}		
	
</script>

<div id="<?php print $block_html_id; ?>" class="chipingo-block <?php /*print $classes;*/ ?>"<?php print $attributes; ?>>
	
	<div class="accordion chipingo-radius chipingo-div ">
	
		<div class="chipingo-block-header">
			<font style="color:#F90">Chip</font>
			<font style="color:#093">In</font>
			<font style="color:#F00">Go</font>
			& QTag List
		</div>
		
		<ul id="accordion" class="chipingo-radius" style="padding: 0px;">
			
<?php
		$publisher_list = _getCurrentUserQuickChipingoList();	
		foreach( $publisher_list as $publisher ) {
?>	
			<li>
				<div class="link">
					<i class="fa fa-home fa-2x"></i>
					<span ><?php echo $publisher->publisher ?></span>
					<i murad="" class="fa fa-chevron-down"></i>
				</div>
				<ul class="submenu ">					
<?php
		$chipingo_list = get_current_user_qtag_chipingo( $publisher->publisher );	
		foreach( $chipingo_list as $chipingo ) {
			$published_qtag_count = get_chipingo_published_qtag_count( $chipingo->chipingo_id );
?>					
					<li>
						
						<ul id="submenu  accordion<?php echo $chipingo->chipingo_id ?>" 
							class="accordion  chipingo-radius">
							<li>
								<div class="sublink link<?php echo $chipingo->chipingo_id ?>">
									
									<?php echo get_chipingo_icon_html( $chipingo->chipingo_status, $published_qtag_count, 2 ) ?>
									<span class="badge" style="position: relative; top: -2px;">
										<?php echo get_chipingo_qtag_count( $chipingo->chipingo_id ) ?>
									</span>
									<span><?php echo  $chipingo->chipingo ?></span>
									
									<i ozgul="" style="float: right;" class="fa fa-chevron-down"></i>
								</div>
								
								<ul class="sub submenu<?php echo $chipingo->chipingo_id ?>" >
<?php
		$qtag_list = get_current_user_qtag_list( $chipingo->chipingo_id );	
		
		if ( $chipingo->chipingo_status == 0 or $chipingo->chipingo_status == 2 ) {
			foreach( $qtag_list as $qtag ) {
			
?>										
									<li>
										<a href="/chipingo/yourqtags/edit/<?php echo $qtag->qtag_id ?>">
											<?php echo $qtag->qtag ?>
										</a>
									</li>
<?php   	} 
		}
?>									
								</ul>
							</li>
						</ul>
						
					</li>
<?php   } ?>					
					
				</ul>
			</li>
	 
<?php } ?>
		
		</ul>
	
	</div>

</div>

<script> 
	var accordion = new Accordion( $('#accordion') );

</script>
