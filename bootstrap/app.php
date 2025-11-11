<?php

use System\Router\Routing;

require_once ('../config/app.php');
require_once ('../config/database.php');
require_once ('../routes/Web.php');
require_once ('../routes/Api.php');

$routing = new Routing();
$routing->run();