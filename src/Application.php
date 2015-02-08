<?php

namespace xpl\Bundle;

use xpl\Common\Storage\Config;
use xpl\Common\Storage\Registry;

/**
 * Application is a bundle representing an application.
 */
class Application extends BundleAbstract
{
	
	/**
	 * @var \xpl\Common\Storage\Config
	 */
	protected $config;
	
	/**
	 * @var \xpl\Common\Storage\Registry
	 */
	protected $registry;
	
	/**
	 * @var boolean
	 */
	private $booted = false;
	
	/**
	 * Constructor takes a config instance.
	 * 
	 * @param \xpl\Common\Storage\Config $config
	 * @param \xpl\Common\Storage\Registry $registry [Optional]
	 */
	public function __construct(Config $config, Registry $registry = null) {
		
		$this->config = $config;
		$this->registry = $registry ?: new Registry;
		
		$this->config->setParent($this);
		
		$this->onInit();
	}
	
	/**
	 * Boots the application.
	 * 
	 * @throws \RuntimeException if already booted.
	 */
	public function boot() {
		
		if ($this->booted) {
			throw new \RuntimeException("Application has already been booted.");
		}
		
		$this->booted = true;
		
		$this->onBoot();
	}
	
	/**
	 * Runs when the application is shutdown.
	 */
	public function shutdown() {}
	
	/**
	 * Returns the type of bundle, "app".
	 * 
	 * @return string
	 */
	final public function getType() {
		return 'app';
	}
	
	/**
	 * Returns the application name.
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->config->get('name');
	}
	
	/**
	 * Returns the app's namespace, if set, otherwise null.
	 * 
	 * @return string
	 */
	public function getNamespace() {
		return $this->config->get('namespace');
	}
	
	/**
	 * Checks whether the app has been booted.
	 * 
	 * @return boolean
	 */
	public function isBooted() {
		return (bool)$this->booted;
	}
	
	/**
	 * Sets a config item value.
	 * 
	 * @param string $item Config item key.
	 * @param mixed $value Conifg item value. Pass null to unset the item.
	 */
	public function setConfig($item, $value) {
		
		if (null === $value) {
			$this->config->remove($item);
		} else {
			$this->config->set($item, $value);
		}
		
		return $this;
	}
	
	/**
	 * Returns a config item or the entire \App\Config object.
	 * 
	 * @param string $item [Optional] Item key to retrive, or null to get the object.
	 * @return mixed
	 */
	public function getConfig($item = null) {
		return isset($item) ? $this->config->get($item) : $this->config;
	}
	
	/**
	 * Sets a component in the object registry.
	 * 
	 * @param string $name Component name.
	 * @param object $object Component object.
	 * @return $this
	 */
	public function setComponent($name, $object) {
		$this->registry->set($name, $object);
		return $this;
	}
	
	/**
	 * Retrieves a component from the application's object registry.
	 * 
	 * @param string $name Component name.
	 * @return mixed
	 */
	public function getComponent($name) {
		return $this->registry->get($name);
	}
	
	/**
	 * Called at end of `__construct()`
	 */
	protected function onInit() {}
	
	/**
	 * Called at end of `boot()`
	 */
	protected function onBoot() {}
	
}
