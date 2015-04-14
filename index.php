<?php

// session start
define("SESSION_ON", true);

// define framework config
define("CONFIG", '/conf/web.php');

// debug switch
define("DEBUG", true);

// include common
include('./common.php');


/* -----v----- start your code -----v----- */

// simplify use class
use pt\framework\debug\console as debug;
use pt\framework\template as template;

// include your project common functions.
// this is a demo that have some useful functions.
include(COMMON_PATH.'web_func.php');

// select from datebase
$db = db(config('db'));
$user = $db -> prepare("SELECT `name` FROM `user` WHERE `uid`=:uid") -> execute(array(':uid'=>1));

// print template
template::assign('user', $user[0]);
template::display('index');