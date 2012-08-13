<?php
/**
 * This class represents a generic view in the MVC design pattern.
 */
class View {
	private $controllerStr;		// The name of the controller, not including
								// "Controller" at the end
	private $viewStr;			// The name of the view, not including "View" at
								// the end
	private $vars;
	
	/**
	 * Creates a new instance of a view.
	 */
	public function __construct($controllerName, $viewName) {
		$viewName = str_replace('_', '', $viewName);
		$this->controllerStr = $this->getUrlized($controllerName);
		$this->viewStr = $viewName . 'View';
		$this->vars = array();
	}
	
	/**
	 * Renders a view, outputting it to the client.
	 */
	public function Render($partial = false) {
		// Set the content type if we are rendering a complete view
		if (!$partial) {
			header('Content-Type: text/html; charset=UTF-8');
		}
		
		// Get the expected path of the view template
		$this->bodyFilename = '../views/' . $this->controllerStr . '/' .
			$this->viewStr . '.php';
		
		// If we are loading a partial view, render it and exit
		if ($partial) {
			$this->RenderBody();
			return;
		}
		
		// If a master layout has not been specified
		if (!isset(Loader::$ViewBag->layout)) {
			// Use the SiteLayout view as the master layout
			Loader::$ViewBag->layout = 'SiteLayout';
		}
		
		// Get the expected path of the master layout
		Loader::$ViewBag->layout = '../views/shared/' .
			Loader::$ViewBag->layout . 'View.php';
		
		// If the master layout exists
		if (file_exists(Loader::$ViewBag->layout)) {
			// Load the master layout, which includes the view
			include(Loader::$ViewBag->layout);
		}
		// If the master layout doesn't exist
		else {
			// Load the view itself
			include($this->bodyFilename);
		}
	}
	
	/**
	 * Renders a partial view, outputting it to the client.
	 */
	public function RenderPartial($controllerName, $viewName/* [, $arg1[,
		$arg2[, ...]]] */) {
		// Load the partial view as a view
		$view = new View($controllerName, $viewName);
		
		// Get any additional arguments
		$args = func_get_args();
		array_shift($args);
		array_shift($args);

		// Make a temporary view bag
		$tempViewBag = new ViewBag();
		
		// Copy all view bag data into the temporary view bag and allow
		// arguments passed to the partial view to be available as view bag
		// fields
		foreach ($args as $arg) {
			if ($arg instanceof ViewBag) {
				foreach ($arg as $key => $value) {
					if (isset($this->ViewBag->$key)) {
						// Copy view bag data to the temporary view bag
						$tempViewBag->$key = $this->ViewBag->$key;
					}
					
					// Allow arguments passed to the partial view to be
					// available as view bag fields
					$this->ViewBag->$key = $value;
				}
			}
		}
		
		// Render the view as a partial view
		$view->Render(true);
		
		// Restore the original view bag
		foreach ($tempViewBag as $key => $value) {
			$this->ViewBag->$key = $tempViewBag->$key;
		}
	}
	
	/**
	 * Renders the body of a view.
	 */
	public function RenderBody() {
		include($this->bodyFilename);
	}
	
	/**
	 * Returns a full URL given a relative path.
	 */
	public static function Url($location = '') {
		return Loader::$ViewBag->host . $location;
	}
	
	/**
	 * HTML encodes given data.
	 */
	public static function Html($html) {
		return htmlentities($html);
	}
	
	/**
	 * Creates a URL slug from a string.
	 */
	public static function GetUrlized($url) {
		// Convert uppercase letters to -[lowercase]
		$urlized = preg_replace_callback("/([A-Z])/", array(self, 'fixCase'),
			$url);
		
		// Remove non-letters and non-hyphens
		$urlized = preg_replace("/[^a-z\-]+/", '', $urlized);

		// Remove leading hyphens
		while ($urlized{0} === '-' || $urlized{0} === '_') {
			$urlized = substr($urlized, 1);
		}
		
		return $urlized;
	}
	
	/**
	 * Replaces "SomeWord" with "-some-word".
	 */
	private static function fixCase($matches) {
		return '-' . strtolower($matches[0]);
	}
	
	/**
	 * Allows the view to access dynamic fields on itself.  Also allows the view
	 * to retrieve a reference to the view bag.
	 */
	public function __get($name) {
		// If the field hasn't been set on the view
		if (!isset($this->vars[$name])) {
			// Return a reference to the view bag if it is being retrieved
			if ($name === 'ViewBag') {
				return Loader::$ViewBag;
			}
			
			// Otherwise, instantiate and return an empty variable
			$this->vars[$name] = new StdClass();
			return $this->vars[$name];
		}
		
		// If the field has been set on the view, return its value
		return $this->vars[$name];
	}
	
	/**
	 * Allows the view to create and instantiate dynamic fields.
	 */
	public function __set($name, $value) {
		$this->vars[$name] = $value;
	}
	
	/**
	 * Automatically creates getters and setters for view data.
	 */
	public function __call($name, $arguments) {
		if (strlen($name) <= 3) {
			return;
		}
		
		$first = substr($name, 0, 3);
		$var = substr($name, 3);
		$var[0] = strtolower($var[0]);

		// Handle a getter invocation
		if ($first === 'get') {	
			return $this->$var;
		}
		// Handle a setter invocation
		else if ($first === 'set') {
			$this->$var = $arguments[0];
		}
	}
}
?>