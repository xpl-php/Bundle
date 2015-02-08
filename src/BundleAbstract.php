<?php

namespace xpl\Bundle;

abstract class BundleAbstract implements BundleInterface 
{
	
	abstract public function boot();
	
	abstract public function shutdown();
	
	abstract public function isBooted();
	
	abstract public function getType();
	
	public function getOverrides() {
		return array();
	}
	
	public function getDependencies() {
		return array();
	}
	
	public function getName() {
		
		if (! isset($this->name)) {
			
			$name = strtolower(get_class($this));
			
			if (false !== $pos = strrpos($name, '\\')) {
				$name = substr($name, $pos+1);
			}
			
			$this->name = str_replace(array('bundle', $this->getType()), '', $name);
		}
		
		return $this->name;
	}
	
	public function getIdentifier() {
		return $this->getType().'.'.$this->getName();
	}
	
}
