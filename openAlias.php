<?php
// Copyright (c) 2018, Gnock
// Copyright (c) 2018, The Masari Project
// Copyright (c) 2019, The Qwertycoin developers
//
// This file is part of Qwertycoin.
//
// Qwertycoin is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// License for more details.
//

include 'config/config.php';

if(!isset($_GET['domain']) || !preg_match('/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/m', $_GET['domain']))
	http_response_code(400);

$records = dns_get_record($_GET['domain'], DNS_TXT);

$recipient_address = null;
$recipient_name = null;

foreach($records as $record){
	if($record['class'] === 'IN' && $record['type'] === 'TXT'){
		foreach($record['entries'] as $entry){
			if(strpos($entry, 'oa1:'.$coinSymbol) !== false){
				$raw = str_replace('oa1:'.$coinSymbol,'', $entry);
				$parts = explode(';', $raw);
				foreach($parts as $part){
					$subparts = explode('=',trim($part));
					if(count($subparts) >= 1){
						if(trim($subparts[0]) === 'recipient_address'){
							$recipient_address = trim($subparts[1]);
						}else if(trim($subparts[0]) === 'recipient_name'){
							$recipient_name = trim($subparts[1]);
						}
					}
				}
			}
		}
	}
	
}
if($recipient_address !== null){
	header('Content-Type: application/json');
	echo json_encode(array(
		'address'=>$recipient_address,
		'name'=>$recipient_name,
	));
}else{
	http_response_code(404);
}
