<?php 
require_once 'iCalcreator/iCalcreator.class.php';
date_default_timezone_set("America/Chicago");
$xml = simplexml_load_file("rooms.xml", "SimpleXMLElement",0,"r25",true);
$nitems = count($xml->item);
$items = $xml->item;

	function sort2dtarray($sort) {
		return 
			array(  "year"  => substr($sort,0,4)+0,
				"month" => substr($sort,4,2)+0,
				"day"   => substr($sort,6,2)+0,
				"hour"  => substr($sort,8,2)+0,
				"min"   => substr($sort,10,2)+0,
				"sec"   => substr($sort,12,2)+0);
	}


function convert_calendar($calxmlstring, $filename, $calname) {
	$calxml = preg_replace("/<r25:/","<", $calxmlstring);
	$calxml = preg_replace("/<\/r25:/","</", $calxml);
	$calx = simplexml_load_string($calxml, "SimpleXMLElement",0); #,"r25",true);

	$tz = date_default_timezone_get();


	$config = array( "unique_id" => "freshwater.uwm.edu",           // set Your unique id, required if any component UID is missing
			 "TZID"      => $tz );                     // opt. set "calendar" timezone
	$v = new vcalendar( $config );                             // create a new calendar object instance

	$v->setProperty( "method", "PUBLISH" );                    // required of some calendar software
	$v->setProperty( "x-wr-calname", $calname );      // required of some calendar software
	$v->setProperty( "X-WR-CALDESC", $calname ); // required of some calendar software
	$v->setProperty( "X-WR-TIMEZONE", $tz );                   // required of some calendar software

	$xprops = array( "X-LIC-LOCATION" => $tz );                // required of some calendar software
	iCalUtilityFunctions::createTimezone( $v, $tz, $xprops );  // create timezone component(-s) opt. 1
								   // based on present date


				
	print_r($calx);
	foreach ( $calx->space_reservation as $reservation) {
		echo $reservation->event->event_name . "\n";
		$vevent = & $v->newComponent( "vevent" );                  // create an event calendar component
		$vevent->setProperty( "dtstart", $reservation->reservation_start_dt);
		$vevent->setProperty( "dtend",   $reservation->reservation_end_dt);
		$vevent->setProperty( "LOCATION", $reservation->spaces->space_name );       // property name - case independent
		$summary = $reservation->event->event_name."\\n".$reservation->event->event_description;
		$layout_id = $reservation->layout_id;
		if ($layout_id != "" && $layout_id != "11") {
			$summary .= "\\nLayout: ".$reservation->layout_id." ".$reservation->layout_name;
		}
		$vevent->setProperty( "summary", $summary);
		$vevent->setProperty( "description", $reservation->event->event_description );
		$vevent->setProperty( "comment", $reservation->event->event_type_name );
		$vevent->setProperty( "attendee", "" );

	}
	return $v;
	file_put_contents($filename."-tmp", $v->CreateCalendar());
	rename($filename."-tmp", $filename);
}



$rnames = array();
$rids = array();

for ($i=0;$i<$nitems;++$i){
	echo ($xml->item[$i]->id)."\t".($xml->item[$i]->name)."\n";
	$rnames[] = (string) $xml->item[$i]->name;
	$rids[]   = (string) $xml->item[$i]->id;
}
array_multisort($rnames,0,$rids);

$ONEDAY= 60*60*24;
$thismonthstart = date("Y-m-01 11:59")-$ONEDAY*45;

for ($months = 0; $months < 1; ++$months) {
	$nextmonthstart = date("Y-m-01 11:59",strtotime($thismonthstart)+$ONEDAY*180);
	$thismonthend = date("Y-m-d 11:59",strtotime($nextmonthstart)-$ONEDAY);
	$ymdstart = date("Ymd",strtotime($thismonthstart));
	$ymdend = date("Ymd",strtotime($thismonthend));
	$dfile = date("Y_m_",strtotime($thismonthstart));
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cooks03.txt");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	for ($i = 0; $i < count($rids); ++$i){
		$roomid = $rids[$i];
		$rfile = "calendars/". preg_replace("/ /","_",$rnames[$i]).".ics";
		echo $rfile."...";
		
		$url = "https://roomscheduling.uwm.edu/25live/data/run/rm_reservations.xml?space_id={$roomid}&date_params=date_order%3A%20MDY%3B%20hour_inc%3A%201%3B%20minute_inc%3A%205%3B%20month_display%3A%20I%3B%20day_display%3A%20I%3B%20date_sep%3A%20S%3B%20time_display%3A%2012&otransform=https://25live.collegenet.com/hybridssl/v23.0/xslt/25live/s25-space-search/rm_rsrv_formatter.xsl&name_display=name&start_dt={$ymdstart}T000001&end_dt={$ymdend}T235959&scope=extended&include=attributes+spaces+blackouts+text&office_start=0600&office_end=2200&calendarstart={$ymdstart}T000001&date_params=date_order%3A%20MDY%3B%20hour_inc%3A%201%3B%20minute_inc%3A%205%3B%20month_display%3A%20I%3B%20day_display%3A%20I%3B%20date_sep%3A%20S%3B%20time_display%3A%2012";
		echo "RETRIEVING URL >> $url\n";
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch);
		if ($result === false) echo curl_error($ch);
		//file_put_contents($rfile,$result);
		//if ($rnames[$i]=="GLRF 3093")
		convert_calendar($result, $rfile, $rnames[$i]);
		echo "\n";


	}
	$thismonthstart = $nextmonthstart;
}
curl_close($ch);


