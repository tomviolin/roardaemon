#!/usr/bin/env php
<?php 
// #!/usr/local/bin/php

if ($GLOBALS['argv'][1] === "check" && file_exists("/calendars/GLRF_ALL.ics")) {
	exit(1);
}

chdir("/opt/roardaemon");
$pass = getenv("LIVEPASS");
system("wget -O rooms2.xml --user tomh --password $pass --no-check-certificate 'https://webservices.collegenet.com/r25ws/wrd/uwm/run/spaces.xml?ML_FLS=R&name=GLRF&scope=list'");



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

	//print_r ($calx);

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
	$previousvevent = 0;
	$previoushash = "";
	foreach ( $calx->space_reservation as $reservation) {
		$eventhash="";
		$workingname = $reservation->event->event_name;
		echo $reservation->event->event_name . ": ";
		$description = $reservation->event->event_description;
		if ($reservation->event->event_type_name == 'LEC') {
			// look for possible cross-listed courses and skip them
			$classdesc = $reservation->event->event_name;
			$classdesc = preg_replace('/BIO SCI/','BIOSCI',$classdesc);
			$classwords = explode(" ",$classdesc);
			if ($classwords[0] == "FRSHWTR") {
				$workingname = "FW " . substr($classwords[1],0,3);
			} elseif ($classwords[0]=="EXAM:") {
				$workingname = "EXAM: FW " . substr($classwords[2],0,3);
			} else {
				$workingname = $classdesc;
			}
			$eventhash = $reservation->event->event_description .
				$reservation->event->event_title .
				$reservation->reservation_start_dt . 
				$reservation->reservation_end_dt;
			if ($eventhash == $previoushash) {
				echo "skipped.\n";
				continue;
			}
		}
		$summary = "[".$roomno."] ".$workingname; //$reservation->event->event_name;//."\\n".$reservation->event->event_description;
		if ($reservation->event->event_type_name == 'LEC') {
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
				$summary .= " ".$instlnames.": ".$reservation->event->event_title;
			}
		}
		echo "creating event; ";
		$vevent = & $allv->newComponent( "vevent" );                  // create an event calendar component
		$vevent->setProperty( "UID", $reservation->reservation_id . "-" . $reservation->spaces->space_name);
		#$vevent->setProperty( "DTSTAMP", "20210101T010101Z");
		$dtstamp = gmdate("Ymd\\THis\\Z");
		$vevent->setProperty( "DTSTAMP", $dtstamp); #"20210101T010101Z");
		$vevent->setProperty( "dtstart", $reservation->reservation_start_dt);
		$vevent->setProperty( "dtend",   $reservation->reservation_end_dt);
		$vevent->setProperty( "LOCATION", $reservation->spaces->space_name );       // property name - case independent
		//$layout_id = $reservation->layout_id;
		//if ($layout_id != "" && $layout_id != "11" && $layout_id != "7") {
		//	$summary .= "\\nLayout: ".$reservation->layout_id." ".$reservation->layout_name;
		//}
		$vevent->setProperty( "summary", $summary);
		if ($reservation->space_instruction_id > 0) {
			$description .= "\\n<b>Space Instructions:</b>\\n" . $reservation->space_instructions;
		}
		if ($reservation->requestor_name != "") {
			$description .= "\\nRequestor:\\n" . $reservation->requestor_name . " " .$reservation->requestor_email . " " . $reservation->requestor_phone;
		}
		if ($reservation->scheduler_name != "") {
			$description .= "\\nScheduler:\\n" . $reservation->scheduler_name . " " .$reservation->scheduler_email . " " . $reservation->scheduler_phone;
		}

		$vevent->setProperty( "description", $description . " [". date("m/d H:i")."]" );
		$vevent->setProperty( "comment", $reservation->event->event_name . " " .$reservation->event->event_type_name . " " . $dtstamp );
		$vevent->setProperty( "attendee", "" );

		$previousvevent = $vevent;
		$previoushash =  $eventhash;
		echo "done.\n";
	}
}

function write_global_calendar($filename) {
	global $allv;
	file_put_contents($filename.".dump", print_r($allv, TRUE));
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


				
	//print_r($calx);
	$previousvevent = 0;
	$previoushash = "";
	foreach ( $calx->space_reservation as $reservation) {
		$eventhash="";
		$workingname = $reservation->event->event_name;
		echo $reservation->event->event_name . " :";
		$description = $reservation->event->event_description;
		if ($reservation->event->event_type_name == 'LEC') {
			// look for possible cross-listed courses and skip them
			$classdesc = $reservation->event->event_name;
			$classdesc = preg_replace('/BIO SCI/','BIOSCI',$classdesc);
			$classwords = explode(" ",$classdesc);
			if ($classwords[0] == "FRSHWTR") {
				$workingname = "FW " . substr($classwords[1],0,3);
			} elseif ($classwords[0]=="EXAM:") {
				$workingname = "EXAM: FW " . substr($classwords[2],0,3);
			} else {
				$workingname = $classdesc;
			}
			$eventhash = $reservation->event->event_description .
				$reservation->event->event_title .
				$reservation->reservation_start_dt . 
				$reservation->reservation_end_dt;
			if ($eventhash == $previoushash) {
				echo "skipped.\n";
				continue;
			}
		}
		$summary = $workingname; //$reservation->event->event_name;//."\\n".$reservation->event->event_description;
		if ($reservation->event->event_type_name == 'LEC') {
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
				#$summary .= " (".$instlnames.")";
				$summary .= " ".$instlnames.": ".$reservation->event->event_title;
			}
		}
		echo "creating event; ";
		$vevent = & $v->newComponent( "vevent" );                  // create an event calendar component
		$vevent->setProperty( "UID", $reservation->reservation_id);
		#$vevent->setProperty( "DTSTAMP", "20210101T010101Z");
		$dtstamp = gmdate("Ymd\\THis\\Z");
		$vevent->setProperty( "DTSTAMP", $dtstamp); #"20210101T010101Z");
		$vevent->setProperty( "dtstart", $reservation->reservation_start_dt);
		$vevent->setProperty( "dtend",   $reservation->reservation_end_dt);
		$vevent->setProperty( "LOCATION", $reservation->spaces->space_name );       // property name - case independent
		//$summary = $workingname; //$reservation->event->event_name;//."\\n".$reservation->event->event_description;
		$vevent->setProperty( "summary", $summary);
		if ($reservation->space_instruction_id > 0) {
			$description .= "\\nSpace Instructions:\\n" . $reservation->space_instructions;
		}
		if ($reservation->requestor_name != "") {
			$description .= "\\nRequestor:\\n" . $reservation->requestor_name . " " .$reservation->requestor_email . " " . $reservation->requestor_phone;
		}
		if ($reservation->scheduler_name != "") {
			$description .= "\\nScheduler:\\n" . $reservation->scheduler_name . " " .$reservation->scheduler_email . " " . $reservation->scheduler_phone;
		}

		//$vevent->setProperty( "description", $description );
		$vevent->setProperty( "description", $description . " [". date("m/d H:i")."]" );
		$vevent->setProperty( "comment", $reservation->event->event_name . " " .$reservation->event->event_type_name . " " . $dtstamp );
		$vevent->setProperty( "attendee", "" );

		$previousvevent = $vevent;
		$previoushash =  $eventhash;
		echo "done.\n";
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
	$ymdstart = date("Ymd",strtotime($thismonthstart)-$ONEDAY*30);
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

