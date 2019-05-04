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

include 'nodesList.php';

header("Access-Control-Allow-Origin: *");

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

$randNode = array_rand($nodesList, 2);

$cacheLocation = 'cache';
$daemonAddress = $nodesList[$randNode[0]];
$rpcPort = 8197;
$coinSymbol = 'QWC';
