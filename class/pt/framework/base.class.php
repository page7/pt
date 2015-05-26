<?php
/**
 * Base Class (Abstract!)
 * Use "extends" method to add a plugin
 *  eg : // obj cache not have and method.
 *       $cache = new cache();
 *       // extend memcache plugin.
 *       $cache -> extends('memcache', array('host'=>...));
 *       // cache have all memcache methods.
 *       $cache -> memcache_write();
 * In reality cache add memcache plugin,
 * only need do this: $cache = new cache(array('type'=>'memcache', 'host'=>...));
 +-----------------------------------------
 * @category    php
 * @package     pt\framework
 * @author      page7 <zhounan0120@gmail.com>
 * @version     $Id$
 */

namespace pt\framework;


abstract class base
{
    // parent
    protected $_parent = null;

    // Record subclass obj
    protected $_children = array();

    // Record subclass's methods
    protected $_child_methods = array();

    // Record subclass's properties
    protected $_child_vars = array();



    // Construct, some class must pass a config
    abstract function __construct($config = array());



    /**
     * Magic method __call.
     * If subclass have the method that called then use it,
     *     else test custom's method "_call" to transfer request.
     +-----------------------------------------
     * @access public
     * @param string $method
     * @param mixed $args
     * @return void
     */
    final public function __call($method, $args)
    {
        if (isset($this -> _child_methods[$method]))
        {
            $class = $this -> _child_methods[$method];
            $object = &$this -> _children[$class];
            return call_user_func_array(array($object, $method), $args);
        }
        else
        {
            // Not have the method, go to call "_call";
            if (method_exists($this, '_call'))
                return call_user_func_array(array($this, '_call'), array($method, $args));
            else
                trigger_error('Undefined Method:'.get_class($this).'->'.$method, E_USER_WARNING);
        }
    }



    /**
     * Set a undefined property,
     * if it's in plugins' properties, change it.
     +-----------------------------------------
     * @access public
     * @param mixed $name
     * @param mixed $value
     * @return void
     */
    final public function __set($name, $value)
    {
        if (isset($this -> _child_vars[$name]))
        {
            $class = $this -> _child_vars[$name];
            $this -> _children[$class] -> $name = $value;
        }
    }



    /**
     * Get a property (unpublic prop)
     +-----------------------------------------
     * @access public
     * @param mixed $name
     * @return void
     */
    final public function __get($name)
    {
        if (isset($this -> _child_vars[$name]))
        {
            $class = $this -> _child_vars[$name];
            return $this -> _children[$class] -> $name;
        }

        return null;
    }



    /**
     * Extend a plugin
     +-----------------------------------------
     * @access public
     * @param  string $class
     * @return void
     */
    final public function extend($class, $config=array())
    {
        return $this -> __ext($class, $config, '');
    }



    /**
     * Extend a plugin : core function.
     * This function is a protected method.
     * So, every class can use it to extend itself in development.
     +-----------------------------------------
     * @access protected
     * @param string $class
     * @param array  $config
     * @param string $path
     * @return void
     */
    final protected function __ext($class, $config=array(), $path='')
    {
        if (!$this -> children[$class])
        {
            $name = get_called_class();
            $_path = str_replace('\\', '/', $name);

            if (!$path)
                $file = CLASS_PATH.$_path.$class;
            else
                $file = $path.$class;

            import($file);
            $class = $name.'\\'.$class;
            $extend = new $class($config);

            // Verify the plugin is the class's subclass.
            if (!($extend instanceof $name))
                trigger_error("have a fatal error about plugin: {$name}.extend({$class}).", E_USER_ERROR);

            // Use ReflectionClass to get more information of method/var.
            $reflection = new \ReflectionClass($extend);

            // Sync $this vars.
            $extend -> __setParent($this);

            // Record subclass's methods.
            foreach ($reflection -> getMethods() as $method)
            {
                $_name = $method -> getName();

                if ($method -> class == $class && $method -> isPublic())
                {
                    if (in_array($_name, array('__construct', '__destruct', '__get', '__set', '__call', '__toString', '_call')))
                        continue;

                    if (isset($this -> _child_methods[$_name]))
                        trigger_error("plugin's method '{$_name}' is already exists: {$name}.extend({$class}).", E_USER_NOTICE);

                    if (!$method -> isStatic())
                        $this -> _child_methods[$_name] = $class;
                }
            }

            // Record subclass's properties.
            foreach ($reflection -> getProperties() as $var)
            {
                $_name = $var -> getName();

                if ($var -> class == $class && $var -> isPublic())
                {
                    if (isset($this -> _child_vars[$_name]))
                        trigger_error("plugin's property '{$_name}' is already exists: {$name}.extend({$class}).", E_USER_NOTICE);

                    $this -> _child_vars[$_name] = $class;
                }
            }

            // Record subclass's instance.
            $this -> _children[$class] = &$extend;
        }

        // Return the instance.
        return $this -> _children[$class];
    }



    /**
     * This method is used in subclass.
     * Make any parent obj's properties sync this properties by address.
     +-----------------------------------------
     * @access public
     * @param  object $object
     * @return void
     */
    final public function __setParent($object)
    {
        $reflection = new \ReflectionClass($this);

        foreach ($reflection -> getProperties() as $properties)
        {
            $name = $properties -> getName();
            // Subclass's property will overwrite any other,
            // so don't sync it.
            if ($properties -> class != get_class($this))
                $this -> $name = &$object -> $name;
        }

        $this -> _parent = &$object;
    }



    /**
     * merger config options
     +-----------------------------------------
     * @access public
     * @param  array $config
     */
    final public function __config($config)
    {
        $reflection = new \ReflectionClass($this);

        foreach ($config as $k => $c)
        {
            if ($reflection -> hasProperty($k) && $property = $reflection -> getProperty($k))
            {
                if ($property -> isStatic())
                    $property -> setValue($c);
                else if ($property -> isPublic())
                    $property -> setValue($this, $c);
                else
                    $this -> $k = $c;
            }
        }
    }



    /**
     * get a class instance
     +-----------------------------------------
     * @access public
     * @param  array $config
     */
    final static function init($config=array())
    {
        static $_instances = array();

        $classname = get_called_class();
        if (!$config)
        {
            if (substr($classname, 0, 13) === 'pt\\framework\\')
            {
                $classname_arr = explode('\\', $classname);
                $config_name = array_pop($classname_arr);
            }
            else
            {
                $config_name = str_replace('\\', '/', $classname);
            }

            $config = config($config_name);
        }

        $ids = md5(self::__serialize($config));

        if(isset($_instances[$ids]))
            return $_instances[$ids];

        return $_instances[$ids] = new $classname($config);
    }




    /**
     * Advanced serialize
     +-----------------------------------------
     * @access final
     * @param mixed $array
     * @return void
     */
    private static function __serialize($array)
    {
        ksort($array);
        foreach ($array as $k => $v)
        {
            $type = gettype($v);
            switch ($type)
            {
                case 'array':
                    $array[$k] = self::__serialize($v);
                    break;
                case 'object':
                    $array[$k] = getidx($v);
                    break;
            }
        }

        return serialize($array);
    }


}
