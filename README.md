pt
===========================
######A simple / native PHP Framework to do something PHP is not good at.



Introduction
---------------------------
This PHP framework is tentative. I hope it can provide more possibilities about PHP framework's develop.<br>

#####1. A especial base class
It can make subclass have `prototype` like javascript. So you can update your project with less code.<br>
```php
class a extends base
{
	protected $a = 'hello';

	public function __construct($config=array()){ }
}

class b extends a
{
	public function __construct($config=array()){ }

	public function say()
	{
		echo $this -> a;
	}

	public function change()
	{
		$this -> a = ' world';
	}
}

$a = new a();
$a -> extend('b'); 		// like prototype

$a -> say();			// output: hello
$a -> change();
$a -> say();			// output: world

```


#####2. Integrated Action / Filter class like wordpress
They are very useful in project. so pt integrated them.<br>


#####3. More...
You can read pt's code after read "How to use". This is a simple framework, you will like to use and bulid it by yourself.<br>
Welcome everyone to make it better. :)<br>


How To Use
---------------------------
This is a multi-entry framework. You only need include common.php in your file.<br>
Documents is building... :P<br>

```php
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
use pt\framework\db as db;

// include your project common functions.
// this is a demo that have some useful functions.
include(COMMON_PATH.'web_func.php');

// select from datebase
$db = db::init();
$user = $db -> prepare("SELECT `name` FROM `user` WHERE `uid`=:uid") -> execute(array(':uid'=>1));

// print template
template::assign('user', $user[0]);
template::display('index');

```


Change Log
---------------------------
######Alpha 0.1

		Date: 2014-10-06
		Contributor: nolan
		* This is the first version.

######Alpha 0.2

		Date: 2015-04-13
		Contributor: nolan
		* Add namespace "pt\framework" and "pt\tool".
		* Add "pt\framework\debug" class.
		* Add "pt\framework\debug\console" class to print debug message into browser's console (Firebug/Chrome/..).
		* Move function "charset_convert" into class "pt\tool\string".
		* Move function "path_by_str" into class "pt\tool\string", rename "pad_split".
		* Move functions "_is_writable", "_mkdir", "mkdirs" into a new class "pt\tool\file".
		* Remove function "addslashes_deep", "trace".
