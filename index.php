<?php

// session start
define("SESSION_ON", true);

// define project's config
define("CONFIG", '/conf/web.php');

// debug switch
define("DEBUG", true);

// include framework entrance file
include('./common.php');


/* -----v----- start your code -----v----- */

// simplify use class
use pt\framework\debug\console as debug;
use pt\framework\template as template;
use pt\framework\route as route;

// include your project common functions.
// this is a demo that have some useful functions.
include(COMMON_PATH.'web_func.php');

// start your code
$callback = function($arg=null)
{
    if (empty($arg)) $arg = 'World';

    debug::log("use route to get \$_GET data: \n".var_export($_GET, true));

    template::assign('name', $arg);
    template::display('index');
};

route::add('/', $callback, '$m/$n/id/$id');
route::init();
