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

$body = json_encode(array());
curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => 'http://'.$daemonAddress.':'.$rpcPort.'/getheight'));
$resp = curl_exec($curl);
curl_close($curl);

$array = json_decode($resp, true);

if($array === null)
	http_response_code(400);
else
	echo $array['height'];
