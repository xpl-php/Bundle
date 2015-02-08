<?php

namespace xpl\Bundle;

interface BundleInterface 
{
	
	/**
	 * Boots the bundle.
	 */
	public function boot();
	
	/**
	 * Shuts down the bundle.
	 */
	public function shutdown();
	
	/**
	 * Whether the bundle is booted.
	 * 
	 * @return boolean
	 */
	public function isBooted();
	
	/**
	 * Returns array of bundle names which this one overrides.
	 * 
	 * @return array 
	 */
	public function getOverrides();
	
	/**
	 * Returns array of bundle names that this one requires.
	 * 
	 * @return array
	 */
	public function getDependencies();
	
	/**
	 * Returns the bundle type.
	 * 
	 * @return string Bundle type.
	 */
	public function getType();
	
	/**
	 * Returns the bundle name.
	 * 
	 * @return string Bundle name.
	 */
	public function getName();
	
	/**
	 * Returns the bundle type and name like "<type>.<name>"
	 * 
	 * @return string Bundle ID.
	 */
	public function getIdentifier();
	
}
