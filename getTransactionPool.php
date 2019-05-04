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
$body = json_encode(array("jsonrpc" => "2.0", "id" => "0", "method" => "f_on_transactions_pool_json", "params" => ''));
curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => 'http://'.$daemonAddress.':'.$rpcPort.'/json_rpc', CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $body));
$resp = curl_exec($curl);

//now get the Tx details
$jsonMempool = json_decode($resp, true);
$rawTransactions = $jsonMempool["result"]["transactions"];
$txHashes = array();
for($iTransaction = 0; $iTransaction < count($rawTransactions); ++$iTransaction){
	$txHashes[] = $rawTransactions[$iTransaction]["hash"];
}

$body = json_encode(array(
			'transactionHashes'=>$txHashes
));
curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => 'http://'.$daemonAddress.':'.$rpcPort.'/get_transaction_details_by_hashes', CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $body));
$resp = curl_exec($curl);
$decodedJson = json_decode($resp, true);
curl_close($curl);

$jsonMempool = json_decode($resp, true);
header('Content-Type: application/json');
echo json_encode($jsonMempool['transactions']);
