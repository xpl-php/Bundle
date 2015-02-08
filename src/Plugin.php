<?php

namespace xpl\Bundle;

abstract class Plugin extends BundleAbstract 
{
	
	final public function getType() {
		return 'plugin';
	}
	
}
