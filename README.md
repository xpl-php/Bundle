# Bundle

This package provides a system to manage groups of code ("bundles").

Bundles have no defined functionality; however, they share the following characteristics:
 * They __belong to a "type"__ which defines its functionality.
 * They are __identifiable__ by name.
 * They are __loaded__ ("booted").
 * They can be __shut down__.
 * They can __depend__ on other bundles, including those of different types.
 * They can __override__ other bundles, including those of different types.
 
Bundles may also be installable.

Bundles are managed by a "bundle manager".

All bundles have a "provider", which creates the bundle and returns it to the manager. Bundle providers may be of the following kinds:
 * __Single bundle providers:__ Provide one unique bundle by name.
 * __Multiple bundle providers:__ Provide multiple unique bundles by name.
 * __Bundle type providers:__ Provide a certain type of bundle.

##Example
The following is an example of a simple "library" bundle type, which simply includes a file "bootstrap.php" from the library directory when it is booted.

####Bundle definition

```php
use xpl\Bundle\BundleAbstract;

class Library extends BundleAbstract
{
	/**
	 * Bundle name.
	 * @var string
	 */
	protected $name;
	
	/**
	 * Directory path to the library.
	 * @var string
	 */
	protected $dirpath;
	
	/**
	 * Whether the bundle has been booted.
	 * @var boolean
	 */
	protected $booted = false;
	
	public function __construct($dirpath) {
		$this->dirpath = realpath($dirpath).DIRECTORY_SEPARATOR;
		$this->name = strtolower(basename($this->dirpath));
	}
	
	public function boot() {
		
		if ($this->booted) {
			throw new \RuntimeException();
		}
		
		if (file_exists($this->dirpath.'bootstrap.php')) {
			require $this->dirpath.'bootstrap.php';
		}
		
		return $this->booted = true;
	}
	
	public function shutdown() {
	  // do nothing
	}
	
	public function getBundleType() {
		return 'library';
	}
	
	public function isBooted() {
		return (bool)$this->booted;
	}
	
}
```

Recall that bundles must have a provider. Here, we will use a single provider for all bundles of type "library".

####Provider defintion

```php
use xpl\Bundle\TypeProviderInterface;

class LibraryProvider implements TypeProviderInterface
{
	
	protected $path;
	
	public function __construct($library_path) {
		$this->path = realpath($library_path).DIRECTORY_SEPARATOR;
	}
	
	public function provides() {
		return 'library';
	}
	
	public function provideBundle($type, $name) {
		
		$path = $this->path.$name.DIRECTORY_SEPARATOR;
		
		if (is_dir($path)) {
			return new Library($path);
		}
	}
	
}
```

####Management
Recall that bundles are managed solely through the manager -- providers are not called directly. Therefore, we must register providers with the manager before use.
```php
use xpl\Bundle\Manager;

$manager = new Manager();

$manager->provide(new LibraryProvider('/path/to/libraries'));
```
Now assume we have a directory at "/path/to/libraries/common" with a "bootstrap.php" file.
```php
$manager->boot("library.common");
```
The file has been included and we can now use the code contained within it.
