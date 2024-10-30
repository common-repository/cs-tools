<?php
/*
Plugin Name:CS Tools 
Plugin URI: http://www.christianscienceplugins.net/
Description: This plugin provides tools for Christian Science branch churches, Christian Scientists and others who wish to promote Christian Science products and activities on their websites. It is being developed as part of the "Field Websites - Global Collaboration". 
Author: Daniel Scott 
Version: 0.6.1

Author URI: http://www.christianscienceplugins.net/
*/ 

// turn on error checking...
# ini_set('display_errors',1);
# error_reporting(E_ALL);




#DEFINE( 'CST_EVENT_TAXONOMY', 'cst_event-cats');  // was 'cst_event-cats'; These should be changed once plugin installed!;
#DEFINE( 'CST_EVENT_POST_TYPE', 'cst_event');   // was 'cst_event';
#DEFINE( 'CST_CF_PREFIX','_cst_'); // should be '_cst_'

#Define global variables 
$cst_pluginName="Tools for Christian Science Websites";
$cst_pluginDir="cs-tools";
$cst_Repository="http://christianscienceplugins.net/repository/";
$cst_iframes="http://christianscience.com/includes/iframes/";
$cst_scripts="http://christianscience.com/includes/rr-scripts/";

function cst_setup_options_etc ()
{
	
  	add_option('cst_plugin_options', ARRAY( 'inc_css' => TRUE, 'filter_etitle' => FALSE, 'redirect_template' => TRUE, 'filter_format' => 'Y-m-d @ H:i  :',
						'ept_slug' => 'events', 'etax_slug' => 'future' , 'past_etax_slug' => 'past', 'flush_rw' => TRUE )); 
	update_option('cst_flush_rw', TRUE);					
  
	/** These options are for event categories - add have been deprecated! as of version 0.6 
	add_option('cst_events_cat', ''); // this stores the furture event category - only future dated posts can remain in this category see events-tools.php
	add_option('cst_events_cats_map', ARRAY ( 'nothing' => 'nothing' ) ); // This should be blank - filled in for testing 
	
	***/ 
	
 	/** These options are for the service times **/
	add_option('cst_service_details', ARRAY( 'sun' => ARRAY ('name' => 'Sunday Service', 'day'=>'0', 'hour' => '11', 'mins'=>'00', 'Dcode' => 'public',
	                                                               'weeks' => '11011', 'next_ts'=>'' ),
	                                         "wed" => ARRAY ('name' => 'Wednesday Testimony Meeting', 'day'=>'3', 'hour' => '19', 'mins' => '00',  
                                                                     'Dcode' => 'public', 'weeks' => '11110', 'next_ts' => '') ) ) ;
   #     add_option('cst_service_types', ARRAY ('Church'=>'','RR'=>'','Members'=>''));   
   
   /** These options store upcoming services and meetings cache **/
        add_option('cst_upcoming_Church', ARRAY('empty') ) ;
        add_option('cst_upcoming_RR', ARRAY('empty') ) ;
        add_option('cst_upcoming_Members', ARRAY('empty') ) ;     
        
        
        flush_rewrite_rules();                                                    
}

register_activation_hook(__FILE__,'cst_setup_options_etc');
register_deactivation_hook(__FILE__,'cst_deactivation');

function cst_deactivation (){
flush_rewrite_rules();
}

#Should really add a uninstall function to remove the options on uninstall. 

# Functionality is split into files - so the folks can work on plugin in a modula way. 

 include('branchtools.php');  // services times, hopefully other stuff like rotas/rosters, reading room shoping carts etc. ;
 include('event_tools.php'); // tools for listing future events e.g. lectures ;

# ----------------------------------- QUOTING FROM THE BOOKS SHORTCODES ---------------------------------------------

function display_biblequote_shortcode_handler ( $atts, $content = null){
   extract( shortcode_atts( array('ref' => 'John 1:1'), $atts ) );
if (null ==$content) {$returnHTML="<a href='http://www.spirituality.com/dt/book_lookup.jhtml?reference=$ref' class='qlink'>   $ref </a>";} 
else { 
  $returnHTML="<div class='csquote'><div class='biblequote'> ";
  $returnHTML.=$content;
  $returnHTML.="<div class='qatt'> From ";
  $returnHTML.="<a href='http://christianscience.com/publications/bible/' class='book' target='bible'>The Bible </a> : ";
  $returnHTML.="<a href='http://www.spirituality.com/dt/book_lookup.jhtml?reference=$ref' class='qlink' target='bquote' title='Read this quote in contect on spirituality.com' alt=title='Read this quote in contect on spirituality.com'> $ref </a></div>";
 $returnHTML.="</div></div>";
}
return $returnHTML;
}

function display_sandh_shortcode_handler ( $atts, $content = null){
   extract( shortcode_atts( array('ref' => 'i:1'), $atts ) );
   $link="<a href='http://www.spirituality.com/dt/book_lookup.jhtml?reference=$ref' class='SHlink'>"; 
if (null ==$content) {$returnHTML=$link."   S&H $ref </a>";} 
else { 
  $returnHTML="<div class='csquote'><div class='shquote'>";
  $returnHTML.=$content;
  $returnHTML.="<div class='qatt'>From ";
  $returnHTML.="<a href='http://scienceandhealth.com' target='book' class='book' title='Science and Health with Key to the Scriptures'> Science and Health</a> <br/>by ";
  $returnHTML.="<a HREF='http://www.marybakereddylibrary.org/' target='mbe' class='author' title='Mary Baker Eddy - The Discover and Founder of Christian Science'>Mary Baker Eddy</a> : ";
  $returnHTML.="<a href='http://www.spirituality.com/dt/book_lookup.jhtml?reference=$ref' class='qlink' target='bquote' alt='Read the full quote in context on spirituality.com' title='Read the full quote in context on spirituality.com'> p$ref </a></div>";
  $returnHTML.="</div></div>";
}
return $returnHTML;
}

function display_csquote_shortcode_handler ( $atts, $content = null){
   extract( shortcode_atts( array('ref' => 'i:1'), $atts ) );
  if (null ==$content) {$returnHTML="";} // Do nothing; 
else { 
  $returnHTML="<div class='csquote'><div class='shquote'>";
  $returnHTML.=$content;
  $returnHTML.="<div class='qatt'>From $ref </div>";
  $returnHTML.="</div></div>";
}
return $returnHTML;
}

add_shortcode('csquote','display_csquote_shortcode_handler');

add_shortcode('CSQuote','display_csquote_shortcode_handler');
add_shortcode('Bible','display_biblequote_shortcode_handler');
add_shortcode('SH','display_sandh_shortcode_handler');
add_shortcode('S&H','display_sandh_shortcode_handler');
# ----------------------------------- PERIODICAL SHORT CODES ---------------------------------------------


function display_cover_shortcode_handler ( $atts, $content = null){


   extract( shortcode_atts( array('p' => 'journal', 'size'=>'', 'style'=>'', 'css_class'=>'' ), $atts ) );
  if ('small'==$size) {$pcode=$p.'-small';} else {$pcode=$p;}

return "<div style='$style' class='pcover $css_class'><script src='http://christianscience.com/includes/rr-scripts/$pcode.js' type='text/javascript'></script></div>";
}

add_shortcode('cover','display_cover_shortcode_handler');

# These are very repetative - ther must be a much better way to do this with functions with variable names etc. 

function display_journal_shortcode_handler( $atts, $content = null){
global $cst_iframes;
 return '<iframe src="'.$cst_iframes.'journal-web-display.html" width="450" height="230" scrolling="no" wmode="transparent" frameborder="0" allowtransparency="true"></iframe>';
}

function display_sentinel_shortcode_handler( $atts, $content = null){
global $cst_iframes;
 return '<iframe src="'.$cst_iframes.'sentinel-web-display.html" width="450" height="200" scrolling="no" wmode="transparent" frameborder="0" allowtransparency="true"></iframe>';
}

function display_lesson_shortcode_handler( $atts, $content = null){
global $cst_iframes;
 return '<iframe src="'.$cst_iframes.'bible-lesson-web-display.html" width="450" height="180" scrolling="no" wmode="transparent" frameborder="0" allowtransparency="true"></iframe>';
}

function display_monitor_shortcode_handler( $atts, $content = null){
global $cst_iframes;
 return '<iframe src="'.$cst_iframes.'monitor-web-display.html" width="450" height="200" scrolling="no" wmode="transparent" frameborder="0" allowtransparency="true"></iframe>';
}

function display_spanish_shortcode_handler ( $atts, $content = null){
global $cst_iframes;
return '<iframe src="'.$cst_iframes.'heraldo-web-display.html" width="450" height="200" scrolling="no" wmode="transparent" frameborder="0" allowtransparency="true"></iframe>';
}
function display_inspanish_shortcode_handler ( $atts, $content = null){
global $cst_iframes;
return '<iframe src="'.$cst_iframes.'spanish-web-display.html" width="450" height="200" scrolling="no" wmode="transparent" frameborder="0" allowtransparency="true"></iframe>';
}

function display_french_shortcode_handler ( $atts, $content = null){
global $cst_iframes;
return '<iframe src="'.$cst_iframes.'french-web-display.html" width="450" height="200" scrolling="no" wmode="transparent" frameborder="0" allowtransparency="true"></iframe>';
}

function display_inFrench_shortcode_handler ( $atts, $content = null){
global $cst_iframes;
return '<iframe src="'.$cst_iframes.'french-chretienne-web-display.html" width="450" height="205" scrolling="no" wmode="transparent" frameborder="0" allowtransparency="true"></iframe>';
}

function display_port_shortcode_handler ( $atts, $content = null){
global $cst_iframes;
return '<iframe src="'.$cst_iframes.'port-web-display.html" width="450" height="200" scrolling="no" wmode="transparent" frameborder="0" allowtransparency="true"></iframe>';
}

function display_port2_shortcode_handler ( $atts, $content = null){
global $cst_iframes;
return '<iframe src="'.$cst_iframes.'port2-web-display.html" width="450" height="200" scrolling="no" wmode="transparent" frameborder="0" allowtransparency="true"></iframe>';
}

function display_german_shortcode_handler ( $atts, $content = null){
global $cst_iframes;
return '<iframe src="'.$cst_iframes.'german-web-display.html" width="450" height="200" scrolling="no" wmode="transparent" frameborder="0" allowtransparency="true"></iframe>';
}

function display_german2_shortcode_handler ( $atts, $content = null){
global $cst_iframes;
return '<iframe src="'.$cst_iframes.'german2-web-display.html" width="450" height="200" scrolling="no" wmode="transparent" frameborder="0" allowtransparency="true"></iframe>';
}

add_shortcode('CSJournal','display_journal_shortcode_handler');
add_shortcode('CSSentinel','display_sentinel_shortcode_handler');
add_shortcode('CSLesson','display_lesson_shortcode_handler');
add_shortcode('CSMonitor','display_monitor_shortcode_handler');
add_shortcode('CSHSpanish','display_spanish_shortcode_handler');
add_shortcode('CSHinSpanish','display_inSpanish_shortcode_handler');
add_shortcode('CSHFrench','display_french_shortcode_handler');
add_shortcode('CSHinFrench','display_inFrench_shortcode_handler');
add_shortcode('CSHPortuguese','display_port_shortcode_handler');
add_shortcode('CSHinPortuguese','display_port2_shortcode_handler');
add_shortcode('CSHGerman','display_german_shortcode_handler');
add_shortcode('CSHinGerman','display_german2_shortcode_handler');

# ////////////////////////////////////// TMC Site logo links ///////////////////////////

function display_csdotcom_shortcode_handler ( $atts, $content = null){
return '<A HREF="http://christianscience.com" target = "_blank"><img src="http://www.christianscience.com/images/logos/cs-logo-small.gif" height="56" width="209" alt="christianscience.com site logo" style="border: 1px #000;"></A>';
}
function display_spdotcom_shortcode_handler ( $atts, $content = null){
return '<A HREF="http://spirituality.com" target = "_blank"><img src="http://www.spirituality.com/logos/2009/spirituality-logo-web-sm.jpg" border="0"></A>';
}
function display_tmcydotcom_shortcode_handler ( $atts, $content = null){
return '<A HREF="http://tmcyouth.com" target = "_blank"><img src="http://www.tmcyouth.com/global/images/logo.png" border="0" style="background: #000; padding:3px;"></A>';
}

function display_mbeldotcom_shortcode_handler ( $atts, $content = null){
return '<A HREF="http://marybakereddylibrary.org" target = "_blank"><img src="http://www.marybakereddylibrary.org/themes/library/images/MBE_logo.gif" border="0"></A>';
}

function display_csmdotcom_shortcode_handler ( $atts, $content = null){
return '<A HREF="http://csmonitor.com" target = "_blank"><img src="http://www.csmonitor.com/extension/csm_base/design/csm_design/images/csmlogo_179x46.gif" border="0" style="background: #000"></A>';
}
add_shortcode('CS.com','display_csdotcom_shortcode_handler');
add_shortcode('SP.com','display_spdotcom_shortcode_handler');
add_shortcode('TMCY.com','display_tmcydotcom_shortcode_handler');
add_shortcode('MBEL.org','display_mbeldotcom_shortcode_handler');
add_shortcode('MBEL.com','display_mbeldotcom_shortcode_handler');
add_shortcode('CSM.com','display_csmdotcom_shortcode_handler');



function cslogo_shortcode_handler ( $atts, $content = null){
 	extract( shortcode_atts( array( 'l'=> 'cstools' ), $atts ) );
 	global $cst_Repository;
	switch ($l) {
	case 'branch':
   		return '<A href="http://christianscience.com/church/branch-churches/" target="new"><img alt="A Branch of The First Church of Christ, Scientist" src="'.$cst_Repository.'/logos/branch.jpg" style="width:220px"/></a>';
	case 'prac': case 'practitioner' :
   		return '<A href="http://christianscience.com/healing/christian-science-practitioner/" target="new"><img alt="A Practitioner listed in the Christian Science Journal" src="'.$cst_Repository.'/logos/journal-listed.jpg" style="width:220px"/></a>';
	case 'teacher': 
   		return '<A href="http://christianscience.com/teachers/" target="new"><img alt="An authorised teacher of Christian Science" src="'.$cst_Repository.'/logos/teacher.jpg" style="width:220px"/></a>';
	case 'lecturer':
   		return '<A href="http://christianscience.com/lectures/" target="new"><img alt="A Practitioner listed in the Christian Science Journal" src="'.$cst_Repository.'/logos/lecturer.jpg" style="width:220px"/></a>';
	case 'fwgc' : 
		return '<div><A HREF="http://christianscienceplugins.net/" target="_new"><img src="'.$cst_Repository.'/logos/webfieldslogo.gif" style="width:175px; padding: 10px 10px 5px 5px" /></A></div>';
	case 'cstools' : 
		return '<div><A HREF="http://christianscienceplugins.net/" target="_new"><img src="'.$cst_Repository.'/logos/cs-plugin-logo.png" style="width:175px; padding: 10px 10px 5px 5px" /></A></div>';
	
	default: 
	}
}
add_shortcode('CSLogo','cslogo_shortcode_handler');

class CST_Logo_Widget extends WP_Widget {
#    /** constructor 
    function CST_Logo_Widget() {
        parent::WP_Widget(false, $name = 'CS Logos');
    }

    #/** @see WP_Widget::widget 
    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
     	 $outputHTML="\n\n".$before_widget; 
                    if ( $title )  { $outputHTML.="\n".$before_title . $title . $after_title."\n";}
               		$outputHTML.=cslogo_shortcode_handler ($instance);
               		$outputHTML.=$after_widget;
                 
     echo $outputHTML;
    }

    #@see WP_Widget::update 
    function update($new_instance, $old_instance) {
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['l'] =  strip_tags($new_instance['l']);
	$instance['width'] = (int) $new_instance['width'];
	$instance['height']= (int) $new_instance['height'];
	
        return $instance;
    }

    /** @see WP_Widget::form **/
    function form($instance) {
    $defaults = ARRAY ( 'title' => '', 'l'=>'cstools', 'width'=>'220', 'height'=>'40' );
        $instance= wp_parse_args( (array) $instance, $defaults ); 
        $title = esc_attr($instance['title']);
        $l = esc_attr( $instance['l']);
        $width = (int) $instance['width'];
        $height = (int) $instance['height'];
             
             echo "l=$l";
              ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
          <p>
          <select class="classform" id="<?php echo $this->get_field_id('l'); ?>" name="<?php echo $this->get_field_name('l'); ?>" >
          
         	<option value='prac' 	<?php selected( $l, 'prac');?> > Journal listed CS Practitioner </option> 
         	<option value='branch' <?php selected( $l, 'branch'); ?> > CS Branch Church or Society </option> 
         	<option value='teacher' <?php selected( $l, 'teacher'); ?> > Authorised CS Teacher </option> 
         	<option value='lecturer' <?php selected( $l, 'lecturer'); ?> > Member of the CS Board of Lectureship </option> 
         	<option value='fwgc' <?php selected( $l, 'fwgc'); ?> > Field Websites - Global Collaboration </option> 
         	<option value='cstools' <?php selected( $l, 'cstools'); ?> > CSTools (Please display!) </option> 
         	
         </select> 
          </p>
          <p>
          <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:'); ?></label> 
          <input  id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" size='3' value="<?php echo $width; ?>" />
         <label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('x height:'); ?></label> 
          <input  id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" size='3' value="<?php echo $height; ?>" />
        </p>
         
        <?php 
    
    }

 
} // CST_Logo_Widget
add_action('widgets_init', create_function('', 'return register_widget("CST_Logo_Widget");'));

# /// The Daily Lift - other functionality for lecture related content to be added later.
# It would be very nice to be able to link to a particular day's daily lift. 
# 


function display_dailylift_shortcode_handler ( $atts, $content = null){
global $cst_Repository;
 
    return '<img alt="The Daily Lift from the Christian Science Board of Lectureship" src="'.$cst_Repository.'dailylift.jpg" />';
}

add_shortcode('DailyLift','display_dailylift_shortcode_handler');

/***************************************************
**   TRM Shortcodes
**
*******************************************************/ 

function TRMW_shortcode_handler ( $atts, $content = null){
 	extract( shortcode_atts( array( 'code'=> 'Reg', 'params' => '','width'=>'0', 'height'=>'0' ,'fn'=>'0', ), $atts ) );
// could add an option on admin page to set default FN (and size options?); 
	$scroll='auto';
	switch ($code) {
		case 'REG' :
		case 'Reg' :
			$defaultWidth=500;
			$defaultHeight=800;
			$code='widgets';
			break;
	# The following widgets are auto-scrolling or non-scrolling articles depending on if $fn/FN is set; 		
		case 'BB01': case 'CE01': case 'CW01': case 'FH01': case 'FS01' : 
		case 'GH01': case 'HT01': case 'JN01': case 'MN01': case 'PH01': 
		case 'RE01': case 'SE01': case 'SH01': case 'SI01': 
		case 'SP01': case 'WE01': case 'YO01':
			$defaultWidth=500;
			$defaultHeight=350;
			$scroll='no';
			break;
	# The following widgets are non-scrolling articles; 
		case 'LS01': case 'ME01': case 'SA01': case 'YQ01': 
			$defaultWidth=350;
			$defaultHeight=450;
			break;
	# This is the schedule or lecturers; 
		case 'LE01':
			$defaultWidth=260;
			$defaultHeight=450;
			$scroll='auto';
			break;
			
		case 'MN02': case 'MN03' : case 'MN04': case 'MN05' : case 'MN06': case 'MN07' : case 'MN08': case 'MN09': 
			$defaultWidth=260;
			$defaultHeight=140;
			break;
		case 'BL01':	
			$defaultWidth=430;
			$defaultHeight=170;
			break;
		case 'CH01':
			$defaultWidth=485;
			$defaultHeight=220;
			break;
		case 'CS01':  case 'NC01' : case 'QT01' :
			$defaultWidth=330;
			$defaultHeight=200;
			break;
		case 'LV01':
			$defaultWidth=400;
			$defaultHeight=155;
			break;
		
	
		case 'PC01': case 'YV01':
			$defaultWidth=400;
			$defaultHeight=400;
			break;
		case 'DL01':
		#Daily lift !;
		
/********************************************************
		Pixel iframe heights are determined by the combining of three elements

    Image height S=55, M=60, L=65 and X=87
    Intro text S=40, all others 48
    Image and trailing space (multiply height by number of daily lifts to be displayed)
    small image with date S=52, all others=53
    small image, no date   S=41, all others=53
    large image                 S=65, all others=80
		************************************************************************************/
	
		if (0==(int) $fn) $fn=5;  # Set the number of lifts to display to 5 unless specified otherwise;
			$defaultWidth=225; # default width of header image is 222;
			$defaultHeight=65+48+($fn*80)+5; #Assuming large image used by default;
			break;
		
		default : 
			$defaultWidth=350;
			$defaultHeight=350;

	}
	if (0 == (int) $width) $width=$defaultWidth;
	if (0 == (int) $height) $height=$defaultHeight;	

	if (''==$params) $params="?WH=$height"; else $params='?'.$params."&WH=$height";
	if (0 <> (int) $fn) $params.="&FN=$fn";
		
#	return "CODE $code (Width : $width  Height: $height)<br/><div class='TRMWidget' style='margin : 0px; border : 2px red solid;'><iframe src='http://www.christianscience.org.uk/crtns/".$code.'.php'.$params."' width='$width px' height='$height px' scrolling='$scroll' frameborder='0' marginwidth='0' marginheight='0' hspace='0' vspace='0'></iframe></div>";

	return "<div class='TRMWidget'><iframe src='http://www.christianscience.org.uk/crtns/".$code.'.php'.$params."' width='$width px' height='$height px' scrolling='$scroll' frameborder='0' marginwidth='0' marginheight='0' hspace='0' vspace='0'></iframe></div>";

}


add_shortcode('TRMW','TRMW_shortcode_handler');


class CST_TRM_Widget extends WP_Widget {
    /** constructor */
    function CST_TRM_Widget() {
        parent::WP_Widget(false, $name = 'TRM Widgets');
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        ?>
              <?php $outputHTML="\n\n".$before_widget; 
                    if ( $title )   $outputHTML.="\n".$before_title . $title . $after_title."\n";
               		$outputHTML.=TRMW_shortcode_handler ($instance);
               		$outputHTML.=$after_widget;
                 
     echo $outputHTML;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['code'] =  strip_tags($new_instance['code']);
	$instance['params'] = strip_tags( $new_instance['params']);
	$instance['width'] = (int) $new_instance['width'];
	$instance['height'] = (int) $new_instance['height'];
	$instance['fn'] = (int) $new_instance['fn'];
	if ($instance['fn']==0) $instance['fn']='';
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
    $defaults = ARRAY ( 'title' => 'Upcoming Events', 'code'=>'DL01', 'params'=> '', 'width'=> '', 'height' => '', 'fn' =>'' );
        $instance= wp_parse_args( (array) $instance, $defaults ); 
        $title = esc_attr($instance['title']);
        $code = esc_attr( $instance['code']);
         $params = esc_attr( $instance['params']);
        $width = (int) $instance['width'];
        $height = (int) $instance['height'];
        $fn = (int) $instance['fn'];
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
          <p>
          <label for="<?php echo $this->get_field_id('code'); ?>"><?php _e('Widget Code:'); ?></label> 
          <input  id="<?php echo $this->get_field_id('code'); ?>" name="<?php echo $this->get_field_name('code'); ?>" type="text" size='5' value="<?php echo $code; ?>" />
        </p><p>
        Available Widgets:
<iframe src='http://www.christianscience.org.uk/crtns/widgets.php' width='230px' height='100 px' scrolling='yes' frameborder='0' marginwidth='0' marginheight='0' hspace='0' vspace='0'></iframe>
<A href="http://www.widgets.christianscience.org.uk/registration.php" target="register">Register </A> additional<A HREF="http://www.widgets.christianscience.org.uk/index.php"> TRM Widgets</A> <br/>
          </p><p>
          <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:'); ?></label> 
          <input  id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" size='3' value="<?php echo $width; ?>" />
         <label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('x height:'); ?></label> 
          <input  id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" size='3' value="<?php echo $height; ?>" />
        </p>
           <p>
          <label for="<?php echo $this->get_field_id('fn'); ?>"><?php _e('Nbr to display:'); ?></label> 
          <input  id="<?php echo $this->get_field_id('fn'); ?>" name="<?php echo $this->get_field_name('fn'); ?>" type="text" size='2' value="<?php echo $fn; ?>" />
        </p>
         <p>
          <label for="<?php echo $this->get_field_id('params'); ?>"><?php _e('Aditional optional paramaters'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('params'); ?>" name="<?php echo $this->get_field_name('params'); ?>" type="text" value="<?php echo $params; ?>" />
        </p>
        <?php 
    }

} // CST_List_Event_Widget


add_action('widgets_init', create_function('', 'return register_widget("CST_TRM_Widget");'));








# //////////////////////////// /// code below adds the options page to the menu. ////////////////////////////

function cst_options_panel() {
	global $cst_pluginName;
	if (function_exists('add_options_page')) {
		add_options_page($cst_pluginName, 'CS Tools', 'manage_options', 'cstools-settings', 'csbranches_display_options_panel');
	}
}

function csbranches_display_options_panel() {
	
 
	global $ssn_pluginName;	if (isset($_POST['info_update'])) {
		?>

		<div class="updated"><p><strong>
	
		<?php _e('Process completed fields in this if-block, and then print warnings, errors or success information.','Localization name'); ?>
	
		</strong></p></div>

		<?php 
	} ?>
	
	<?php include('cstools_admin.php'); ?>
	
	
<?php
}




add_action('admin_menu', 'cst_options_panel');
# The following line makes shortcodes available within text widgets. 
add_filter('widget_text', 'do_shortcode');

# ///////////////////////// The following code adds the plugin stylesheet(cstools_styles.css) to the admin and front pages ///////////

function link_cstools_styles() {
global $cst_pluginDir;
  #  $url = get_option('siteurl');
    $url = plugins_url('/css/cstools_styles.css',__FILE__);
    echo '<link rel="stylesheet" type="text/css" href="' . $url . '" />';
}

if ( $cst_plugin_options['inc_css']) {
	add_action('admin_head', 'link_cstools_styles');
	add_action('wp_head','link_cstools_styles',1);
}
/*****************************************************************************
** Add  contextual help for Events 
** This filter adds contextual help to admin screens
** It probably only needs to be loaded to admin pages... ACTION TO DO 
*********************************************************************************/

add_action( 'contextual_help', 'cst_add_help_text2', 10, 3 );

function cst_add_help_text2($contextual_help, $screen_id, $screen) { 
 # $contextual_help .= var_dump($screen); // use this to help determine $screen->id

  if ('post' == $screen->id ) {
    $contextual_help .='<img src="http://christianscienceplugins.net/logos/cs-plugin-logo.png" style="float:left; width:100px; padding-right:30px;"/>'.
     '<strong> The CS Tools plugin </strong>makes the following shortcodes available:<ul>'.
     '<li> [cover p="periodical"] to display CS periodical and product covers (<A href="http://christianscienceplugins.net/2011/04/periodical-cover-shortcodes/" target="cst_help"> more info</A>)</li>'.
     '<li> [Bible ref="???"] Bible passage [/Bible] and [SH] passage [/SH] to display quotes in context (<A target="cst_help" href="http://christianscienceplugins.net/2011/04/bible-and-science-and-health-quotes/"> more info </a>)</li>'.
     '<li> [upcoming ]</li>'.
        '<li> [TRMW code="XXYY" ]</li>'.
     '</ul> <hr style="clear:both;">' ;
	}
	
 if ('settings_page_cstools-settings' == $screen->id ) {
    $contextual_help = <<<EOD
<img src="http://christianscienceplugins.net/logos/cs-plugin-logo.png" style="float:left; width:100px; padding-right:30px;"/>
<p>CS Tools plugin was written specifically for <a href="http://christianscience.com/lectures/" target="_blank">Christian Science Lecturers </a>and <a href="http://christianscience.com/church/" target="_blank">CS Branch churches</a>, but anyone is of course welcome to use it.</p>
<p>Settings allow you to disable the plugin style sheet if you wish to use your own instead.</p>
<p>The plugin introduces a new Event Post Type (cst_event), the Event Base controls where these posts appear on your site (e.g. www.example.com/events ) The Future Event Category and Past Event Category Bases control where Event Category Archives will appear on the site (e.g. www.example.com/future/lectures  where lectures is an event category slug).</p>
<p>For more information vist <a href="http://christianscienceplugins.net" target="_blank">christianscienceplugins.net</a></p>
EOD;

	}	
	
  return $contextual_help;
}



?>