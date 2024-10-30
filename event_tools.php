<?php
 

DEFINE( 'CST_EVENT_TAXONOMY', 'cst_event-cats');  // was 'cst_event-cats'; These should be changed once plugin installed!;
DEFINE( 'CST_EVENT_POST_TYPE', 'cst_event');   // was 'cst_event';
DEFINE( 'CST_CF_PREFIX','_cst_'); // should be '_cst_'

	$cst_plugin_options=get_option('cst_plugin_options');
	$cst_google_markers = ARRAY(); // set a global variable to store the our markers for google maps;
	 $cst_gmap_script_added = false ; // 

add_action('init', 'cst_custom_init',1);

function cst_custom_init() 
{
	$cst_plugin_options=get_option('cst_plugin_options');

  	// Add new taxonomy, make it hierarchical (like categories)
  	$labels = array(
    		'name' => _x( 'Event Categories', 'taxonomy general name' ),
    		'singular_name' => _x( 'Event Category', 'taxonomy singular name' ),
    		'search_items' =>  __( 'Search Event Categories' ),
    		'all_items' => __( 'All Event Categories' ),
    		'parent_item' => __( 'Parent Event Category' ),
    		'parent_item_colon' => __( 'Parent Event Category:' ),
    		'edit_item' => __( 'Edit Event Category' ), 
    		'update_item' => __( 'Update Event Category' ),
    		'add_new_item' => __( 'Add New Event Category' ),
    		'new_item_name' => __( 'New Event Category name' ),
    		'menu_name' => __( 'Event Categories' ),
  		); 	

  	register_taxonomy(CST_EVENT_TAXONOMY,array(CST_EVENT_POST_TYPE), array(
    	'hierarchical' => true,
    	'labels' => $labels,
    	'show_ui' => true,
    	'query_var' => true,
    	'rewrite' => array( 'slug' => $cst_plugin_options['etax_slug'] ), 
  	));


	// register the Event post type;
  	$labels = array(
    		'name' => _x('Events', 'post type general name'),
    		'singular_name' => _x('Event', 'post type singular name'),
    		'add_new' => _x('Add New', 'event'),
    		'add_new_item' => __('Add New Event'),
    		'edit_item' => __('Edit Event'),
    		'new_item' => __('New Event'),
    		'view_item' => __('View Event'),
    		'search_items' => __('Search Events'),
    		'not_found' =>  __('No events found'),
    		'not_found_in_trash' => __('No events found in Trash'), 
    		'parent_item_colon' => '',
    		'menu_name' => 'Events'
  		);

  	$args = array(
    		'labels' => $labels,
    		'public' => true,
    		'publicly_queryable' => true,
    		'show_ui' => true, 
    		'show_in_menu' => true, 
    		'query_var' => true,
    		'rewrite' => array( 'slug' => $cst_plugin_options['ept_slug'] ),
    		'capability_type' => 'post',
    		'has_archive' => true, 
    		'menu_position' => null,  
 	// NB I would have liked ot make post type hierarchical - but then you can't add custom columns due limited core functionality. if this is fixed add - 'page-attributes' to supports; 
      		'hierarchical' => false,
    		'supports' => array('title','editor','author','thumbnail','excerpt','comments','custom-fields' )
  		); 
  		
  	register_post_type(CST_EVENT_POST_TYPE,$args);
  
  	/*****************************************************************************
	** Rewrite rules need to be added to cope with past events
	** 
	** 
	*********************************************************************************/
	/** This isn't work so I am removing for now... may need to add permastructure instead of aswell...;
		$cst_write_rules = ARRAY (
      		  	CST_PAST_EVENT_TAX_SLUG.'/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?'.CST_EVENT_TAXONOMY.'=$matches[1]&feed=$matches[2]&show_events=past',
       	     		CST_PAST_EVENT_TAX_SLUG.'/([^/]+)/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?'.CST_EVENT_TAXONOMY.'=$matches[1]&feed=$matches[2]&show_events=past',
            		CST_PAST_EVENT_TAX_SLUG.'/([^/]+)/page/?([0-9]{1,})/?$' => 'index.php?'.CST_EVENT_TAXONOMY.'=$matches[1]&paged=$matches[2]&show_events=past',
            		CST_PAST_EVENT_TAX_SLUG.'/([^/]+)/?$' => 'index.php?'.CST_EVENT_TAXONOMY.'=$matches[1]&show_events=past'
            	); 

		foreach ($cst_write_rules AS $pattern => $replacement) {
			add_rewrite_rule( $pattern, $replacement, 'top');
		}
	
	*/ 
	add_rewrite_rule( $cst_plugin_options['past_etax_slug'].'/([^/]+)/?$', 'index.php?'.CST_EVENT_TAXONOMY.'=$matches[1]&show_events=past','top');
	
	add_feed ('iCal', 'cst_output_ical_feed');
	add_feed ('ical', 'cst_output_ical_feed');

	// probably this should be on admin_init hook; 
	wp_enqueue_script('datepickerScript', plugins_url('/js/jquery.ui.datepicker.js',__FILE__), array('jquery','jquery-ui-core'),'1.8.11');
	wp_enqueue_style('datepickerStyle', plugins_url('/css/jquery-ui-1.8.11.custom.css',__FILE__));
	
	if (get_option('cst_flush_rw')==TRUE) {
		flush_rewrite_rules(); 
		 update_option('cst_flush_rw', FALSE);
		} 
	

} // End of functions hooked to init. 

add_action('right_now_content_table_end', 'add_recipe_counts');

function add_recipe_counts() {
        if (!post_type_exists(CST_EVENT_POST_TYPE)) {
             return;
        }

        $num_posts = wp_count_posts( CST_EVENT_POST_TYPE );
        $num = number_format_i18n( $num_posts->publish );
        $text = _n( 'Event', 'Events', intval($num_posts->publish) );
        if ( current_user_can( 'edit_posts' ) ) {
            $num = "<a href='edit.php?post_type=".CST_EVENT_POST_TYPE."'>$num</a>";
            $text = "<a href='edit.php?post_type=".CST_EVENT_POST_TYPE."'>$text</a>";
        }
        echo '<td class="first b b-recipes">' . $num . '</td>';
        echo '<td class="t recipes">' . $text . '</td>';

        echo '</tr>';

        if ($num_posts->pending > 0) {
            $num = number_format_i18n( $num_posts->pending );
            $text = _n( 'Event Pending', 'Events Pending', intval($num_posts->pending) );
            if ( current_user_can( 'edit_posts' ) ) {
                $num = "<a href='edit.php?post_status=pending&post_type=".CST_EVENT_POST_TYPE."'>$num</a>";
                $text = "<a href='edit.php?post_status=pending&post_type=".CST_EVENT_POST_TYPE."'>$text</a>";
            }
            echo '<td class="first b b-recipes">' . $num . '</td>';
            echo '<td class="t recipes">' . $text . '</td>';

            echo '</tr>';
        }
}


// Add functions for the edit - page
add_action ( 'load-edit.php', 'cst_handle_load_edit' );
function cst_handle_load_edit () {

	add_filter("manage_edit-cst_event_columns", "my_event_columns");
	 
	function my_event_columns($columns){
	
		$columns = array (	
	 		'cb' => '<input type="checkbox" />',
	    		'event_date' => 'Event Date',
    			'title' => 'Title',
        		CST_EVENT_TAXONOMY => 'Categories',
    			'author' => 'Author',
    			'comments' => '<div class="vers"><img alt="Comments" src="http://www.christianscienceplugins.net/b-demo/wp-admin/images/comment-grey-bubble.png" /></div>',
    			'date' => 'Publication date'
			);
	    return $columns;
	}

	add_action("manage_posts_custom_column", "cst_custom_columns");

	function cst_custom_columns($column) {
	    global $post;
    		if ("ID" == $column) echo $post->ID;
    		elseif ("event_date" == $column) {
   			 $timestamp=get_post_meta( $post->ID, CST_CF_PREFIX.'timestamp', true );
    			if (null==$timestamp ) { echo "Not set!!!";}
   			 else { 	
    				$cst_tbc=get_post_meta( $post->ID, CST_CF_PREFIX.'tbc', true );
        		 	echo date( 'Y-M-d' , $timestamp); 
        		 	if (TRUE==$cst_tbc['date'])  echo ' (T.B.C.)';
         			echo '<br/>'.date( ' @ H:i' , $timestamp); 
         			if (TRUE==$cst_tbc['time']) echo ' (T.B.C.)';
      				}
    		} elseif (CST_EVENT_TAXONOMY==$column) {
    			echo the_terms( $post->ID, CST_EVENT_TAXONOMY, '', ', ', ' ' );
		}
	} // end cst_custom_columns function;

	add_filter("restrict_manage_posts", 'cst_handle_restrict_manage_posts');
	function cst_handle_restrict_manage_posts () { 

		if ($_GET['post_type']=='cst_event') {
		?> 
		<select name="show_events" id='show_events' class='postform'> 
  			<option value="future"> View Upcoming events </option>
  			<option value="past" <?php if (isset($_GET['show_events']) && $_GET['show_events']=="<") echo 'selected="selected"' ?> > View past events </option>
  			<option value="all" <?php if (isset($_GET['show_events']) && $_GET['show_events']=="A") echo 'selected="selected"' ?> > Show all dates </option>
  			</select>
  		<?php
  		}
	}

/**
	add_filter("posts_where", 'cst_handle_posts_where');
	function cst_handle_posts_where ($where) { 
     		global $wpdb;    
     		if ((get_query_var('post_type') ==CST_EVENT_POST_TYPE) && (array_key_exists('cst_evdates' , $_GET))) {
      			if (($_GET['cst_evdates']=="<") || $_GET['cst_evdates']==">" ){ 
          
            			$now=strtotime('-5 minutes');  // 5 minutes grace period! ; 
            			$where .=" AND ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='".CST_CF_PREFIX . "timestamp' AND meta_value".$_GET['cst_evdates'].$now.' )';
           
          		} else if ($_GET['cst_evdates']=="tbc") $where.=" AND ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='cst_tbc' AND (meta_value='date' OR meta_value='time' OR meta_value='datetime'))";
   		}
    		return $where;
    	}
    	
**/

  
   

} // end of handle-edit.php; 



		/**
		* Create the new Custom Fields meta box
		*/
		add_action( 'admin_menu', 'cst_createCustomFields' );
		function cst_createCustomFields() {
		
		global $cst_plugin_options; 
			if ( function_exists( 'add_meta_box' ) ) {
		
				// The following line adds a meta box on the side to enter the event date and time;
			        add_meta_box( 'event-date', 'Event Details', 'cst_displayEventDate', CST_EVENT_POST_TYPE, 'side', 'high' );
			        // add this adds a meta box for with the venue fields; 
			        	
			          foreach ($cst_plugin_options['gms_types'] as $post_type ) {
			         	 add_meta_box( 'event-venue', 'Venue Details', 'cst_display_venue_fields', $post_type ,'normal','high'); 
				}
			}
		}
		
		
		/**
		* Display the new Event Date meta box
		*/
		function cst_displayEventDate(){
		
			global $post;
			echo "<p> Please enter date as YYYY-MM-DD @ HH:MM </p>";
			$duration=get_post_meta( $post->ID, CST_CF_PREFIX. 'duration', true );
			$event['durhour']= floor ($duration / 60);
			$event['durmin']= $duration % 60;
			$timestamp=get_post_meta( $post->ID, CST_CF_PREFIX . 'timestamp', true );
			if ($timestamp==null) {$timestamp=strtotime('next Sunday');}
			echo " <table> <tr><td>Start: <input type='text' name='cst-date' id='cst-date'  value='".date('Y-m-d',$timestamp)."' size='10' /></td>";
			echo "<td> @ </td><td><input type='text' name='cst-hour' value='".date('H',$timestamp)."' size='2' />:<input type='text' name='cst-mins' value='".date('i',$timestamp)."' size='2' /></td></tr>";
			$cst_tbc=get_post_meta( $post->ID, CST_CF_PREFIX. 'tbc', true );
			if ($cst_tbc==null) {$cst_tbc=ARRAY( 'date' => FALSE, 'time' => FALSE);}
			echo '<tr><td><input type="checkbox" name="cst_date_tbc" id="cst_date_tbc" value="date" ';
			#if (('date'==$cst_tbc) OR ('datetime'==$cst_tbc))  echo ' checked="checked"';
			if (TRUE==$cst_tbc['date']) echo ' checked="checked"';
			echo '> Date T.B.C. </input></td><td></td>';
			echo '<td><input type="checkbox" name="cst_time_tbc" id="cst_time_tbc" value="time" ';
			#if (('time'==$cst_tbc) OR ('datetime'==$cst_tbc)) echo ' checked="checked"';
			if (TRUE==$cst_tbc['time']) echo ' checked="checked"';
			echo '> Time T.B.C. </input></td></tr></table>';
			
			echo "Duration: <input type='text' name='cst-duration-hour' value='".$event['durhour']."' size='2' /> hour:<input type='text' name='cst-duration-mins' value='".$event['durmin']."' size='2' /> minutes";
		
		}
		
	function cst_display_venue_fields(){
		
			global $post;
			wp_nonce_field( 'cst_date-fields', 'cst-date-fields_wpnonce');
			$duration=get_post_meta( $post->ID, CST_CF_PREFIX. 'duration', true );
			$venuefields=array(  'name'=>'Name of Location',
					'add1' => 'Street Address Line 1',
					'add2' => 'Street Address Line 2',
					'city' => 'City',
					'county' => 'County/Island',
					'pcode' => 'Postal /Zip Code',
					'country' => 'Country',
					'state' =>  'State/Province/Region' );
			$venuedata=get_post_meta( $post->ID, CST_CF_PREFIX. 'venue', true);
			if ($venuedata==FALSE) {$venuedata=$venuefields; foreach ($venuedata AS $fcode => $description){$venuedata[$fcode]='';} }
			echo '<table> <tR><tD> <TABLE>';
			foreach ($venuefields AS $fcode => $description) {
			
			 echo '<TR><TD>'.$description.': </TD><TD>';
			 echo '<input type="text" size="30" name="cst_venue_'.$fcode.'" id="cst_venue_'.$fcode.'"';
			 echo ' value="'.htmlspecialchars( $venuedata[$fcode]) . '" />';
			 echo '</TD></TR>';
			}
			echo '</Table></tD><tD>';
			$latlong=get_post_meta( $post->ID, CST_CF_PREFIX. 'latlong', true);
			if (FALSE<>$latlong) { 
				echo 'Geocode from address? <input type="checkbox" name="cst-geocode" />';
				$markers = ARRAY ( ARRAY ( 'lat' => $latlong['lat'], 'long' => $latlong['long'], 'title' => 'Venue Location ', 'content' => 'This is the venue location' ) );
			
				echo cst_display_google_map( $markers ,14,220,220,'venue_map','',ARRAY('admin'=>TRUE) );
			
				echo 'Latitude: <input type="text" size=30 name="cst_venue_lat" id="cst_venue_lat"';
				echo  ' value="'.htmlspecialchars( $latlong['lat']) . '" /></br>';
				echo 'Longitude: <input type="text" size=30 name="cst_venue_long" id="cst_venue_long"';
				echo  ' value="'.htmlspecialchars( $latlong['long']) . '" />';
			#	echo "latitude: ".$latlong['lat'].'&nbsp&nbsp  longitude: '.$latlong['long'];
			} else {echo "Latitude and longitude not set, geocode from address data? <input type='checkbox' name='cst-geocode' checked />";} 
			
			ECHO "</td></tr></TABLE>";
			
		}	


// it would be good to add this only to saving posts of type CST_EVENT_POST_TYPE ; 


	add_action( 'save_post', 'cst_saveDateTime', 1, 2 );
	
function cst_gmap_geocode( $address ) {  // from boj book;
    // Make Google Geocoding API URL
    $map_url = 'http://maps.google.com/maps/api/geocode/json?address=';
    $map_url .= urlencode( $address ).'&sensor=false';
    
    // Send GET request
    $request = wp_remote_get( $map_url );

    // Get the JSON object
    $json = wp_remote_retrieve_body( $request );

    // Make sure the request was succesful or return false
    if( empty( $json ) )
        return false;
    
    // Decode the JSON object
    $json = json_decode( $json );

if ($json->status<>'OK') return FALSE; // make sure you have a valid result returned. 

    // Get coordinates
    $lat = $json->results[0]->geometry->location->lat;    //latitude
    $long = $json->results[0]->geometry->location->lng;   //longitude
    
    // Return array of latitude & longitude
    return compact( 'lat', 'long' );
}
	

function cst_saveDateTime( $post_id, $post ) {
		global $cst_plugin_options;
		if (!empty($_POST) && in_array( $post->post_type, $cst_plugin_options['gms_types'] ) && check_admin_referer( 'cst_date-fields', 'cst-date-fields_wpnonce'))
			{
				if ( !current_user_can( 'edit_post', $post_id ) )
					return;
	             if (in_array( $post->post_type, ARRAY(CST_EVENT_POST_TYPE) )) {
			
			     $timestamp = strtotime( $_POST[ 'cst-date' ].' '.$_POST['cst-hour'].':'.$_POST['cst-mins']);
			     update_post_meta( $post_id, CST_CF_PREFIX . 'timestamp', $timestamp );
				
				// The TBC checkboxes 
				$cst_tbc=ARRAY();
				if (isset($_POST['cst_date_tbc'])) $cst_tbc['date']=TRUE; else $cst_tbc['date']=FALSE;
				if (isset($_POST['cst_time_tbc'])) $cst_tbc['time']=TRUE; else $cst_tbc['time']=FALSE;
							
				if ($cst_tbc<>"") { update_post_meta($post_id, CST_CF_PREFIX. 'tbc',$cst_tbc);}
				  else {delete_post_meta( $post_id, CST_CF_PREFIX . 'tbc' ); }
				
				$duration= ( (int) $_POST[ 'cst-duration-hour' ] * 60 ) + (int) $_POST['cst-duration-mins'];		
				update_post_meta($post_id, CST_CF_PREFIX . 'duration',$duration);
				} 
			
			if (in_array( $post->post_type, $cst_plugin_options['gms_types'] )) {
				
				$venuedata=array(   'name'=>$_POST['cst_venue_name'],
					'add1' => $_POST['cst_venue_add1'],
					'add2' => $_POST['cst_venue_add2'],
					'city' => $_POST['cst_venue_city'],
					'county' => $_POST['cst_venue_county'],
					'pcode' => $_POST['cst_venue_pcode'],
					'country' => $_POST['cst_venue_country'],
					'state' =>  $_POST['cst_venue_state'] );
					
				update_post_meta($post_id, CST_CF_PREFIX . 'venue',$venuedata);
					
				if (isset($_POST['cst-geocode']	)) {
					// geocode the ADDRESS;
					$address=$venuedata['name'].', '.$venuedata['add1'].', '.$venuedata['add2'].', '.$venuedata['city'].', '.$venuedata['county'].', '.$venuedata['pcode'].', '.$venuedata['country'];
					$latlong=cst_gmap_geocode( $address );		
					if ($latlong<>FALSE) {
						update_post_meta($post_id, CST_CF_PREFIX . 'latlong',$latlong);
					} else {
						delete_post_meta( $post_id, CST_CF_PREFIX . 'latlong' );
					}
				} elseif (isset($_POST['cst_venue_lat'])) {
					$latlong = ARRAY( 'lat' => (float) $_POST['cst_venue_lat'], 'long' => (float) $_POST['cst_venue_long']); 
			
				update_post_meta($post_id, CST_CF_PREFIX . 'latlong',$latlong);
				} 
			     }
			}
		}


add_shortcode('venueadd','cst_venue_add_handler');

function cst_venue_add_handler ( $attr, $content ) {
global $post;
	$venuedata=get_post_meta( $post->ID, CST_CF_PREFIX. 'venue', true);
	$returnHTML="";
	if ($venuedata<>FALSE) {
		foreach ($venuedata AS $fcode => $description) {
			if ($description<>'') $returnHTML.= "<span class='cstvenue-$fcode'> $description </span></br>";
			}
		}	
 return $returnHTML;
}


 add_shortcode('venuemap', 'cst_venue_map_handler'); 
 
 function cst_venue_map_handler( $attr, $content ) {
 
 global $post;
    // Set map default
    $defaults = array(
        'width'  => '250',
        'height' => '250',
        'zoom'   => 14,
        'map_id' => ''
    );
    // Get map attributes (set to defaults if omitted)
    extract( shortcode_atts( $defaults, $attr ) );
    
    if ($map_id=='') $map_id='cst_google_map_'."$post->ID";
    
    $latlong=get_post_meta( $post->ID, CST_CF_PREFIX. 'latlong', true);
	if (FALSE==$latlong) {
	 	return '';
	}  
	$markers = ARRAY( '$post->ID' => ARRAY ( 'lat' => $latlong['lat'], 'long' => $latlong['long'], 'title' => 'Venue Location ', 'content' => cst_marker_content() ) ) ;
	
	return '<div class="venuemap">'.cst_display_google_map ( $markers, $zoom, $width, $height, $map_id ).'</div>';
    
    // generate a unique map ID so we can have different maps on the same page
  # $map_id = 'cst_map_'.md5( $lat );
 
    } // END venuemap shortcode handler...
    
     add_shortcode('eventmap', 'cst_event_map_handler'); 
 
 function cst_event_map_handler( $attr, $content ) {
 
 global $cst_google_markers;
 static $map_nbr = 1;
    // Set map default
    $defaults = array(
        'width'  => '250',
        'height' => '250',
        'zoom'   => 9,
        'map_id' => '',
        'lat' => '',
        'long' => ''
    );
    // Get map attributes (set to defaults if omitted)
    extract( shortcode_atts( $defaults, $attr ) );
    
    if ($map_id=='') $map_id='cst_event_map_'.$map_nbr;
    $map_nbr++;
    
    if (($lat<>'') && ($long<>'')) { $centre=ARRAY ('lat' => $lat, 'long'=> $long );} else $centre='';
   
	return cst_display_google_map ( $cst_google_markers, $zoom, $width, $height, $map_id ,$centre);

    } // END eventmap shortcode handler...
  
    
 /*********************************************
 *
 *  Markers ( Array ( $lat, $long, $title, $content) ) 
 *
 *
 *******************************************************************************/
 
   function cst_display_google_map ( $markers, $zoom=10, $width=400, $height=400, $map_id='cst_google_map', $centre='', $options=ARRAY ('draggable' => FALSE) ) {
    
    
   $output='';
    // Sanitize variables depending on the context they will be printed in
    
    $zoom    = esc_js( $zoom );
    $width   = esc_attr( $width );
    $height  = esc_attr( $height );
    
    // Add the Google Maps javascript only once per page
    global $cst_gmap_script_added;
    if( $cst_gmap_script_added == false ) {
        $output .= '<script type="text/javascript"
        src="http://maps.google.com/maps/api/js?sensor=false"></script>';
        $cst_gmap_script_added = true;
    }
    
    if ($centre==='') { 
    	$lat=0;$long=0;
    	foreach ($markers as $marker) {
  	  $lat+=$marker['lat'];
   		 $long+=$marker['long'];
   	 }
      	$lat     = $lat / count($markers);
    	$long    = $long / count ($markers);
    }
    else { $lat = $centre['lat']; $long=$centre['long']; }
    
    // Add the map specific code
    
    
#    $output = "Google map here: map id = $map_id, Number of markers: ".count($markers);
    
    
    $output .= <<<CODE
    <div id="$map_id" class="cst-gmap"></div>
    
    <script type="text/javascript">
    function generate_$map_id() {
      
          var latlng = new google.maps.LatLng( $lat, $long );
        var options = {
            zoom: $zoom,
            center : latlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }

        var map2 = new google.maps.Map(
            document.getElementById("$map_id"),
            options
        );

CODE;
 
 
    $i=1;
    foreach ($markers AS $marker ) {
           
    $lat     = esc_js( $marker['lat'] );
    $long    = esc_js( $marker['long'] );
    $title = esc_html( $marker['title']);
    $legend = $marker['content'];
    
    $cst_markers_draggable= (isset($options['admin']) && ($options['admin']==TRUE) )? "draggable:true," : "";
           
  $output .= <<<CODE
  	var latlng_$i = new google.maps.LatLng( $lat, $long );
        var legend_$i = '$legend';
        var title= '$title'; 
        var infowindow_$i = new google.maps.InfoWindow({ content: legend_$i   });

        var marker_$i = new google.maps.Marker({
            position: latlng_$i,
            $cst_markers_draggable
            map: map2,
        });
        
CODE;

if (isset($options['admin']) && $options['admin']==TRUE) {  # option admin would be better. 
$output .=<<<CODE
	google.maps.event.addListener( marker_$i, 'dragend', function(event) { 
	
	   var myLatLng = event.latLng;
    var lat = myLatLng.lat();
    var lng = myLatLng.lng();
		document.getElementById('cst_venue_lat').value = lat;
		document.getElementById('cst_venue_long').value = lng; });

CODE;
} else {   
  
  $output .=<<<CODE
        google.maps.event.addListener( marker_$i, 'click', function() {
            infowindow_$i.open(map2,marker_$i);
        });
      
CODE;
}

$i++;
} // End loop through the markers;



  $output .= <<<CODE
    }

    generate_$map_id();
    
    </script>
    
    <style type"text/css">
    .map_legend{
        width:200px;
        max-height:400px;
        min-height:100px;
    }
    #$map_id {
        width: {$width}px;
        height: {$height}px;
    }
    </style>
CODE;

    return $output;
}

 
/* These filters insure only future or past events are shown as appropriate and sorted by event time */

add_filter('query_vars','cst_query_vars');
add_filter('posts_join', 'cst_meta_join' );
add_filter('posts_where', 'cst_timestamp_where' );
add_filter('posts_groupby', 'cst_event_groupby' );
add_filter("posts_orderby","cst_events_orderby");

 function cst_query_vars($vars) {
 	$vars[]="show_events";
 	return $vars;
 }
 
 
 
function cst_meta_join( $join ){
  	global $wpdb;
  	if( (get_query_var('post_type') ==CST_EVENT_POST_TYPE)|| is_tax(CST_EVENT_TAXONOMY) ) {
  		// Should I be using a LEFT JOIN?
      		$join .= "LEFT JOIN $wpdb->postmeta cstpostmeta ON ( $wpdb->posts.ID =  cstpostmeta.post_id AND cstpostmeta.meta_key='".CST_CF_PREFIX . "timestamp' ) ";
  	}
  	return $join;
}

function cst_timestamp_where( $where ){
global $wp_query;

	if(  !is_single() && (get_query_var('post_type') ==CST_EVENT_POST_TYPE) || is_tax(CST_EVENT_TAXONOMY)) {
	   	if( !isset( $wp_query->query_vars['show_events']) || (isset( $wp_query->query_vars['show_events']) && $wp_query->query_vars['show_events']=='future' )) 
	   	{
			$now=strtotime('-5 minutes'); 
			$where .= " AND (cstpostmeta.meta_value > $now) ";
                } elseif ($wp_query->query_vars['show_events']=='past') 
                {
                 	$now=strtotime('+5 minutes'); 
			$where .= " AND (cstpostmeta.meta_value < $now) ";
                } elseif ($wp_query->query_vars['show_events']=='all') {  
                  	return $where; // ie. do nothing! i.e. show all events past and future;
                	
                }    	
  	}      
  	return $where;
}

function cst_event_groupby( $groupby ){
	global $wpdb;
   	if( (get_query_var('post_type') <>CST_EVENT_POST_TYPE)) {
    		return $groupby;
  	}
  	// we need to group on post ID
	$mygroupby = "{$wpdb->posts}.ID";
	if( preg_match( "/$mygroupby/", $groupby )) {
    		// grouping we need is already there
    		return $groupby;
  	}
	if( !strlen(trim($groupby))) {
    		// groupby was empty, use ours
    		return $mygroupby;
  	}
	  // wasn't empty, append ours
  	return $groupby . ", " . $mygroupby;
}

 function cst_events_orderby( $orderby ){
 	global $wpdb;
	if(  (get_query_var('post_type') ==CST_EVENT_POST_TYPE)|| is_tax(CST_EVENT_TAXONOMY)) {
	
		if (!is_admin() || (strpos($orderby,'post_date')>0) ) {  // if it isn't and admin page OR it is an admin page and  orderby is not already set...;
			if (get_query_var('show_events') =='past') {
				$orderby= "cstpostmeta.meta_value DESC";
			} else {
    				$orderby= "cstpostmeta.meta_value ASC";
    			}
  		}
  	}
  	return $orderby;
}

 
 function cst_display_date(){ 
 	global $post;
 	$timestamp=get_post_meta( $post->ID, CST_CF_PREFIX.'timestamp', true );
	$cst_tbc=get_post_meta( $post->ID, CST_CF_PREFIX.'tbc', true );
	if ($timestamp==null) return FALSE;
	if (null==$cst_tbc) {$cst_tbc=ARRAY( 'time' => FALSE, 'date' => FALSE );}
 	$outputHTML='<span class="event-date"><span class="event-year">';
 	$outputHTML.=date("Y",$timestamp).'</span>';
	if (TRUE==$cst_tbc['date']) $outputHTML.='<span class="event-dayname"> Date </span><span class="event-dayname">T.B.C.</span>'; 
	else {$outputHTML.='<span class="event-dayname">'.date("D",$timestamp).'</span>';
		$outputHTML.='<span class="event-day">'.date("d",$timestamp).'</span>';
	} 
		$outputHTML.='<span class="event-month">'.date("M",$timestamp).'</span>';
	$outputHTML.='</span>';
	return $outputHTML;
 	
 }
 
 
 if ( $cst_plugin_options['filter_etitle']) add_filter( 'the_title' , 'cst_filter_title',100,2);
 
 function cst_filter_title ($title, $id) {
 	global $post;
 	$cst_plugin_options=get_option('cst_plugin_options');
 	$dateformat=$cst_plugin_options['filter_format']; // Could store options as to where to filter title; 
 	
# could add title only to certain types of page...; 	
 #	if (in_the_loop() && ($post->post_type==CST_EVENT_POST_TYPE) && (is_tax() || is_singular(CST_EVENT_POST_TYPE))) {
  
  
  	if (in_the_loop() && ($post->post_type==CST_EVENT_POST_TYPE)) {
 		$timestamp=get_post_meta( $id, CST_CF_PREFIX.'timestamp', true );
		$cst_tbc=get_post_meta( $id, CST_CF_PREFIX.'tbc', true );
		if (null==$timestamp) return $title; 
  		$title=date($dateformat, $timestamp).$title;
 	}
 	return $title;
 }
 
 
 function cst_event_time ($format='g:ia') {
  global $post;
     $timestamp=get_post_meta( $post->ID, CST_CF_PREFIX.'timestamp', true );
     if (null==$timestamp) 
     	return ""; 
     else
     	return date($format, $timestamp);
 }
 
 function cst_event_date ($format='Y-m-d'){ return cst_event_time( $format );}
 
 function cst_template_redirect () {
 
  	if ( is_singular(CST_EVENT_POST_TYPE)) { 
    		include(  WP_PLUGIN_DIR.'/cs-tools/templates/single-cst_event.php');
		exit;
	} elseif (is_tax(CST_EVENT_TAXONOMY)) {
			include(  WP_PLUGIN_DIR.'/cs-tools/templates/taxonomy-cst_event-cats.php');
		exit;
	} elseif (get_query_var('post_type') ==CST_EVENT_POST_TYPE){
		include(  WP_PLUGIN_DIR.'/cs-tools/templates/archive-cst_event.php');
		exit;
	}
	
}

 if ( $cst_plugin_options['redirect_templates']) add_action('template_redirect', 'cst_template_redirect');
 
 
 
 
 
 /******
 ** The next funtion displays future events. 
 ** 
 *******/
  
add_shortcode('FutureEvents','display_events_shortcode_handler'); // [FutureEvents] shortcode will display a list of future events in a page or post;
add_shortcode('DisplayEvents','display_events_shortcode_handler');
add_shortcode('ListEvents','list_events_shortcode_handler');

/*****************************************************************************
** Display Events functions
** ARGUMENTS past = TRUE/FALSE - show past or future events; cats= slug or array of slugs...
** 
** 
*********************************************************************************/

function cst_event_layout() {
	$title = get_the_title();
	global $post;
	$outputHTML="\n\n <div id='post-".get_the_ID()."'>";
	#.post_class().'>';
	$outputHTML.='<div class="event-header">';
	$outputHTML.=cst_display_date(); 
	$link = get_permalink(); $event_time = cst_event_time();
	$outputHTML.= "<div class=\"event-title\">";
	$outputHTML.="<h2><span class='event-time'> $event_time </span>";
	$outputHTML.= '<a href="'.$link.'" rel="bookmark">'.$title.'</a> </h2></div>';
	ob_start (); 
		the_terms( $post->ID, 'cst_event-cats', 'Posted under:', ', ', ' ' );
	$outputHTML.=ob_get_contents();
  	ob_end_clean();  
	$outputHTML.=' </div> <div class="event-content">';
	$latlong=get_post_meta( $post->ID, CST_CF_PREFIX. 'latlong', true);
	if (FALSE<>$latlong) { 
		$markers = ARRAY ( ARRAY ( 'lat' => $latlong['lat'], 'long' => $latlong['long'], 'title' => 'Venue Location ', 'content' => 'This is the venue location' ) );
			
###				$outputHTML.= cst_display_google_map( $markers, , );
		}
	global $more;
	$more = 0;
	$outputHTML.=do_shortcode( get_the_content("Further information ... >>")); 
	#$outputHTML.=edit_post_link(); 
	$outputHTML.='</div> </div>';
	return $outputHTML;
}

function cst_list_event() {
	$title = get_the_title();
	global $post;
	$link = get_permalink(); $event_time = cst_event_time('d M Y g:ia');
	$outputHTML="\n\n <li><span class='event-time'> $event_time </span></br>";
	$outputHTML.= '<a href="'.$link.'" rel="bookmark">'.$title.'</a> </li>';
	
	return $outputHTML;
}

function cst_marker_content() {
	$title = get_the_title();
	global $post;
	$link = get_permalink(); $event_time = cst_event_time('d M Y g:ia');
	$outputHTML='<div><span class="event-time">'.$event_time.' </span></br>';
	$outputHTML.= '<a href="'.$link.'" rel="bookmark">'.$title.'</a> </div>';
	
	#NB output can't include any single quotes...;
	
	return $outputHTML;
}

/*****************************************************************************
** Display Events Shortcode
** ARGUMENTS past = TRUE/FALSE - show past or future events; cats= slug or array of slugs...
** 
** 
*********************************************************************************/

 function display_events_shortcode_handler ( $atts, $content = null) {

	extract(shortcode_atts(array(
		'past' => FALSE,
		'cats' => '',
		'max' =>'',
	), $atts));
	global $cst_google_markers,$cst_gmap_script_added;
	$script_added=$cst_gmap_script_added; $cst_gmap_script_added=TRUE;
   	$outputHTML=cst_display_events ( $cats, $past, $max, 'cst_event_layout');
	if (FALSE==$outputHTML) $outputHTML.="There are currently no confirmed upcoming events - please check back later!"; 
	else { 
	$cst_gmap_script_added=$script_added;
	
	$mapHTML=cst_display_google_map($cst_google_markers, 5, 500,300,'map_of_events');
	$outputHTML=$mapHTML."<div style='cst_event_list'>".$outputHTML."</div>";
	} 
	 return $outputHTML;
}

 function list_events_shortcode_handler ( $atts, $content = null) {

	extract(shortcode_atts(array(
		'past' => FALSE,
		'cats' => '',
		'max' =>'',
	), $atts));

   
	$outputHTML=cst_display_events ( $cats, $past, $max, 'cst_list_event');
	if (FALSE==$outputHTML) $outputHTML.="There are currently no confirmed upcoming events - please check back later!"; 
	else $outputHTML="<div style='cst_event_list'><ul>".$outputHTML."</ul></div>";
	
	 return $outputHTML;
}

function cst_display_events ( $cats='', $past=FALSE, $max=7, $callback='') {

global $cst_google_markers;
# pass grace period as a parameter?? 
 $cst_now=($past) ? strtotime( '+30 minutes') : strtotime( '-30 minutes');  # 30 minutes grace either side...

$outputHTML="";

  $args=array(
   	'post_type' => CST_EVENT_POST_TYPE,
  	'meta_key'=> CST_CF_PREFIX . 'timestamp',
   	'orderby' => 'meta_value_num',
  	 'order'=> ($past) ? 'DESC': 'ASC',
	'meta_query' => array(
			array(
				'key' => CST_CF_PREFIX . 'timestamp',
				'value' => $cst_now,
				'type' => 'NUMERIC',
				'compare' =>  ($past) ? '<' : '>'
				)
			)     
 	 );


if (''<>$cats) {

$slug= ( (int) $cats== $cats) ? 'id' : 'slug';

 
 $args['tax_query'] = array(
		  array(
			'taxonomy' => CST_EVENT_TAXONOMY,
			'field' => $slug,
			'terms' => explode(',',$cats)
			)
		);
}

if (0<> (int) $max) $args['posts_per_page']=$max;
#if ($past) $args['show_past_events']=TRUE;

  $event_query = new WP_query($args);
  // Assign predefined $args to your query
  $event_query->query($args);
 
// Loop through results

if ($event_query->have_posts()) :{ 
  while ($event_query->have_posts()) : 
  global $post;
  	$event_query->the_post();
  
  	$outputHTML.=call_user_func($callback);
  	# perhaps we should also build a map KML file or placeholder as we go;
  	$latlong=get_post_meta( $post->ID, CST_CF_PREFIX. 'latlong', true);
	if (FALSE<>$latlong) { 
		$marker_content=cst_marker_content();
		$marker = ARRAY ( 'lat' => $latlong['lat'], 'long' => $latlong['long'], 'title' => 'Venue Location ', 'content' => $marker_content );
		$cst_google_markers["$post->ID"] = $marker;
  	}
  endwhile;
}
else :
// do stuff for no results
wp_reset_query();
return FALSE;
endif;

// RESET THE QUERY
wp_reset_query();

 return $outputHTML;
}

class CST_List_Event_Widget extends WP_Widget {
    /** constructor */
    function CST_List_Event_Widget() {
        parent::WP_Widget(false, $name = 'List Events');
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        ?>
              <?php $outputHTML="\n\n".$before_widget; ?>
                  <?php if ( $title )
                        $outputHTML.="\n".$before_title . $title . $after_title."\n"; ?>                 
                         <?php
                  
                  $cats = ($instance['showcat']==TRUE) ? $instance['cat'] : '';
                
                 $eventHTML=cst_display_events( $cats, $instance['past'], $instance['max'], 'cst_list_event' );
                 
              
                if ((FALSE==$eventHTML) ) $outputHTML='';
                else $outputHTML.='<ul class ="event">'.$eventHTML.'</ul>'.$after_widget;;
                 
     echo $outputHTML;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['showcat'] =  strip_tags($new_instance['showcat']);
	$instance['past'] = (boolean) $new_instance['past'];
	$instance['max'] = (int) $new_instance['max'];
	$instance['cat'] = (int) $new_instance['cat'];
	if ($instance['max']==0) $instance['max']='';
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
    $defaults = ARRAY ( 'title' => 'Upcoming Events', 'showcat'=>FALSE, 'cat'=> 0,  'past'=> FALSE, 'max' => '' );
        $instance= wp_parse_args( (array) $instance, $defaults ); 
        $title = esc_attr($instance['title']);
        $showcat = (boolean) $instance['showcat'];
        $past = (boolean) $instance['past'];
        $cat = (int) $instance['cat'];
        $max = $instance['max'];
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
          <p>
          <input id="<?php echo $this->get_field_id('past'); ?>" name="<?php echo $this->get_field_name('past'); ?>" type="checkbox" <?php checked($past, TRUE);?> />
          <label for="<?php echo $this->get_field_id('max'); ?>"> <?php _e('Show past (instead of future) events'); ?></label>
         </p><p> 
          <input id="<?php echo $this->get_field_id('showcat'); ?>" name="<?php echo $this->get_field_name('showcat'); ?>" type="checkbox" <?php checked($showcat, TRUE);?> />
         <label for="<?php echo $this->get_field_id('showcat'); ?>">  <?php _e('Show only event Category:'); ?></label> 
      
        <?php 
     wp_dropdown_categories( 'taxonomy='.CST_EVENT_TAXONOMY.'&hide_empty=0&show_count=1&selected='.$cat.'&id='.$this->get_field_id('cat').'&name='.$this->get_field_name('cat') );
 ?>
           </p>
           <p>
          <label for="<?php echo $this->get_field_id('max'); ?>"><?php _e('Max to display:'); ?></label> 
          <input  id="<?php echo $this->get_field_id('max'); ?>" name="<?php echo $this->get_field_name('max'); ?>" type="text" size='2' value="<?php echo $max; ?>" />
        </p>
        <?php 
    }

} // CST_List_Event_Widget


add_action('widgets_init', create_function('', 'return register_widget("CST_List_Event_Widget");'));


/*****************************************************************************
** The function is added to via the init hook above and produced an ical feed
** example.com/feed/ical 
** Should loop thought hte upcoming events like above shortcode but output in ical format. 
** Check output in an iCal validator on the web... TO DO 
BEGIN:VEVENT
DTSTART:20010714T170000Z
DTEND:20010715T035959Z
SUMMARY:Bastille Day Party
ATTACH;FMTTYPE=image/jpeg:http://domain.com/images/bastille.jpg
END:VEVENT


*********************************************************************************/

function cst_output_ical_feed () {
	header('Content-Type:application/iCal') ; 
	$outputTXT = <<<EOL
BEGIN:VCALENDAR\r
PRODID:-//CSTOOLS//EN\r
VERSION:2.0\r
CALSCALE:GREGORIAN\r
METHOD:PUBLISH\r\n
EOL;
	
	$outputTXT.= cst_display_events ( '', FALSE, 5, 'cst_output_event_ical');
	$outputTXT.=<<<EOL
END:VCALENDAR	\r\n
EOL;

echo $outputTXT;

}

function cst_output_event_ical () {
  global $post;
        $timestamp=get_post_meta( $post->ID, CST_CF_PREFIX.'timestamp', true );
        $duration=get_post_meta( $post->ID, CST_CF_PREFIX. 'duration', true );
	$outputTXT="BEGIN:VEVENT\r\n";
	$link=get_permalink();
	$outputTXT.="UID:".$link."\r\n";
	$outputTXT.='DTSTART:'. date('Ymd', $timestamp).'T'.date('Hi', $timestamp)."00Z\r\n";
	$outputTXT.='DTEND:'. date('Ymd', ($timestamp+(60*$duration))).'T'.date('Hi', ($timestamp+(60*$duration)))."00Z\r\n";
	$outputTXT.='SUMMARY:'.get_the_title()."\r\n";
	$outputTXT.="END:VEVENT\r\n";
	
	return $outputTXT;
}


/*****************************************************************************
** Add  contextual help for Events 
** This filter adds contextual help to admin screens
** It probably only needs to be loaded to admin pages... ACTION TO DO 
*********************************************************************************/
add_action( 'contextual_help', 'cst_add_help_text', 10, 3 );

function cst_add_help_text($contextual_help, $screen_id, $screen) { 
 # $contextual_help .= var_dump($screen); // use this to help determine $screen->id
  if (CST_EVENT_POST_TYPE == $screen->id ) {
    $contextual_help =<<<EOD
    <img src="http://christianscienceplugins.net/logos/cs-plugin-logo.png" style="float:left; width:100px; padding-right:30px;"/>
     <div> <p>Things to remember when adding or editing an event:</p>
<ul>
<li>Make sure you sent the event date, time  and duration. (Tick T.B.C.=To Be Confirmed if you are not sure of the date or time).</li>
<li>Enter the Venue details.  Then when you save, or publish the event the latitude and longitude will be looked up on Google. If an address if found a google map appears you can drag the marker to a new location to fine-tune the location. Don&#8217;t forget to save your changes! (You can recode the location from the address if it changes).</li>
<li>Select one or more Event Categories under which to list your event.</li>
</ul></div>
<p>You can use the following Shortcodes within your event post:</p>
<p>{venuemap} &#8211; to display a map of venue location.</p>

<p>More coming in next release&#8230;</p>
<p>For more information vist <a href="http://christianscienceplugins.net" target="_blank">christianscienceplugins.net</a></p>

EOD;
  } elseif ( 'edit-'.CST_EVENT_POST_TYPE == $screen->id ) {
    $contextual_help = <<<EOD
    <img src="http://christianscienceplugins.net/logos/cs-plugin-logo.png" style="float:left; width:100px; padding-right:30px;"/>
<p>This is the list of events. If you click to sort by publication date it actually sorts by event date. (Sorry it is the only way at present I can sort up event date).</p>
<p>The &#8220;show all dates&#8221; dropdown filters on <em>publication</em> date.On the other hand, the &#8220;Show upcoming events&#8221; dropdown filters on<em> event</em> date and displays future, past or all events.</p>
<p>For more information vist <a href="http://christianscienceplugins.net" target="_blank">christianscienceplugins.net</a></p>

EOD;
  }
  return $contextual_help;
}
?>