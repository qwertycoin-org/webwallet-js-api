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

header("Access-Control-Allow-Origin: *");

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

$cacheLocation = 'cache';
$daemonAddress = '127.0.0.1';
$rpcPort = 8197;
$coinSymbol = 'QWC';
