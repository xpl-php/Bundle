<?php

namespace xpl\Bundle;

interface ProviderInterface 
{
	
	/**
	 * Provides a bundle by type and name.
	 * 
	 * @param string $type Bundle type.
	 * @param string $name Bundle name.
	 */
	public function provideBundle($type, $name);
	
}
