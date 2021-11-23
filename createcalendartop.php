require_once 'iCalcreator/iCalcreator.class.php';
date_default_timezone_set("America/Chicago");
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
		$layout_id = $reservation->layout_id;
		if ($layout_id != "" && $layout_id != "11" && $layout_id != "7") {
			$summary .= "\\nLayout: ".$reservation->layout_id." ".$reservation->layout_name;
		}
		$vevent->setProperty( "summary", $summary);
		$description = $reservation->event->event_description;
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
