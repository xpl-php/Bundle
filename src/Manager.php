<?php

namespace xpl\Bundle;

use UnexpectedValueException;
use xpl\Bundle\BundleInterface as Bundle;
use xpl\Bundle\ProviderInterface as Provider;

class Manager 
{
	/**
	 * Bundles of joy.
	 * @var array
	 */
	protected $bundles = array();
	
	/**
	 * Callbacks or ProviderInterfaces that provide bundles.
	 * @var array
	 */
	protected $providers = array();
	
	/**
	 * Sets an object or callback that provides a bundle upon request.
	 * 
	 * @param string $bundle_name
	 * @param \xpl\Bundle\ProviderInterface|callable $provider
	 * @return void
	 */
	public function provide($bundle_name, $provider) {
		
		if (! $provider instanceof Provider && ! is_callable($provider)) {
			throw new InvalidProviderException;
		}
		
		$this->providers[strtolower($bundle_name)] = $provider;
	}
	
	/**
	 * Sets an object or callback that provides bundles of a specific type.
	 * 
	 * @param string $bundle_type Type of bundle.
	 * @param \xpl\Bundle\ProviderInterface|callable $provider
	 * @return void
	 */
	public function provideType($bundle_type, $provider) {
		
		if (! $provider instanceof Provider && ! is_callable($provider)) {
			throw new InvalidProviderException;
		}
		
		$this->providers[strtolower($bundle_type)] = $provider;
	}
	
	/**
	 * Sets an object or callback that provides multiple bundles.
	 * 
	 * @param array $bundles Indexed array of bundle names.
	 * @param \xpl\Bundle\ProviderInterface|callable $provider
	 * @return void
	 */
	public function provideMultiple(array $bundles, $provider) {
		
		if (! $provider instanceof Provider && ! is_callable($provider)) {
			throw new InvalidProviderException;
		}
		
		foreach($bundles as $bundle) {
			$this->providers[strtolower($bundle)] = $provider;
		}
	}
	
	/**
	 * Sets a bundle.
	 * 
	 * Overwrite this function to do stuff to bundles as they're set (e.g. inject DI).
	 * 
	 * @param \xpl\Bundle\BundleInterface $bundle
	 * @return void
	 */
	public function setBundle(Bundle $bundle) {
		$this->bundles[strtolower($bundle->getIdentifier())] = $bundle;
	}
	
	/**
	 * Returns a bundle by name, booting from provider if necessary.
	 * 
	 * @param string $name Bundle name.
	 * @return \xpl\Bundle\BundleInterface Bundle or null on failure.
	 */
	public function getBundle($name) {
		
		$name = strtolower($name);
		
		if (isset($this->bundles[$name]) || $this->setBundleFromProvider($name)) {
			return $this->bundles[$name];
		}
		
		return null;
	}
	
	/**
	 * Checks whether a bundle exists.
	 * 
	 * A bundle exists when it:
	 * (a) has been created (and possibly booted), or
	 * (b) has a provider registered (exclusively or for its type)
	 * 
	 * @param string $name Bundle name.
	 * @return boolean True if bundle exists, otherwise false.
	 */
	public function exists($name) {
			
		$name = strtolower($name);
		
		if (isset($this->bundles[$name]) || isset($this->providers[$name])) {
			return true;
		}
		
		if (! empty($this->providers)) {
			return isset($this->providers[strstr($name, '.', true)]);
		}
		
		return false;
	}
	
	/**
	 * Checks whether a given bundle exists and has been booted.
	 * 
	 * @param string $name Bundle name.
	 * @return boolean True if exists and booted, otherwise false.
	 */
	public function isBooted($name) {
		
		$name = strtolower($name);
		
		if (isset($this->bundles[$name])) {
			return $this->bundles[$name]->isBooted();
		}
		
		return false;
	}
	
	/**
	 * Boots a bundle.
	 * 
	 * @param string $name Bundle name.
	 * @return boolean True if booted, otherwise false.
	 */
	public function boot($name) {
		
		if ($bundle = $this->getBundle($name)) {
			return $this->bootBundle($bundle);
		}
		
		return false;
	}
	
	/**
	 * Boots a bundle, including its dependencies.
	 * 
	 * @param \xpl\Bundle\BundleInterface $bundle
	 * @return boolean
	 */
	protected function bootBundle(Bundle $bundle) {
		
		// Boot bundle dependencies
		$this->bootDependencies($bundle);
		
		// Shutdown and remove any bundles that this bundle overrides.
		$this->shutdownOverrides($bundle);
		
		// If installable and not installed, install.
		if ($bundle instanceof InstallableInterface && ! $bundle->isInstalled()) {
			$bundle->install();
		}
		
		$bundle->boot();
		
		return true;
	}
	
	/**
	 * Sets a bundle object from its provider.
	 * 
	 * @param string $name Bundle name.
	 * @return boolean True if bundle was provided, otherwise false.
	 * @throws \UnexpectedValueException if an implementation of BundleInterface is not provided.
	 */
	protected function setBundleFromProvider($name) {
		
		$bundle_type = null;
		$bundle_name = $name;
		
		if (false !== strpos($name, '.')) {
			list($bundle_type, $bundle_name) = explode('.', $name, 2);
		}
		
		if (! $provider = $this->getProviderFor($bundle_type, $bundle_name)) {
			return false;
		}
		
		if ($provider instanceof Provider) {
			$bundle = $provider->provideBundle($bundle_type, $bundle_name);
		} else {
			$bundle = call_user_func($provider, $bundle_type, $bundle_name);
		}
		
		if (! $bundle instanceof Bundle) {
			throw new UnexpectedValueException("A valid bundle was not provided for '$name'.");
		}
		
		$this->setBundle($bundle);
		
		return true;
	}
	
	protected function getProviderFor($type, $name) {
		
		// Bundle-specific provider
		if (isset($this->providers[$name])) {
			return $this->providers[$name];
		}
		
		// Bundle type provider
		if (isset($this->providers[$type])) {
			return $this->providers[$type];
		}
		
		return false;
	}
	
	/**
	 * Loads a bundle's dependencies.
	 * 
	 * @param \xpl\Bundle\BundleInterface $bundle
	 * 
	 * @throws \xpl\Bundle\DependencyException if missing dependency bundles.
	 */
	protected function bootDependencies(Bundle $bundle) {
		
		if (! $dependencies = $bundle->getDependencies()) {
			return;
		}
		
		$missing = array();
		
		foreach($dependencies as $dependency) {
			
			try {
				
				$dep = $this->getBundle($dependency);
				
				if (! $dep || (! $dep->isBooted() && ! $this->bootBundle($dep))) {
					$missing[] = $dependency;
				}
				
			} catch (DependencyException $e) {
				// catch dependency exceptions while recursing
				$missing = array_merge($missing, $e->getMissing());
			}
		}
		
		if (! empty($missing)) {
			
			$message = sprintf(
				'Could not boot bundle "%s" - failed to load dependencies: "%s"', 
				$bundle->getName(), 
				implode(', ', $missing)
			);
			
			$exception = new DependencyException($message, 793);
			$exception->setMissing($missing);
			
			throw $exception;
		}
	}
	
	/**
	 * Shuts down bundles that are overridden by another bundle.
	 * 
	 * @param \xpl\Bundle\BundleInterface $bundle Bundle that may override others.
	 */
	protected function shutdownOverrides(Bundle $bundle) {
		
		if ($overrides = $bundle->getOverrides()) {
			
			foreach($overrides as $name) {
			
				$name = strtolower($name);
				
				if (isset($this->bundles[$name])) {
						
					$this->bundles[$name]->shutdown();
					
					unset($this->bundles[$name]);
				}
			}
		}
	}
	
}
