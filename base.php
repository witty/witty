<?php
/**
 * Base Witty Class
 *
 * @author lzyy http://blog.leezhong.com
 * @version 0.1.1
 */
class Witty_Base
{
	protected $_config = array();

	protected $_imported_funcs = array();

	//{{{config
	/**
	 * something to do before set config
	 */
	protected function _before_config()
	{
	}

	/**
	 * something to do after config
	 */
	protected function _after_config()
	{
	}

	/**
	 * something to do after construct
	 */
	protected function _after_construct($id)
	{
	}

	/**
	 * config construct
	 *
	 * @param array $config
	 * @param string $id 
	 * @edit 0.1.1 add id param
	 */
	public function __construct($config = NULL, $id = NULL)
	{
		// pass config param
		$this->_before_config($config);
		if (!empty($config))
		{
			$this->set_config($config);
		}
		$this->_after_config();

		$this->_after_construct($id);
	}

	/**
	 * set config
	 *
	 * @param string | array $key config's key or an array, if array is provided , ignore the 2nd param
	 * @param string $val config value
	 */
	public function set_config($key , $val = NULL)
	{
		if (is_string($key))
		{
			$this->_config[$key] = $val;
		}
		elseif (is_array($key))
		{
			$this->_config = Arr::merge($this->_config, $key);
		}
	}

	/**
	 * get config
	 *
	 * @param string $key config key
	 * @param mixed $default default
	 * @return mixed
	 */
	public function get_config($key = NULL, $default = NULL)
	{
		if (empty($key))
		{
			return $this->_config;
		}
		else
		{
			return isset($this->_config[$key]) ? $this->_config[$key] : $default;
		}
	}
	//}}}

	//{{{ behavior
	public function __call($method, $args)
	{
		array_unshift($args, &$this);
		if (isset($this->_imported_funcs[$method]))
		{
			call_user_func_array(array($this->_imported_funcs[$method], $method), $args);
		}
	}

	public function attach_behavior($class)
	{
		$obj = new $class();
		$funcs = get_class_methods($obj);
		foreach ($funcs as $func)
		{
			$this->_imported_funcs[$func] = &$obj;
		}
	}
	//}}}

	//{{{ getter / setter
	public function __set($key, $val)
	{
		$setter = 'set_'.$key;
		if (method_exists($this, $setter))
		{
			return $this->$setter($val);
		}
	}

	public function __get($key)
	{
		$getter = 'get_'.$key;
		if (method_exists($this, $getter))
		{
			return $this->$getter();
		}
	}
	//}}}
}
