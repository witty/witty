<?php
class Witty_Exception extends Exception
{
	public function __construct($message, array $variables = NULL, $code = 0)
	{
		if(!empty($variables))
			$message = strtr($message, $variables);
		parent::__construct($message, $code);
	}
}

