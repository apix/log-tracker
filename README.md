# Log-Tracker, the tracking companion to APIx Log

[![Latest Stable Version](https://poser.pugx.org/apix/log-tracker/v/stable.svg)](https://packagist.org/packages/apix/log-tracker)  [![Build Status](https://travis-ci.org/frqnck/apix-log-tracker.png?branch=master)](https://travis-ci.org/frqnck/log-tracker)  [![Code Quality](https://scrutinizer-ci.com/g/frqnck/apix-log-tracker/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/frqnck/apix-log-tracker/?branch=master)  [![Code Coverage](https://scrutinizer-ci.com/g/frqnck/apix-log-tracker/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/frqnck/apix-log-tracker/?branch=master)  [![License](https://poser.pugx.org/apix/log-tracker/license.svg)](https://packagist.org/packages/apix/log-tracker)

An extension for the [Apix/Log](https://github.com/frqnck/apix-log) PSR-3 logger, allowing the integration of:
 * [GoogleAnalytics](src/GoogleAnalytics.php)
 * More to come...

Features:
 * Send tracking data asynchnonously (non-blocking).
 * Handles batched/deferred mode.
 * 100% Unit tested and compliant with PSR0, PSR1 and PSR2.
 * Continuously integrated against all modern PHP versions (**5.3 all the way through 7.0**, including **HHVM**).
 * Home repo is on [on github](https://github.com/frqnck/apix-log-tracker), and the Composer package is [on packagist](https://packagist.org/packages/frqnck/apix-log-tracker).

Feel free to comment, send pull requests and patches...

## Installation

Install the latest version via composer:

    $ composer require frqnck/apix-log-tracker

You require at least PHP 5.3.

## Basic usage, Google Analytics.

```php
use Apix\Log;

$options = [
    'tid' => '<UA-XX-XX>',    // Tracking/Property ID (required). 
    // 'cid' => '<UUID-v4>',  // Anonymous Client ID UUIDv4 (if not provided, auto-generated one)
    // ...                    // Any numbers of Google Analytics Parameters (see note). 
];

$logger = new GoogleAnalytics($options);
$logger->setDeferred(true); // recommneded, batched mode.

$dataToTrack = $logger->trackPage('http://foo.tld/...', 'Welcome page');
//$dataToTrack = $logger->trackEvent('category', 'action', 'label', 'value');

$logger->notice('GA Tracking', $dataToTrack);
```

Note: if required, you can add some additional [Google Analytics Parameters](https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters) to the `options` array.

## Adavnced usage.

TODO -- for now just follow [Apix Log Examples](https://github.com/frqnck/apix-log).