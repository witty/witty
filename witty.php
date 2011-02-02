<?php
/**
 * Witty Class
 *
 * @dependency arr
 * @author lzyy http://blog.leezhong.com
 * @homepage https://github.com/witty/witty
 * @version 0.1.1
 */
class Witty
{
	protected static $_config_path;
	protected static $_config_prefix;

	protected static $_module_path;
	protected static $_helper_path;
	protected static $_vendor_path;

	/**
	 * init system
	 */
	public static function init()
	{
		static $init;
		if (!empty($init))
			return ;

		Witty::$_module_path= realpath(dirname(__FILE__).'/../../modules').'/';
		Witty::$_helper_path = realpath(dirname(__FILE__).'/../../helpers').'/';
		Witty::$_vendor_path = realpath(dirname(__FILE__).'/../../vendors').'/';
		spl_autoload_register('Witty::autoload');
		Witty::init_modules();

		$init = TRUE;
	}

	/**
	 * set config dir. if a module needs config, you can put it in a config dir,
	 * eg. config/cache.php, when Witty::instance('cache'), it will search for config/cache.php,
	 * if file exists, load it, and merge with default config
	 *
	 * @param string $path directory name
	 * @param string $prefix config file's prefix, like 'witty'
	 */
	public static function set_config_dir($path, $prefix = '')
	{
		Witty::$_config_path = rtrim($path, '/').'/';
		Witty::$_config_prefix = $prefix;
	}

	/**
	 * init module, if there's init.php in it ,require it
	 */
	public static function init_modules()
	{
		foreach (glob(Witty::$_module_path.'*', GLOB_ONLYDIR) as $dir)
		{
			// without "_" prefixed is enabled, and if there is init.php, require it
			if ($dir[0] !== '_' && file_exists($dir.'/init.php'))
			{
				require $dir.'/init.php';
			}
		}
	}

	/**
	 * fetch item's config if config_dir is set
	 *
	 * @param string $item
	 * @return mixed
	 */
	public static function get_config($item)
	{
		static $configs = array();
		if (isset($configs[$item])) 
			return $configs[$item];

		$file = strtolower($item);

		if (strpos($item, '_') !== FALSE)
		{
			list($file,) = explode('_', $item, 2);
			$file = strtolower($file);
		}
		
		if (!isset($configs[$file]))
		{
			$configs[$file] = array();

			if (!empty(self::$_config_path))
			{
				$config_file = self::$_config_path.self::$_config_prefix.$file.'.php';
				if (is_file($config_file))
				{
					$configs[$file] = include $config_file;
				}
			}
			else
			{
				return NULL;
			}
		}

		return isset($configs[$file][$item]) ? $configs[$file][$item] : NULL;
		
	}

	/**
	 * global factory method
	 *
	 * @param string $class class name
	 * @param string $id factory use this id to return different object
	 * @param array $config class's config
	 * @return object
	 * @edit 0.1.1 add id param
	 */
	public static function factory($class, $id, $config = NULL)
	{
		if (is_null($config))
			$config = Witty::get_config($class);
		$obj = new $class($config, $id);
		return $obj;
	}

	/**
	 * global instance method
	 *
	 * @param string $class class name
	 * @param array $config class's config
	 * @return object
	 */
	public static function instance($class, $config = NULL)
	{
		static $classes = array();
		if (is_null($config))
			$config = Witty::get_config($class);
		if (!isset($classes[$class]))
		{
			$classes[$class] = new $class($config);
		}
		return $classes[$class];
	}

	/**
	 * auto load class
	 * 
	 * @param string $classname 类名
	 * @return boolean
	 */
	public static function autoload($classname)
	{
		$classname  = strtolower($classname);
		$maybe_helper = FALSE;
		if (strpos($classname, '_') === FALSE)
		{
			$classfile = $classname.'/'.$classname.'.php';
			$maybe_helper = TRUE;
		}
		else
		{
			$classfile = str_replace('_', '/', $classname).'.php';
		}
		if (file_exists(Witty::$_module_path.$classfile))
		{
			require Witty::$_module_path.$classfile;
			return TRUE;
		}
		if ($maybe_helper)
		{
			$classfile = Witty::$_helper_path.$classname.'.php';
			if (file_exists($classfile))
			{
				require $classfile;
				return TRUE;
			}
		}
		return FALSE;
	}
}
