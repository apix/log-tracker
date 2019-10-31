# Log-Tracker, the tracking companion to [Apix-Log](//github.com/apix/log)

[![Latest Stable Version](https://poser.pugx.org/apix/log-tracker/v/stable.svg)](https://packagist.org/packages/apix/log-tracker)  [![Build Status](https://travis-ci.org/apix/log-tracker.png?branch=master)](https://travis-ci.org/apix/log-tracker)  [![Code Quality](https://scrutinizer-ci.com/g/apix/log-tracker/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/apix/log-tracker/?branch=master)  [![Code Coverage](https://scrutinizer-ci.com/g/apix/log-tracker/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/apix/log-tracker/?branch=master)  [![License](https://poser.pugx.org/apix/log-tracker/license.svg)](https://packagist.org/packages/apix/log-tracker)

An extension for the [Apix-Log](//github.com/apix/log) PSR-3 logger which adds log tracking to:
 * [GoogleAnalytics](src/GoogleAnalytics.php),
 * [Dashbot](src/Dashbot.php).

Features:
 * Send tracking data asynchnonously (non-blocking).
 * Handles batched/deferred mode.
 * 100% Unit tested and compliant with PSR0, PSR1 and PSR2.
 * Continuously integrated against all modern PHP versions (**5.3 all the way through 7.3**, ~~including HHVM~~).
 * Home repo is on [on github](//github.com/apix/log-tracker), and the Composer package is [on packagist](//packagist.org/packages/apix/log-tracker).

Feel free to comment, send pull requests and patches...

## Installation

Install the latest version via composer:

    $ composer require apix/log-tracker

You require at least PHP 5.3.

## Basic usage, Google Analytics.

```php
use Apix\Log;

$options = [
    'tid' => '<UA-XX-XX>',   // Tracking/Property ID (required). 
    //'cid' => '<UUID-v4>',  // Anonymous Client ID UUIDv4 (if not provided, auto-generated one).
    //...                    // Any numbers of Google Analytics Parameters (see notes). 
];

$ga_logger = new GoogleAnalytics($options);
$ga_logger->setDeferred(true); // Enable batched mode (recommneded).

$dataToTrack = $ga_logger->getPage('http://foo.tld/...', 'Welcome page');
//$dataToTrack = $ga_logger->getEvent('category', 'action', 'label', 'value');
//$dataToTrack = $ga_logger->getSocial('action', 'network', 'target');
//$dataToTrack = $ga_logger->getException('description');
//$dataToTrack = $ga_logger->getApp('name', 'version', 'id');

$ga_logger->notice('GA Tracking', $dataToTrack);
```

Notes:
 * The log level and message are not forwarded to Google Analytics (TBD).
 * If required, you can add some additional [Google Analytics Parameters](https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters) to the `options` array such as `uip` (user IP), `ua` (user agent), etc... If not provided, these will be generated and/or guessed from the current context.

## Basic usage, Dashbot.

```php
use Apix\Log;

$dashbot_logger = new Dashbot('<API-Key');
//$dashbot_logger->setPlatform('facebook'); // 'generic' (default), 'slack', 'kik'.
//$dashbot_logger->setGlobalTag('myTag');   // Useful to combined metrics.

$messages_received = ["text" => "Hi, bot", "userId" => "..."];
$dataToTrack = $dashbot_logger->incoming($messages_received);
//$dataToTrack = $dashbot_logger->incoming($messages_received, "localTag"); // Override the global tag

$messages_sent = ["text" => "Hello, user", "userId" => "..."];
$dataToTrack = $logger->outgoing($messages_sent);

$dashbot_logger->info('Dashbot Tracking', $dataToTrack);
```

Notes:
 * The log level and message are not forwarded to Dashbot (TBD).
 * A local tag (which override the main global tag) can be passed to the `incoming` and `outgoing` methods as a second argument.

## Advanced usage.

Please for now just follow [Apix Log Examples](//github.com/apix/log).
