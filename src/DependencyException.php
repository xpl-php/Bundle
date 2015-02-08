<?php

namespace xpl\Bundle;

class DependencyException extends Exception 
{
	
	protected $missing;
	
	public function setMissing(array $missing) {
		$this->missing = $missing;
	}
	
	public function getMissing() {
		return isset($this->missing) ? $this->missing : array();
	}
	
}
