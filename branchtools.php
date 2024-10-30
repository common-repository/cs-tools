<?php

// turn on error checking...
#ini_set('display_errors',1);
#error_reporting(E_ALL);



function display_next_meetings_shortcode_handler( $atts, $content = null ) {
// it is possible to extract attributes from the shortcode. 
   extract( shortcode_atts( array( 'type' => 'Church' ,'max'=>5,
                'heading' => 'Upcoming Services', 'hide_name' => FALSE,  'hide_subject' => FALSE, 'date_format' => '  jS F  g:ia'), $atts ) );
  

$service_subjects = array (
  "God", "Sacrament", "Life", "Truth", "Love", 
  "Spirit", "Soul", "Mind", "Christ Jesus", "Man", 
  "Substance", "Matter", "Reality",
  "Unreality", "Are Sin, Disease, and Death Real?", "Doctrine of Atonement", "Probation After Death",
  "Everlasting Punishment", "Adam and Fallen Man", "Mortals and Immortals", "Soul and Body",
  "Ancient and Modern Necromancy, alias Mesmerism and Hypnotism, Denounced",
  "God the Only Cause and Creator",
  "God the Preserver of Man",
  "Is the Universe, Including Man, Evolved by Atomic Force?",
  "Christian Science"
);

 $cst_types=get_option('cst_service_types', ARRAY ('Church'=>'','RR'=>'','Members'=>''));
 if (!array_key_exists($type, $cst_types )) return "<div class='cstools-next-services' ><H2> Warning!</H2> The service and meeting type :<em> $type </em>does not exist </div>";
$upcoming_mtgs=cst_get_upcoming_services ( $max, $type);

$returnHTML='<div class="cstools-next-services" >';
$returnHTML.='<H2>'. $heading.'</H2><hr/>';
$i=0;

foreach ($upcoming_mtgs as $service) {
 
 if (($i<$max) ) { #Check that we want to display this entry;

#if (date("j m") == date("j m", $service['next_ts']) ) { $date_format = "TODAY g:ia"; }
 
 if ($hide_name<>TRUE) $returnHTML.='<span class="service-name">'.$service['name'].': </span>';;
 $returnHTML.='<span class="service-date">'.date( $date_format, $service['timestamp']).'</span>';  

 if (0==date('w',$service['timestamp']) && ($hide_subject<>TRUE) ) { # Its a sunday service!;
   // find subject
  $sun_week_of_year = floor(date("z",$service['timestamp'])/7);  // z is day from 0 to 365.
  $week_of_26 = fmod($sun_week_of_year, 26);
  $subject = $service_subjects[$week_of_26];
 $returnHTML.="<br/>Subject  : <span class='lesson-subject'> $subject </span>";  // The evening service is a repetition of the morning service??? ;
 }
$returnHTML.='<br/><hr/>';

 $i++;
} // end if want to displayentry

} // end foreach
$returnHTML.=$content;
$returnHTML.='</div>';
return $returnHTML;

} // end function display_service_details

/////////////////////////////////////////////////////////////////////////OLD FUNCTION
function meetings_shortcode_handler( $atts, $content = null ) {
// it is possible to extract attributes from the shortcode. 
   extract( shortcode_atts( array('include' => '','max'=>99), $atts ) );
  
   return display_next_meetings( $include, $max, $content );
}

add_shortcode('display_next_meetings','display_next_meetings_shortcode_handler');
add_shortcode('display_service_times','display_next_meetings_shortcode_handler');
add_shortcode('upcoming','display_next_meetings_shortcode_handler');

$cst_plugin_options=get_option('cst_plugin_options');
if ( $cst_plugin_options['inc_cac']) {
	add_action( 'wp_footer', 'cst_footer_add' ,99);  // wp_head clashes with adding style sheets. 
	}
	
function cst_footer_add () {
 Echo cross_and_crown();
}


function cross_and_crown (){
global $cst_Repository;
$cst_plugin_options=get_option('cst_plugin_options');
$top = $cst_plugin_options['cac_down'];
$left = $cst_plugin_options['cac_left'];
$background = $cst_plugin_options['cac_col'];
$border=$cst_plugin_options['cac_border_col'];
$returnHTML="";
$returnHTML='<div id="candcholder"><div id="CrossAndCrown"><img id="candc" SRC="'.$cst_Repository.'candc/candc.gif" ALT="The Cross and Crown - a registered Trade Mark"/></div></div>';
$returnHTML.=<<<CODE
\n  <style type="text/css">
#candc { float: left; position:absolute; top:{$top}px; left :{$left}px; z-index:99; background: #$background; padding :3px;  border : 5px #$border solid; height:170px;}
</style>
CODE;

return $returnHTML;
}


function cst_compare_timestamp($a, $b)   // this funcion is used by usort to sort the service options array. NOT CURRENTLY NEEDED! Could use to merge result arrays 
{
    if ($a['timestamp'] == $b['timestamp']) {
        return 0;
    }
    return ($a['timestamp'] < $b['timestamp']) ? -1 : 1;
}



function cst_get_upcoming_services ( $max=9, $Type='Church' ) {
// This function sets that service timestamps  

 $upcoming=get_option("cst_upcoming_$Type", ARRAY () ); 
 $last_sunday=strtotime('last sunday');
 $cst_types=get_option('cst_service_types', ARRAY ('Church'=>$last_sunday,'RR'=>$last_sunday,'Members'=>$last_sunday));
 $changed=false;
 $sat_midnight=$cst_types["$Type"]; //Find the time stamp for end of the week we have details for; 
 $ts_now=strtotime('-5 minutes');  // include a period of grace for potential late comers...!
  if ($sat_midnight<$ts_now) {$upcoming=ARRAY(); $sat_midnight=strtotime('last sunday');  } // if it is in the past discard the entire array; echo date(' jS F  g:ia',$sat_midnight);
 while (count($upcoming)>0 && ($upcoming[0]['timestamp']<$ts_now)) {
     $discard=array_shift($upcoming);                                       // otherwisde step through and discard events that are now in the past;
  $changed=true;
  }
 $service_details=get_option('cst_service_details'); $wk=0;
 while (count($upcoming)<$max && ($wk<52)) { // add events week by week until we have enough; 

    foreach($service_details as $key => $service) { 
          if ($service['Type']==$Type) { 
      $frommidnight=' +'.$service['day'].' Days '.$service['hour'].' hours '.$service['mins'].' minutes';
       
            $tstamp=strtotime($frommidnight, $sat_midnight);
            if ($tstamp>$ts_now) {
               $week=floor((date('j',$tstamp)-1) / 7 );   $month=date('n',$tstamp)-1;
               
                              
               if ($service['weeks'][$week]==TRUE && $service['months'][$month]==TRUE ) { //set up array element perhpas should use PUSH; 
                  $upcoming[]= ARRAY( 'timestamp' => $tstamp, 'name'=> $service['name'], 'key' => $key, 'note' => ''); 
                  $changed=true;
               }       
          }
       } 
    } // end foreach looping through service details
 $sat_midnight=strtotime('+1 week', $sat_midnight);
  $wk++;
 }
 if ($changed==TRUE) { 
    $cst_types["$Type"]=$sat_midnight;
    
 #   echo "CHANGED!! $Type $sat_midnight".date('D jS M Y g:ia',$sat_midnight);
        
    $yes=update_option('cst_service_types',$cst_types); // store the weekedending date for the events in the upcoming array;
    
 #   if ($yes==TRUE) echo "YESSS!!!!";
    
    update_option( "cst_upcoming_$Type",$upcoming);  // and store the arrray for next time; 
 }
 // trim $upcoming if needed if we have too many results.
  while ($max>0 && count($upcoming)>$max) {    // if $max=0 the function returns all the values stored. 
     array_pop($upcoming);                                       // discard events that are not needed would array_slice;
     
 #    echo "POP!";
     
  }
 return $upcoming; 
}


function cst_print_service_options () {
$cst_service_details=get_option('cst_service_details'); 


$returnHTML ="<h2> Regular Church Service and Meeting times </h2>";
$returnHTML .="Please enter the times of your Sunday Services and Wednesday Testimony Meetings <br/><br/>";  
$i=1;
$returnHTML .="<table>";
$returnHTML .="<tr><td></td><td> Service Name </td><td> Day </td><td> Time </td><td> Weeks in the month </td><td> Type </td> </tr>";
foreach ($cst_service_details as $service) {

$returnHTML.="<tr> <td>$i : </td><td><input type='text' name='cst_service_name-$i' value='".$service['name']."' size='40' /></td>";
$sunselected= (0==$service['day']) ?  "selected='selected'" : "";
$wedselected= (3==$service['day']) ?  "selected='selected'" : "";
$returnHTML.="<td><select name='cst-day-$i'>";
$weekdays=ARRAY ('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
for ($j=0; $j<=6; $j++) { 
$selected= ($j==$service['day']) ?  "selected='selected'" : "";
$returnHTML.="<option $selected value='$j' > $weekdays[$j] </option>"; 
}
$returnHTML.=" </select> </td>";
$returnHTML.="<td><input type='text' name='cst-hour-$i' value='".$service['hour']."' size='2' />:<input type='text' name='cst-mins-$i' value='".$service['mins']."' size='2' /> </td><td>";
$weeks=$service['weeks'];
$returnHTML.=($weeks[0]==0) ? "1st: <input type='checkbox' name='cst-$i-1' />" : "1st: <input type='checkbox' name='cst-$i-1' checked='checked' />"; 
$returnHTML.=($weeks[1]==0)? "  2nd: <input type='checkbox' name='cst-$i-2' />" : "  2nd: <input type='checkbox' name='cst-$i-2' checked='checked' />"; 
$returnHTML.=($weeks[2]==0)? "  3rd: <input type='checkbox' name='cst-$i-3' />" : "  3rd: <input type='checkbox' name='cst-$i-3' checked='checked' />"; 
$returnHTML.=($weeks[3]==0)? "  4th: <input type='checkbox' name='cst-$i-4' />" : "  4th: <input type='checkbox' name='cst-$i-4' checked='checked' />"; 
$returnHTML.=($weeks[4]==0)? "  5th: <input type='checkbox' name='cst-$i-5' />" : "  5th: <input type='checkbox' name='cst-$i-5' checked='checked' />"; 
$returnHTML.="</td>";
$returnHTML.="<td><input type='hidden' name='cst-Type-$i' value='".$service['Type']."' size='10' /> </td>";
$returnHTML.="<td><select name='cst-Type-$i'>";
$types=get_option('cst_service_types',ARRAY ('Church'=>'last Sunday','RR'=>'last Sunday','Members'=>'last Sunday'));
foreach ($types as $type=>$midnight) { 
$selected= ($type==$service['Type']) ?  "selected='selected'" : "";
$returnHTML.="<option $selected value='$type' > $type </option>"; 
}
$returnHTML.=" </select> </td>";
$returnHTML.="</td><tr/>";

$months=isset($service['months']) ? $service['months']: ARRAY( TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE,TRUE);
$monthnames=ARRAY('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
$returnHTML.="<tr><td></td><td colspan=6>";
for ($j=0;$j<12; $j++) {
$returnHTML.=($months[$j]==0) ? $monthnames[$j].": <input type='checkbox' name='cst-$i-month-$j' />  " : $monthnames[$j].": <input type='checkbox' name='cst-$i-month-$j' checked='checked' />  "; 
} 
$returnHTML.="</td></tr>";


$i++;
}
$returnHTML.="<tr><td></td><td> Add another ? <input type='checkbox' name='add-another' /> </td><td></td><td colspan=4> (Entries can be removed by entering a blank name field or not entering any weeks.)</td></tr>";
$returnHTML.="</table><br/>";

  
/* This can be used to check the code ...  or display a list of upcoming services on the admin page. 

foreach ($types as $type=>$midnight) { 
$returnHTML.="<h2> Upcoming $type </H2>";   #".date(" D jS M Y g:ia", $midnight)."
  $upcoming_services=cst_get_upcoming_services(5, $type);
  $returnHTML.=' <TABLE>';
  foreach ($upcoming_services as $meeting) {
  $returnHTML.='<TR><TD>'.date(" D jS M Y g:ia", $meeting['timestamp']).'</TD><TD>'.$meeting['name'].'</TD></TR>';
  }
  $returnHTML.='</TABLE>';
}

 */
 
 
  return $returnHTML;
}

function cst_service_detail_compare($a, $b)   // this funcion is used by usort to sort the service details options array. 
{
    if ($a['day'] == $b['day']) {  // Compare days then hours then minutes then...
       if ($a['hour']==$b['hour']) {
         if  ($a['mins']==$b['mins']) {
              return 0;
              } else return ($a['mins'] < $b['mins']) ? -1 : 1;
        } else return ($a['hour'] < $b['hour']) ? -1 : 1;
    }
    return ($a['day'] < $b['day']) ? -1 : 1;
}

function cst_handle_service_option_form(){
$i=1; 
$services = ARRAY();
while (isset($_POST["cst_service_name-$i"])) {
 if (($_POST["cst_service_name-$i"]<>'') AND (isset($_POST["cst-$i-1"])|| isset($_POST["cst-$i-2"])||isset($_POST["cst-$i-3"])||isset($_POST["cst-$i-4"])||isset($_POST["cst-$i-5"])) ) {
   $weeks=ARRAY(isset($_POST["cst-$i-1"]),isset($_POST["cst-$i-2"]),isset($_POST["cst-$i-3"]),isset($_POST["cst-$i-4"]),isset($_POST["cst-$i-5"]) );
    $months=ARRAY(isset($_POST["cst-$i-month-0"]), isset($_POST["cst-$i-month-1"]),isset($_POST["cst-$i-month-2"]),isset($_POST["cst-$i-month-3"]),isset($_POST["cst-$i-month-4"]),isset($_POST["cst-$i-month-5"]),isset($_POST["cst-$i-month-6"]), isset($_POST["cst-$i-month-7"]),isset($_POST["cst-$i-month-8"]),isset($_POST["cst-$i-month-9"]),isset($_POST["cst-$i-month-10"]),isset($_POST["cst-$i-month-11"]) );
  
   $services[$i]=ARRAY ('name' => $_POST["cst_service_name-$i"], 'day' => $_POST["cst-day-$i"], 'hour'=> $_POST["cst-hour-$i"],'mins'=> $_POST["cst-mins-$i"], 'Type' => $_POST["cst-Type-$i"],
                'weeks' => $weeks, 'months' => $months );
                }
                $i++;
}


if (isset($_POST['add-another'])) $services[$i]=ARRAY ('name' => 'Please enter service name' , 'day' => '0', 'hour'=> '11','mins'=>'00','weeks' => $weeks, 'Dcode'=>'', 'next_ts'=>'' );
                
         usort( $services,"cst_service_detail_compare" );        
update_option('cst_service_details',$services);
$last_sunday=strtotime('last Sunday');
update_option('cst_service_types',ARRAY ('Church'=>$last_sunday,'RR'=>$last_sunday,'Members'=>$last_sunday));

}

/* This function is to display the upcoming regular services and meetings in a form so they can be edited if needed; 

function display_upcoming_form () {

 $cst_types=get_option('cst_service_types', ARRAY ('Church'=>'','RR'=>'','Members'=>''));
 
 
 foreach ($cst_types as $csType => $cst-tstamp) {
 
 $upcoming_mtgs=cst_get_upcoming_services ( 0, $csType);

$returnHTML='<div class="cstools " >';
$returnHTML.='<H2>'. $csType.'</H2><hr/>';
$i=0;

foreach ($upcoming_mtgs as $service) {
 
 $returnHTML.='<span class="service-name">'.$service['name'].': </span>';
 $returnHTML.='<span class="service-date">'.date( $date_format, $service['timestamp']).'</span>';  

$returnHTML.='<br/><hr/>';

 $i++;


} // end foreach upcoming meeting

} // end foreach csTypes


$returnHTML.=$content;
$returnHTML.='</div>';
return $returnHTML;

} // end function display upcoming form

*/



?>