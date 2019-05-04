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

$curl = curl_init();

$body = json_encode(array("jsonrpc" => "2.0", "id" => "0", "method" => "getlastblockheader"));
curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => 'http://'.$daemonAddress.':'.$rpcPort.'/json_rpc', CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $body));

$resp = curl_exec($curl);
curl_close($curl);

$array = json_decode($resp, true);

if($array === null)
	http_response_code(400);
else{
	$blockHeader = $array['result']['block_header'];
	header('Content-Type: application/json');
	echo json_encode(array(
			'major_version'=>$blockHeader['major_version'],
			'hash'=>$blockHeader['hash'],
			'reward'=>$blockHeader['reward'],
			'height'=>$blockHeader['height'],
			'timestamp'=>$blockHeader['timestamp'],
			'difficulty'=>$blockHeader['difficulty'],
			'hashrate'=>$blockHeader['difficulty']*60*2,
			'daemon'=>$daemonAddress,
	));
}
