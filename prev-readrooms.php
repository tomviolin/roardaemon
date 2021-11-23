#!/usr/bin/php
<?php 



system("wget -O rooms2.xml --user tomh --password 25LiveSFSPublisher --no-check-certificate 'https://webservices.collegenet.com/r25ws/wrd/uwm/run/spaces.xml?ML_FLS=R&name=GLRF&scope=list'");



require_once 'iCalcreator/iCalcreator.class.php';
date_default_timezone_set("America/Chicago");
$xml = simplexml_load_file("rooms2.xml", "SimpleXMLElement",0,"r25",true);
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

$allv = NULL;

function convert_to_global_calendar($calxmlstring, $calname) {
	global $allv;
	$calxml = preg_replace("/<r25:/","<", $calxmlstring);
	$calxml = preg_replace("/<\/r25:/","</", $calxml);
	$calx = simplexml_load_string($calxml, "SimpleXMLElement",0); #,"r25",true);

	print_r ($calx);

	$tz = date_default_timezone_get();


	$config = array( "unique_id" => "freshwater.uwm.edu",           // set Your unique id, required if any component UID is missing
			 "TZID"      => $tz );                     // opt. set "calendar" timezone
	if ($allv === NULL) {

		$allv = new vcalendar( $config );                             // create a new calendar object instance

		$allv->setProperty( "method", "PUBLISH" );                    // required of some calendar software
		$allv->setProperty( "x-wr-calname", "GLRF Rooms" );      // required of some calendar software
		$allv->setProperty( "X-WR-CALDESC", "GLRF Rooms" ); // required of some calendar software
		$allv->setProperty( "X-WR-TIMEZONE", $tz );                   // required of some calendar software
		$xprops = array( "X-LIC-LOCATION" => $tz );                // required of some calendar software
		iCalUtilityFunctions::createTimezone( $allv, $tz, $xprops );  // create timezone component(-s) opt. 1
									   // based on present date
	}



	print_r($calx);
	$roomno = preg_replace('/GLRF /','',$calname);
	foreach ( $calx->space_reservation as $reservation) {
		echo $reservation->event->event_name . "\n";
		$vevent = & $allv->newComponent( "vevent" );                  // create an event calendar component
		$vevent->setProperty( "dtstart", $reservation->reservation_start_dt);
		$vevent->setProperty( "dtend",   $reservation->reservation_end_dt);
		$vevent->setProperty( "LOCATION", $reservation->spaces->space_name );       // property name - case independent
		$summary = "[".$roomno."] ".$reservation->event->event_name;//."\\n".$reservation->event->event_description;
		$description = $reservation->event->event_description;
		if ($reservation->event->event_type_name == "LEC") {
			if (preg_match('/^Instructors: /',$description) >= 0) {
				$instlnames="";
				$ilcomma="";
				$insts = preg_replace('/^Instructors: /','',$description);
				$iarr = explode("; ",$insts);
				foreach ($iarr as $inst) {
					$inames = explode(', ',$inst);
					$instlnames .= $ilcomma.$inames[0];
					$ilcomma=",";
				}
				$summary .= "(".$instlnames.")";
			}
		}
		//$layout_id = $reservation->layout_id;
		//if ($layout_id != "" && $layout_id != "11" && $layout_id != "7") {
		//	$summary .= "\\nLayout: ".$reservation->layout_id." ".$reservation->layout_name;
		//}
		$vevent->setProperty( "summary", $summary);
		if ($reservation->space_instruction_id > 0) {
			$description .= "\\n<b>Space Instructions:</b>\\n" . $reservation->space_instructions;
		}
		$vevent->setProperty( "description", $description );
		$vevent->setProperty( "comment", $reservation->event->event_type_name );
		$vevent->setProperty( "attendee", "" );

	}
}

function write_global_calendar($filename) {
	global $allv;
	file_put_contents($filename."-tmp", $allv->CreateCalendar());
	rename($filename."-tmp", $filename);
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
		$summary = $reservation->event->event_name;//."\\n".$reservation->event->event_description;
		//$layout_id = $reservation->layout_id;
		//if ($layout_id != "" && $layout_id != "11" && $layout_id != "7") {
		//	$summary .= "\\nLayout: ".$reservation->layout_id." ".$reservation->layout_name;
		//}
		$vevent->setProperty( "summary", $summary);
		$description = $reservation->event->event_description;
		if ($reservation->event->event_type_name == "LEC") {
			if (preg_match('/^Instructors: /',$description) >= 0) {
				$instlnames="";
				$ilcomma="";
				$insts = preg_replace('/^Instructors: /','',$description);
				$iarr = explode("; ",$insts);
				foreach ($iarr as $inst) {
					$inames = explode(', ',$inst);
					$instlnames .= $ilcomma.$inames[0];
					$ilcomma=",";
				}
				$summary .= "(".$instlnames.")";
			}
		}
		if ($reservation->space_instruction_id > 0) {
			$description .= "\\nSpace Instructions:\\n" . $reservation->space_instructions;
		}
		$vevent->setProperty( "description", $description );
		$vevent->setProperty( "comment", $reservation->event->event_type_name );
		$vevent->setProperty( "attendee", "" );

	}

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
$thismonthstart = date("Y-m-01 11:59");

for ($months = 0; $months < 1; ++$months) {
	$nextmonthstart = date("Y-m-01 11:59",strtotime($thismonthstart)+$ONEDAY*500);
	$thismonthend = date("Y-m-d 11:59",strtotime($nextmonthstart)-$ONEDAY);
	$ymdstart = date("Ymd",strtotime($thismonthstart)-$ONEDAY*90);
	$ymdend = date("Ymd",strtotime($thismonthend));
	$dfile = date("Y_m_",strtotime($thismonthstart));
	//$ch = curl_init();
	//curl_setopt($ch, CURLOPT_COOKIEFILE, "cooks03.txt");
	for ($i = 0; $i < count($rids); ++$i){
		$roomid = $rids[$i];
		$rfile = "calendars/". preg_replace("/ /","_",$rnames[$i]).".ics";
		echo $rfile."...";

		echo "RETRIEVING URL >>";
		echo("wget -O $rfile-xml --user tomh --password 25LiveSFSPublisher --no-check-certificate 'https://webservices.collegenet.com/r25ws/wrd/uwm/run/rm_reservations.xml?space_id={$roomid}&start_dt={$ymdstart}T000000&end_dt={$ymdend}T235959&scope=extended'\n");
		system("wget -O $rfile-xml --user tomh --password 25LiveSFSPublisher --no-check-certificate 'https://webservices.collegenet.com/r25ws/wrd/uwm/run/rm_reservations.xml?space_id={$roomid}&start_dt={$ymdstart}T000000&end_dt={$ymdend}T235959&scope=extended'");
		$result = file_get_contents("$rfile-xml");
		convert_calendar($result, $rfile, $rnames[$i]);
		convert_to_global_calendar($result, $rnames[$i]);
		echo "\n";


	}
	$thismonthstart = $nextmonthstart;
}

write_global_calendar("calendars/GLRF_ALL.ics");
