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

$body = file_get_contents('php://input');

curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => 'http://'.$nodesList[0].':'.$rpcPort.'/sendrawtransaction', CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $body));

$resp = curl_exec($curl);
curl_close($curl);

header('Content-Type: application/json');
echo $resp;
