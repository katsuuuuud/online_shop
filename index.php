<?php

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/Container.php';
require_once __DIR__ . '/src/Router.php';

session_start();

(new Router(new Container()))->dispatch();