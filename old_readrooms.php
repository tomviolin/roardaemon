#!/usr/bin/php
<?php 


// log into the site
//
//




echo "================ request 1 ================\n";
// wget -S -O resp01.html --load-cookies=/dev/null   --save-cookies=cooks01.txt --keep-session-cookies --no-check-certificate https://25live.collegenet.com/25live/data/uwm/run/login.shibboleth?redirect=https://25live.collegenet.com/25live/index.html 2>resp01headers.txt

$ch = curl_init();
curl_setopt($ch, CURLOPT_COOKIEFILE, "");
curl_setopt($ch, CURLOPT_COOKIEJAR, "cooks01.txt");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CERTINFO, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_URL, "https://25live.collegenet.com/25live/data/uwm/run/login.shibboleth?redirect=https://25live.collegenet.com/uwm/");
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
$info = curl_exec($ch);
//curl_close($ch);

print_r($info);


echo "================ request 2 ================\n";
//wget -S -O resp02.html --load-cookies=cooks01.txt --save-cookies=cooks02.txt --keep-session-cookies --no-check-certificate --post-data 'j_username=tomh&j_password=wd34faer%3F456&s=Log+in' --max-redirect=0 https://idp.uwm.edu/idp/Authn/UserPassword

//$ch=curl_init();
curl_setopt($ch, CURLOPT_COOKIEFILE, "cooks01.txt");
curl_setopt($ch, CURLOPT_COOKIEJAR, "cooks02.txt");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CERTINFO, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_URL, "https://idp.uwm.edu/idp/profile/SAML2/Redirect/SSO?execution=e1s1");
curl_setopt($ch, CURLOPT_POSTFIELDS, "j_username=tomh&j_password=wd34faer-5252&_eventId_proceed=");
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_NOBODY, false);

$info = curl_exec($ch);

//$p = strpos($info,"<html>");
//$info=substr($info,$p);


//curl_close($ch);
echo "====== INFO TO BE PARSED ======\n\n";
echo $info;
echo "\n\n====== END OF INFO TO BE PARSED ======\n\n";


// new way
$dom = new DOMDocument;
$dom->loadHTML($info);

/*  OLD WAY 
$sxe = simplexml_load_string($info);

$dom_sxe = dom_import_simplexml($sxe);

$dom = new DOMDocument('1.0');
$dom_sxe = $dom->importNode($dom_sxe, true);
$dom_sxe = $dom->appendChild($dom_sxe);
*/

$inputs = $dom->getElementsbyTagName("input");

$fields = array();
for ($i = 0; $i < $inputs->length; ++$i) {
	$domnode = $inputs->item($i);
	$fieldname = @$domnode->attributes->getNamedItem('name')->value;
	$fieldvalue = @$domnode->attributes->getNamedItem('value')->value;
	if ($fieldname > "") {
		$fields[$fieldname] = $fieldvalue;
	}
}

print_r($fields);

echo "================ request 3 ================\n";
//echo -n "RelayState=$RelayState&SAMLResponse=$SAMLResponse" > post03.txt
//echo "RelayState=$RelayState&SAMLResponse=$SAMLResponse" 

//wget -S -O resp03.html --user-agent "Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10" \
//   --header "Host: 25live.collegenet.com" \
//   --header 'Origin: https://idp.uwm.edu' \
//   --header 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' \
//   --header 'Accept-Encoding: gzip,deflate' \
//   --header 'Accept-Language: en-US,en;q=0.8' \
//   --header 'Cache-Control: no-cache' \
//   --header 'Content-Type: application/x-www-form-urlencoded' \
//   --referer 'https://idp.uwm.edu/idp/profile/SAML2/Redirect/SSO' \
//   --load-cookies=cooks025.txt --save-cookies=cooks03.txt --keep-session-cookies \
//   --no-check-certificate --post-file post03.txt 'https://25live.collegenet.com/Shibboleth.sso/SAML2/POST'

//php readrooms.php


//$ch=curl_init();
curl_setopt($ch, CURLOPT_COOKIEFILE, "cooks02.txt");
curl_setopt($ch, CURLOPT_COOKIEJAR, "cooks03.txt");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CERTINFO, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$postfields="RelayState=".urlencode($fields['RelayState'])."&SAMLResponse=".urlencode($fields['SAMLResponse']);
curl_setopt($ch, CURLOPT_URL, "https://25live.collegenet.com/uwm/Shibboleth.sso/SAML2/POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
curl_setopt($ch, CURLOPT_HEADER, true);
//curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	"POST /Shibboleth.sso/SAML2/POST HTTP/1.1",
	"Host: 25live.collegenet.com",
	"Connection: keep-alive",
	//"Content-Length: ".strlen($postfields),
	"Cache-Control: max-age=0",
	"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
	"Origin: https://idp.uwm.edu",
	"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.125 Safari/537.36",
	"Content-Type: application/x-www-form-urlencoded",
	"Referer: https://idp.uwm.edu/idp/profile/SAML2/Redirect/SSO?execution=e1s1",
	//"Accept-Encoding: gzip, deflate",
	"Accept-Language: en-US,en;q=0.8"));
echo "about to exec...\n";
echo $postfields."\n";
$info = curl_exec($ch);
//curl_close($ch);

echo $info;

echo "================ END OF request 3 ================\n";




//$ch=curl_init();
curl_setopt($ch, CURLOPT_COOKIEFILE, "cooks03.txt");
curl_setopt($ch, CURLOPT_COOKIEJAR, "cooks04.txt");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CERTINFO, true);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_URL, "https://25live.collegenet.com/25live/data/uwm/run/spaces.xml?ML_FLS=R&name=GLRF&scope=list");
curl_setopt($ch, CURLOPT_HEADER, false);
//curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	"Host: 25live.collegenet.com",
	"Connection: keep-alive",
	//"Content-Length: ".strlen($postfields),
	"Cache-Control: max-age=0",
	"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
	"Origin: https://idp.uwm.edu",
	"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.125 Safari/537.36",
	// "Referer: https://idp.uwm.edu/idp/profile/SAML2/Redirect/SSO?execution=e1s1",
	#"Accept-Encoding: gzip, deflate",
	"Accept-Language: en-US,en;q=0.8"));
echo "about to exec...\n";
$info = curl_exec($ch);
//curl_close($ch);

echo $info;

echo "done curling.\n";

file_put_contents("rooms.xml", $info);


#system("wget -O rooms.xml --keep-session-cookies --load-cookies=cooks04.txt --save-cookies=cooks05.txt --no-check-certificate --header 'Cookie: blah=5' 'https://25live.collegenet.com/25live/data/uwm/run/spaces.xml?ML_FLS=R&name=GLRF&scope=list'");
#system("wget -O rooms.xml --keep-session-cookies --load-cookies=cooks05.txt --save-cookies=cooks06.txt --no-check-certificate --header 'Cookie: blah=5' 'https://25live.collegenet.com/25live/data/uwm/run/spaces.xml?ML_FLS=R&name=GLRF&scope=list'");
#system("wget -O zoot.txt --keep-session-cookies --load-cookies=cooks05.txt --save-cookies=cooks06.txt --no-check-certificate --header 'Cookie: blah=5' 'https://25live.collegenet.com/25live/data/uwm/run/rm_reservations.ics?space_id=685&start_dt=-30&end_dt=+180&options=standard'");

#wget -O calendar.xml --load-cookies=cooks03.txt --no-check-certificate "https://25live.collegenet.com/25live/data/uwm/run/rm_reservations.xml?space_id=427&date_params=date_order%3A%20MDY%3B%20hour_inc%3A%201%3B%20minute_inc%3A%205%3B%20month_display%3A%20I%3B%20day_display%3A%20I%3B%20date_sep%3A%20S%3B%20time_display%3A%2012&otransform=https://25live.collegenet.com/hybridssl/v23.0/xslt/25live/s25-space-search/rm_rsrv_formatter.xsl&name_display=name&start_dt=20141201T000001&end_dt=20141231T235959&scope=extended&include=attributes+spaces+blackouts+text&office_start=0600&office_end=2200&calendarstart=20141201T000001&date_params=date_order%3A%20MDY%3B%20hour_inc%3A%201%3B%20minute_inc%3A%205%3B%20month_display%3A%20I%3B%20day_display%3A%20I%3B%20date_sep%3A%20S%3B%20time_display%3A%2012"


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
		//$layout_id = $reservation->layout_id;
		//if ($layout_id != "" && $layout_id != "11" && $layout_id != "7") {
		//	$summary .= "\\nLayout: ".$reservation->layout_id." ".$reservation->layout_name;
		//}
		$vevent->setProperty( "summary", $summary);
		$description = $reservation->event->event_description;
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
	$ymdstart = date("Ymd",strtotime($thismonthstart)-$ONEDAY*45);
	$ymdend = date("Ymd",strtotime($thismonthend));
	$dfile = date("Y_m_",strtotime($thismonthstart));
	//$ch = curl_init();
	//curl_setopt($ch, CURLOPT_COOKIEFILE, "cooks03.txt");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	for ($i = 0; $i < count($rids); ++$i){
		$roomid = $rids[$i];
		$rfile = "calendars/". preg_replace("/ /","_",$rnames[$i]).".ics";
		echo $rfile."...";

		$url= "https://25live.collegenet.com/25live/data/uwm/run/rm_reservations.xml?space_id={$roomid}&start_dt={$ymdstart}T000000&end_dt={$ymdend}T235959&scope=extended";
		
	//	$url = "https://25live.collegenet.com/25live/data/uwm/run/rm_reservations.xml?space_id={$roomid}&date_params=date_order%3A%20MDY%3B%20hour_inc%3A%201%3B%20minute_inc%3A%205%3B%20month_display%3A%20I%3B%20day_display%3A%20I%3B%20date_sep%3A%20S%3B%20time_display%3A%2012&otransform=https://25live.collegenet.com/hybridssl/v23.0/xslt/25live/s25-space-search/rm_rsrv_formatter.xsl&name_display=name&start_dt={$ymdstart}T000001&end_dt={$ymdend}T235959&scope=extended&include=attributes+spaces+blackouts+text&office_start=0600&office_end=2200&calendarstart={$ymdstart}T000001&date_params=date_order%3A%20MDY%3B%20hour_inc%3A%201%3B%20minute_inc%3A%205%3B%20month_display%3A%20I%3B%20day_display%3A%20I%3B%20date_sep%3A%20S%3B%20time_display%3A%2012";
		echo "RETRIEVING URL >> $url\n";
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch);
		if ($result === false) echo curl_error($ch);
		file_put_contents($rfile."-xml",$result);
		//file_put_contents($rfile,$result);
		//if ($rnames[$i]=="GLRF 3093")
		convert_calendar($result, $rfile, $rnames[$i]);
		convert_to_global_calendar($result, $rnames[$i]);
		echo "\n";


	}
	$thismonthstart = $nextmonthstart;
}
curl_close($ch);

write_global_calendar("calendars/GLRF_ALL.ics");
