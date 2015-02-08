<?php

namespace xpl\Bundle;

class Library extends BundleAbstract 
{
	protected $name;
	protected $dirpath;
	protected $booted = false;
	
	public function __construct($directory) {
		$this->dirpath = realpath($directory).DIRECTORY_SEPARATOR;
		$this->name = strtolower(basename($this->dirpath));
	}
	
	public function boot() {
		
		if (! $this->booted) {
			
			if (file_exists($this->dirpath.'bootstrap.php')) {
				require $this->dirpath.'bootstrap.php';
			}
			
			$this->booted = true;
		}
		
		return true;
	}
	
	public function shutdown() {	
	}
	
	final public function getType() {
		return 'library';
	}
	
	public function isBooted() {
		return (bool)$this->booted;
	}
	
}
