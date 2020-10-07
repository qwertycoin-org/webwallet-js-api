![image](https://cdn.qwertycoin.org/images/press/other/qwc-github-3.png)

# Qwertycoin's Mobile- and Web Wallet API endpoint
The API servers are currently only used to optimize the communication with the daemon and compress the blockchain.

# How to compile & Deploy

### Prerequisites

You will need a VPS-Server with a running and fully synchronized Qwertycoin Daemon for this API Endpoint.

### Building

Upload everything to your web server.

Set the right permissions:

```
chmod 0777 cache
chmod 0777 config/lastRun.txt
```

Don't forget do modify or add your own Qwertycoin node to config/nodesList.php

# Cron task / Process
Precomputed data are build by another process. This process will call the Masari daemon and compute blocks into chunks of blocks to reduce network latency.
In order to do so, you will need to run the file blockchain.php with an environment variable "export=true". 
This file will shut down after 1h, and has a anti-concurrency mechanism built in.

One way to handle this is by running a cron task each minute with something like:
```
*/2 * * * * root cd /var/www/domain.com/webwallet-js-api && php cronjob.php
```

### Donate

```
QWC: QWC1K6XEhCC1WsZzT9RRVpc1MLXXdHVKt2BUGSrsmkkXAvqh52sVnNc1pYmoF2TEXsAvZnyPaZu8MW3S8EWHNfAh7X2xa63P7Y
```
```
BTC: 1DkocMNiqFkbjhCmG4sg9zYQbi4YuguFWw
```
```
ETH: 0xA660Fb28C06542258bd740973c17F2632dff2517
```
```
BCH: qz975ndvcechzywtz59xpkt2hhdzkzt3vvt8762yk9
```
```
XMR: 47gmN4GMQ17Veur5YEpru7eCQc5A65DaWUThZa9z9bP6jNMYXPKAyjDcAW4RzNYbRChEwnKu1H3qt9FPW9CnpwZgNscKawX
```
```
ETN: etnkJXJFqiH9FCt6Gq2HWHPeY92YFsmvKX7qaysvnV11M796Xmovo2nSu6EUCMnniqRqAhKX9AQp31GbG3M2DiVM3qRDSQ5Vwq
```
