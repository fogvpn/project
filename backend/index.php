<?php
require __DIR__ . '/vendor/autoload.php';

Flight::set('flight.log_errors', true);

$pdo = require __DIR__ . '/src/db.php';

require __DIR__ . '/src/flight/login.php';
require __DIR__ . '/src/flight/register.php';
require __DIR__ . '/src/flight/me.php';
require __DIR__ . '/src/flight/payments.php';
require __DIR__ . '/src/flight/logout.php';
require __DIR__ . '/src/flight/config.php';
require __DIR__ . '/src/flight/traffic.php';

Flight::start();