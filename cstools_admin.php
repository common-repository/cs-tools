 <?php    



if(!empty($_POST['service_submit'])) {echo cst_handle_service_option_form();}
if(!empty($_POST['event_submit'])) {echo cst_handle_events_option_form();}
if(!empty($_POST['cst_options'])) { // handle the options form submission;

	$old_options=get_option( 'cst_plugin_options');

	$options_to_save = ARRAY( 'inc_css' => isset($_POST['cst-include-css']), 
			'filter_etitle' => isset($_POST['cst-filter-event-titles']), 
			'redirect_templates' => isset($_POST['cst-redirect_templates']),
			'filter_format' => strip_tags($_POST['cst_event_datetime_format']),
			'ept_slug' => sanitize_title($_POST['cst_event_slug'],'events'),
			'etax_slug' => sanitize_title($_POST['cst_event_tax_slug'],'future'),
			'past_etax_slug' => sanitize_title($_POST['cst_past_event_tax_slug'],'past'),
			
			
				'inc_cac' => isset($_POST['cst-include-cac']),
				'cac_left' => (int) $_POST['cst_cac_left'],
				'cac_down' => (int) $_POST['cst_cac_down'],
				'cac_col' => preg_replace( '/[^a-f0-9]/i', "", $_POST['cst_cac_col'] ), 
				'cac_border_col' => preg_replace( '/[^a-f0-9]/i', "", $_POST['cst_cac_border'] ),
				
				'gms_types' => ARRAY ()
				
			
			 );
			 
			 $args=array( 'public' => TRUE,
		 	# ,'_builtin' => FALSE
					 );
			$output = 'objects'; // names or objects
			$post_types=get_post_types($args,$output); 
 			 foreach ($post_types  as $post_type ) {
  				if ( (isset($_POST["cst-gms-$post_type->name"])) || (CST_EVENT_POST_TYPE==$post_type->name) )
  					$options_to_save['gms_types'][]="$post_type->name";
  				#else $options_to_save['gms_types']["$post_type->name"]=FALSE;
  }	
			
	if (($options_to_save['ept_slug']<>$old_options['ept_slug']) || ($options_to_save['etax_slug']<>$old_options['etax_slug'])
	|| ($options_to_save['past_etax_slug']<>$old_options['past_etax_slug'])
	)
		{update_option('cst_flush_rw', TRUE); }
	
	update_option( 'cst_plugin_options', $options_to_save ); 
			
}

?>


	<div id="icon-options-general" class="icon32"> <br/></div>
	

<div class ="wrap">
<h2> CS Tools settings </h2>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php $cst_plugin_options=get_option('cst_plugin_options');

 ?>

<h3> Styling </h3>
<input type='checkbox' name='cst-include-css' <?php checked( $cst_plugin_options['inc_css'], 1);?> />
 Include Plugin CSS file (Turn this off if you prefer to control formating through you own theme file). <br/>
<h3> Event Post Types </h3>
<input type='checkbox' name='cst-redirect_templates'  <?php checked( $cst_plugin_options['redirect_templates'], 1);?> /> 
Redirect event posts to plugin templates (uncheck if you wish to use your own templates) <br/>
<input type='checkbox' name='cst-filter-event-titles' <?php checked( $cst_plugin_options['filter_etitle'], 1);?> /> 
Prepend date (and time) to Event Title with the following <A href="http://uk.php.net/manual/en/function.date.php" target="help"> datetime format </a>: 
<input type='text' name='cst_event_datetime_format' value='<?php echo esc_attr($cst_plugin_options['filter_format']);?>' size='20' /><br/>
Examples - "jS M Y:" or "Y-m-d @ H:i" <br/>
Event Post Type Base: <input type='text' name='cst_event_slug' value='<?php echo esc_attr($cst_plugin_options['ept_slug']);?>' size='20' /><br/>
Future Event Categories Base: <input type='text' name='cst_event_tax_slug' value='<?php echo esc_attr($cst_plugin_options['etax_slug']);?>' size='20' />
Past Event Categories Base: <input type='text' name='cst_past_event_tax_slug' value='<?php echo esc_attr($cst_plugin_options['past_etax_slug']);?>' size='20' />

<h3> Cross and Crown </h3>
The Cross and Crown Trademark can be licensed for use on the websites of branches of The Mother Church.<br/>
<input type='checkbox' name='cst-include-cac' <?php checked( $cst_plugin_options['inc_cac'], 1);?> />
Include Cross and Crown Trademark at postion 
<input type='text' name='cst_cac_left' value='<?php echo esc_attr($cst_plugin_options['cac_left']);?>' size='3' />
px left and 
<input type='text' name='cst_cac_down' value='<?php echo esc_attr($cst_plugin_options['cac_down']);?>' size='3' />
px down (from the top corner). </br>
Background colour : <input type='text' name='cst_cac_col' value='<?php echo esc_attr($cst_plugin_options['cac_col']);?>' size='6' />
Border colour : <input type='text' name='cst_cac_border' value='<?php echo esc_attr($cst_plugin_options['cac_border_col']);?>' size='6' />

<h3> Add Google map support to : </h3>
<?php 
$args=array(
 'public' => TRUE,
 
 # ,'_builtin' => FALSE
 );
$output = 'objects'; // names or objects
$post_types=get_post_types($args,$output); 

unset($post_types['attachment']);
#print_r($cst_plugin_options['gms_types']);
  foreach ($post_types  as $post_type ) {
   echo "<input type='checkbox' name='cst-gms-$post_type->name'";
   if ('cst_event'==$post_type->name) echo "checked='checked' disabled='true'"; 
   	else    echo checked( in_array($post_type->name, $cst_plugin_options['gms_types']), 1);
   echo ' />' . $post_type->labels->name . '</br>';
  }
?>


<p class="submit">
		<input type="submit" name="cst_options" class="button-primary" value="Save Changes" />
	</p>	

</form>
</div>
<div class="wrap"><hr/>
<?php #	<div id="icon-options-general" class="icon32"><br /></div> ?>
	
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	
	<?php if (function_exists('cst_print_service_options')) { echo cst_print_service_options(); } else {echo "Sorry Service time options not enabled !"; }?>
	
	<p class="submit">
		<input type="submit" name="service_submit" class="button-primary" value="Save Changes" />
	</p>
	</form>	
	
	See this <A href="http://christianscienceplugins.net/2010/12/servicetimes/" target="new">post </A>on christianscienceplugins.net for usage instructions. 
</div>


<div class="wrap"><hr/>
<h3> TRM Widgets </h3>
The plugin provides a shortcode [TRMW code="???"] to easily embedded <A HREF="http://www.widgets.christianscience.org.uk/index.php" target="TRM">Trevor Marr's widgets</A> 
within your post / page content. However before these widgets can be used they need to be <A href="http://www.widgets.christianscience.org.uk/registration.php" target="register">registered</A> for use on your site. 
(There is a small registration fee for each widget). 
<Table> 
<TR> <td><h4>Registered Widgets:</h4> </td><td> <h4>Available (unregistered) widgets </h4></TD> </TR> 
<TR> <TD>
<iframe src='http://www.christianscience.org.uk/crtns/widgets.php' width='400px' height='100 px' scrolling='auto' frameborder='0' marginwidth='0' marginheight='0' hspace='0' vspace='0'></iframe>
</TD><TD>
<iframe src='http://www.christianscience.org.uk/crtns/widgets.php?SR=NO' width='400px' height='100 px' scrolling='auto' frameborder='0' marginwidth='0' marginheight='0' hspace='0' vspace='0'></iframe>
</TD></TR></TABLE>
Register TRM Widgets for use on your site <A href="http://www.widgets.christianscience.org.uk/registration.php" target="register">here.</A><br/><br/>
Please note: the term "widget" here does not refer to a Wordpress widget, but to small code snippets inserted via iframes). 
<hr/>


</div>