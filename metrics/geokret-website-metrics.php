#!/usr/bin/env php

<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

//
// principle : provide GeoKrety webSite metrics for Prometheus
// - at cron interval (ex. 1 minute), some counter will be grabbed from database (ex. error count).
// - then pushgateway will be updated: clean metric group, and push new metric value
// NB/ pushgateway scrap interval (from prometheus config) must be set max to 1 minute
//
// TODO LIST
// - [x] use pushgateway alias to avoid using dynamic IP
// - [ ] study/add fallback when pushgateway is unavailable
// - [ ] construct MetricPublishService to push a collection of metrics to pushgateway
// - [ ] construct MetricCollectService to collect a collection of metrics from db
// - [ ] add this script to cron
//  ***************


// async http
// http://docs.php-http.org/en/latest/components/promise.html
// composer require php-http/curl-client guzzlehttp/psr7 php-http/message

use Http\Client\Exception;
use Http\Discovery\HttpAsyncClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;

$messageFactory = MessageFactoryDiscovery::find();
$httpAsyncClient = HttpAsyncClientDiscovery::find();

//~ ## pushgateway params
$jobName = "geokrety-website";
$instance = $config['prod_server_name'];
$pushGatewayHostPort = CONFIG_PUSH_GATEWAY_ALIAS;
$gatewayEndpoint = "http://$pushGatewayHostPort/metrics/job/$jobName";

$gatewayScrapIntervalSeconds = 60; // must be the same as http://pushgateway:9090/config :: pushgateway :: scrape_interval

//~ ## collect metrics
$curTime = date('Hi');
$collectedMetrics = "static_metric{instance=\"$instance\"} $curTime\n";

$link = GKDB::getLink();
$sql =<<<EOSQL
    SELECT COUNT(*) as sum, uid, severity
    FROM `gk-errory`
    WHERE timestamp > NOW() - INTERVAL $gatewayScrapIntervalSeconds SECOND
    GROUP BY uid,severity
EOSQL;

$result = mysqli_query($link, $sql);
while ($row = mysqli_fetch_array($result)) {
    list($errorCount, $errorUid, $errorSeverity) = $row;
    $collectedMetrics .= "errory_metric{instance=\"$instance\",uid=\"$errorUid\",severity=\"$errorSeverity\"} $errorCount\n";
}

$debugHtml = <<<EODEBUG
<h3>input</h3>
<pre>
$collectedMetrics
</pre>
EODEBUG;

//~ ## build metrics body
$postBody = <<<EOMETRIC
# TYPE errory_metric counter
# HELP errory_metric GeoKrety errory table metrics
$collectedMetrics

EOMETRIC;
//~ FAQ
// in case of "400" "Bad Request" - text format parsing error in line 1: unexpected end of input stream
// => check end of line (must be LF), and require extra white line before EOMETRIC


//~ ## clean metrics first
$deleteMetricsRequest = $messageFactory->createRequest('DELETE', $gatewayEndpoint);
$promise = $httpAsyncClient->sendAsyncRequest($deleteMetricsRequest);

//~ display delete result
// http://docs.guzzlephp.org/en/stable/psr7.html#responses
$response = $promise->wait(true);
$responseStatus = $response->getStatusCode();
$responsePhrase = $response->getReasonPhrase();
$responseBody = $response->getBody();
$debugHtml .= <<<EODEBUG
<h3>delete metrics result</h3>
<pre>
$responseStatus $responsePhrase
$responseBody
</pre>
EODEBUG;


//~ ## send metrics
$postHeaders = [];
// $postHeaders = ["Content-Type" => "application/x-www-form-urlencoded", "Accept" => "*/*", "Content-Length" => strlen($postBody)];
$postMetricRequest = $messageFactory->createRequest('POST', $gatewayEndpoint, $postHeaders, $postBody);

$promise = $httpAsyncClient->sendAsyncRequest($postMetricRequest);

//~ display post result
// http://docs.guzzlephp.org/en/stable/psr7.html#responses
$response = $promise->wait(true);
$responseStatus = $response->getStatusCode();
$responsePhrase = $response->getReasonPhrase();
$responseBody = $response->getBody();
$debugHtml .= <<<EODEBUG
<h3>post metrics result</h3>
<pre>
$responseStatus $responsePhrase
$responseBody
</pre>
EODEBUG;

//~ ## ouput reprot
echo $debugHtml;
