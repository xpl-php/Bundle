<?php

namespace xpl\Bundle;

class InvalidProviderException extends Exception 
{
	
	public function __construct($message = '', $code = 0, \Exception $previous = null) {
		
		if (empty($message)) {
			$message = "Provider must be instance of 'xpl\\Bundle\\ProviderInterface' or callable.";
		}
		
		parent::__construct($message, $code, $previous);
	}
	
}
