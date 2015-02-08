<?php

namespace xpl\Bundle;

interface InstallableInterface
{
	
	public function isInstalled();
	
	public function install();
	
	public function uninstall();
	
}
