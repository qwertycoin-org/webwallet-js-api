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

if(!empty($_GET["gen"]) && $_GET['gen'] == "1"){
  putenv("generate=true");
} else {
  putenv("generate=false");
}

function getTxWithHashes($txHashes){
	global $rpcPort;
	global $daemonAddress;
	$curl = curl_init();
	
	$body = json_encode(array(
		'txs_hashes'=>$txHashes,
		'decode_as_json'=>true
	));
	curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => 'http://'.$daemonAddress.':'.$rpcPort.'/gettransactions', CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $body));
	
	$resp = curl_exec($curl);
	curl_close($curl);
	$array = json_decode($resp, true);
	
	return $array;
}

function getBlockchainHeight(){
	global $rpcPort;
	global $daemonAddress;
	$curl = curl_init();
	
	curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => 'http://'.$daemonAddress.':'.$rpcPort.'/getheight', CURLOPT_POST => 1, CURLOPT_POSTFIELDS => ''));
	
	$resp = curl_exec($curl);
	curl_close($curl);
	$array = json_decode($resp, true);
	return $array['height'];
}


$outCount = 0;//to start at 0

function createOptimizedBock($startHeight, $endHeight){
	global $outCount;
	global $rpcPort;
	global $daemonAddress;
	$txHashesPerBlock = array();
	$txHashes = array();
	$txHashesMap = array();
	$txOutCountMap = array();
	
	$finalTransactions = array();
	$curl = curl_init();
	
	$minerTxs = [];
	
	$blockTimes = array();
	
	for($height = $startHeight; $height <= $endHeight; ++$height){
		//get the block hash
		$body = json_encode(array("jsonrpc" => "2.0", "id" => "0", "method" => "on_getblockhash", "params" => array($height)));
		curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => 'http://'.$daemonAddress.':'.$rpcPort.'/json_rpc', CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $body));
		$resp = curl_exec($curl);
		$array = json_decode($resp, true);
		$hash = $array["result"];
		//get the block details
		$body = json_encode(array("jsonrpc" => "2.0", "id" => "0", "method" => "f_block_json", "params" => array("hash" => $hash)));
		curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => 'http://'.$daemonAddress.':'.$rpcPort.'/json_rpc', CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $body));
		$resp = curl_exec($curl);
		$array = json_decode($resp, true);
		$blockJson = $array["result"]["block"];

		$blockTxHashes = array();
		$blockTimes[$height] = $blockJson['timestamp'];
		$txs = $blockJson['transactions'];
		foreach($txs as $tx){
			$blockTxHashes[] = $tx["hash"];
			//$tx["block_timestamp"] = $blockJson['timestamp'];
		}
		$txHashesPerBlock[$height] = $blockTxHashes;
		
		foreach($blockTxHashes as $txHash){
			$txHashesMap[$txHash] = $height;
			$txHashes[] = $txHash;
			$txOutCountMap[$txHash] = $outCount;
		}
		
	}


	for($height = $startHeight; $height <= $endHeight; ++$height){

		$body = json_encode(array(
			'transactionHashes'=>$txHashesPerBlock[$height]
		));
		
		curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => 'http://'.$daemonAddress.':'.$rpcPort.'/get_transaction_details_by_hashes', CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $body));
		
		$resp = curl_exec($curl);
		
		$decodedJson = json_decode($resp, true);

		if(!isset($decodedJson['transactions'])){
			$rawTransactions = [];
		}else{
			$rawTransactions = $decodedJson['transactions'];
		}

		for($iTransaction = 0; $iTransaction < count($rawTransactions); ++$iTransaction){

			$rawTransaction = $rawTransactions[$iTransaction];

			$finalTransaction = $rawTransaction;
			unset($finalTransaction['signatures']);
			unset($finalTransaction['ts']);
			unset($finalTransaction['unlockTime']);
			unset($finalTransaction['signaturesSize']);
			$finalTransaction['global_index_start'] = $outCount;
			$finalTransaction['ts'] = $blockJson['timestamp'];
			$finalTransaction['height'] = $height;
			$finalTransaction['hash'] = $rawTransaction['hash'];
			$finalTransactions[] = $finalTransaction;
			
			$voutCount = count($finalTransaction['outputs']);
			$outCount += $voutCount;
		}
	}
	
	curl_close($curl);

	return $finalTransactions;
}

function retrieveCache($startHeight, $endHeight, $decoded=true){
	global $cacheLocation;
	$content = @file_get_contents($cacheLocation.'/'.$startHeight.'-'.$endHeight);
	if($content === false)
		return null;
	if($decoded)
		$content = json_decode($content, true);
	return $content;
}

function saveCache($startHeight, $endHeight, $content){
	global $cacheLocation;
	file_put_contents($cacheLocation.'/'.$startHeight.'-'.$endHeight, json_encode($content));
}

if(getenv('generate') !== 'true'){
	if(!is_int($_GET['height']+0)){
		http_response_code(400);
		exit;
	}
	$startHeight = (int)$_GET['height'];
	$realStartHeight = $startHeight;
	$startHeight = floor($startHeight/10)*10;
	$endHeight = $startHeight + 10;
	if($startHeight < 0) $startHeight = 0;
	
	$blockchainHeight = getBlockchainHeight();
	if($blockchainHeight === null) $blockchainHeight = $endHeight+10;
	if($endHeight > $blockchainHeight){
		$endHeight = $blockchainHeight;
	}

	$cacheContent = retrieveCache($startHeight, $endHeight, false);
	if($cacheContent === null){
		http_response_code(400);
	}else{
		$cacheContent = json_decode($cacheContent, true);
		$txForUser = [];
		foreach($cacheContent as $tx){
			if($tx['height'] >= $realStartHeight){
				$txForUser[] = $tx;
			}
		}
		
		header('Content-Type: application/json');
		echo json_encode($txForUser);
	}
} else {
	$lastRunStored = @file_get_contents('./config/lastRun.txt');
	if($lastRunStored===false)
		$lastRunStored = 0;
	else
		$lastRunStored = (int)$lastRunStored;
	
	if($lastRunStored+1/**60*/ >= time())//concurrent run, 1min lock
		exit;
	file_put_contents('./config/lastRun.txt', time());
	
	$lastScanHeight = 0;
	$timeStart = time();
	$lastOutCount = 0;
	while(time() - $timeStart < 59*60){
		$blockchainHeight = getBlockchainHeight();
		$lastBlockCacheContent = null;
		for($startHeight = $lastScanHeight; $startHeight <= $blockchainHeight; $startHeight += 10){
			
			$endHeight = $startHeight + 10;
			$realStartHeight = $startHeight;

			if($endHeight > $blockchainHeight){
				$endHeight = $blockchainHeight;
			}
						
			$cacheContent = retrieveCache($realStartHeight, $endHeight, false);

			if($cacheContent === null){
				if($realStartHeight > 1){
					$lastBlockCacheContent = retrieveCache($realStartHeight-10, $realStartHeight, false);
					$decodedContent = json_decode($lastBlockCacheContent, true);
					if(count($decodedContent) > 0){
						$lastTr = $decodedContent[count($decodedContent) - 1];
						$outCount = $lastTr['global_index_start'] + count($lastTr['outputs']);

					}else{
						var_dump('Missing compacted block file. Weird case');
						exit;
					}
					$lastBlockCacheContent = null;
				}
				
				$cacheContent = createOptimizedBock($realStartHeight, $endHeight);
				saveCache($realStartHeight, $endHeight, $cacheContent);
				$cacheContent = json_encode($cacheContent);
			}
			
			var_dump($outCount);
		}
		
		$lastOutCount = $outCount;
		
		$allBlocksFiles = scandir($cacheLocation);
		foreach($allBlocksFiles as $filename){
			if($filename !== '.' && $filename !== '..'){
				$blocksNumbers = explode('-', $filename);
				if($blocksNumbers[1] % 10 !== 0){
					if($blocksNumbers[1]+1  < $blockchainHeight) {
						//to be sure if other client are using the last one
						unlink($cacheLocation . '/' . $filename);
					}
				}
			}
		}
		
		$lastScanHeight = floor($blockchainHeight/10)*10;
		
		file_put_contents('./config/lastRun.txt', time());
		sleep(10);
	}
}
