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

// include project common functions
include(COMMON_PATH.'web_func.php');

/* -----v----- your code -----v----- */

$db = db(config('db'));

$news = $db -> prepare("SELECT * FROM `news` WHERE `id`=:id") -> execute(array(':id'=>1));

template::assign('news', $news[0]);
template::display('login', true);

```


Change Log
---------------------------
######Alpha 0.1

		Date£º2014-10-06<br>
		Contributor£º[nolan](http://www.nolanchou.com/)<br>
		* This is the first version.