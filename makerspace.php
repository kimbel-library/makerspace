<?php
	// Makerspace Certification
	$MAKERSPACE_CERT = "Makerspace Certification";
	// UNIQUE ID
	$UNIQUE_ID = "Unique ID";
	// Base URL
	$ALMA_REQ_URL = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/users/";
	// API KEY
	$ALMA_API_KEY = "API KEY"; 
	// GET PARAMETERS
	$ALMA_GET_PARAM = "?user_id_type=all_unique&view=full&expand=none&apikey=";
	// PUT PARAMETERS
	$ALMA_PUT_PARAM = "?user_id_type=all_unique&send_pin_number_letter=false&recalculate_roles=false&apikey=";
	
	// Initialize cURL GET
	$cr = curl_init();
	$curl_options = array(
		CURLOPT_URL => sprintf("%s%s%s%s",$ALMA_REQ_URL,$UNIQUE_ID,$ALMA_GET_PARAM,$ALMA_API_KEY),
		CURLOPT_HTTPGET => true,
		CURLOPT_HTTPHEADER => array("Accept: application/xml"),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false
	);
	curl_setopt_array($cr, $curl_options);
	$response = curl_exec($cr);
	curl_close($cr);

	$doc = new DOMDocument();
	$doc->loadXML($response);
	$xpath = new DOMXpath($doc);
	
	// XPATH query for Makerspace Certification
	$note_text = $xpath->query("//note_text/text()[contains(.,\"$MAKERSPACE_CERT\")]");
	
	// If the patron doesn't have an existing note, then we add a <user_note> element to XML response
	if ($note_text->length == 0) {
		// Equipment Agreement valid to $semester 
		$user_notes = $xpath->query("//user_notes")->item(0);
		$user_notes_domnode = $user_notes->cloneNode();
		$user_note = new DOMElement("user_note");
		$user_notes->appendChild($user_note);
		$user_note->setAttribute("segment_type","Internal");​
		$user_note->appendChild(new DOMElement("note_type","OTHER"));​
		$user_note->appendChild(new DOMElement("note_text","$MAKERSPACE_CERT"));​
		$user_note->appendChild(new DOMElement("user_viewable","true"));
	
		// Initialize cURL PUT
		$cr = curl_init();
		$curl_options = array(
			CURLOPT_URL => sprintf("%s%s%s%s",$ALMA_REQ_URL,$UNIQUE_ID,$ALMA_PUT_PARAM,$ALMA_API_KEY),
			CURLOPT_CUSTOMREQUEST => "PUT",
			CURLOPT_POSTFIELDS => $doc->saveXML(),
			CURLOPT_HTTPHEADER => array("Content-Type: application/xml", "Accept: application/xml"),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		);
		curl_setopt_array($cr, $curl_options);
		$response = curl_exec($cr);
		curl_close($cr);
	}
}
?>
