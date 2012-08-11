<?php
/**
 * Automatically loads classes that are referenced.
 */
function __autoload($className) {
	$path = $className . '.php';
	$path2 = '../controllers/' . $path;
	
	// Attempt to load from current directory
	if (file_exists($path)) {
		include($path);
	}
	// Attempt to load from controllers directory
	else if (file_exists($path2)) {
		include($path2);
	}
}

/**
 * This class takes the request, maps it to the correct method of the requested
 * controller, and then renders the view that was returned.
 */
class Loader {
	public static $ViewBag;
	
	/**
	 * Constructs an instance of the loader.
	 */
	public function __construct() {
		date_default_timezone_set('America/New_York');
		self::$ViewBag = new ViewBag();
		
		// Attempt to parse the URL into its components
		$this->parseUrl($controllerBaseName, $controllerName, $methodName,
			$params);

		// Attempt to load the requested page
		$this->load($controllerBaseName, $controllerName, $methodName, $params);
	}
	
	private function load($controllerBaseName, $controllerName, $methodName,
		$params) {
		// Determine if the controller is abstract
		$class = new ReflectionClass($controllerName);
		$validController = !$class->isAbstract();
		
		if ($validController) {
			// Create an instance of the controller
			$controller = new $controllerName($controllerBaseName);
		
			// Use the request method within the controller
			$method = array($controller, $methodName);
		}
		
		$first = true;
		$methodExists = false;
		$exhausted = false;
		
		// If we can call the method
		while (($validController && ($first || is_callable($method))) ||
			($methodExists && $exhausted = true)) {
			$first = false;
			$methodName = $method[1];
			
			if (is_callable($method)) {
				$methodReflection = new ReflectionMethod($controllerName,
					$methodName);
				$numParamsExpected = $methodReflection->getNumberOfParameters();
			}
			else {
				$numParamsExpected = -1;
			}
			
			if ($exhausted) {
				$method = $savedMethod;
			}
			
			if (count($params) === $numParamsExpected ||
				($exhausted && count($params) >= $numParamsExpected)) {
				$HostModel = new Model('Host', $controllerName, $methodName);
				self::$ViewBag->host = $HostModel->host;
				self::$ViewBag->fileHost = $HostModel->fileHost;
				self::$ViewBag->page = $HostModel->page;
				self::$ViewBag->controller = View::GetUrlized(
					$controllerBaseName);
				self::$ViewBag->view = View::GetUrlized($methodName);
			
				// Autoload the Models
				$modelName = $methodName . 'Model';
				$controller->$modelName = new Model($methodName);
				$modelName = $controllerBaseName . 'Model';
				$controller->$modelName = new Model($controllerBaseName);
				
				// Invoke the controller function to handle the GET/POST request
				$view = call_user_func_array($method, $params);
				
				// Make sure a View was returned
				if ($view instanceof View) {
					$view->render();
				}
				else if (is_string($view)) {
					echo $view;
				}
				else if ($view === false) {
					$this->kill();
				}
				else {
					trigger_error($controllerName . '::' . $methodName .
						' must return a View', E_USER_ERROR);
				}
				
				exit(0);
			}
			else {
				$methodExists = true;
				$savedMethod = $method;	
			}
			
			$method = array($controller, '_' . $methodName);
		}
		
		// If we need to show the 404 page
		if ($methodName !== 'PageNotFound') {
			// Show the 404 page
			$this->kill();
		}
		// If we have just outputted the 404 page
		else {
			$this->kill(false);
		}
	}
	
	/**
	 * Attempts to parse the controller, method, and parameters from a request.
	 */
	private function parseUrl(&$controllerBaseName, &$controllerName,
		&$methodName, &$params) {
		// Get the request URL
		$page = $_GET['_page'];
		unset($_GET['_page']);
		
		// Append any parameters that have been set via the query string to the
		// URL and reload
		//
		// e.g.,	/controller/method?user=John&code=3 becomes
		// 			/controller/method/John/3
		//
		// This should never happen, but if it does, this provides "protection."
		// A downfall of providing this functionality is that order matters in
		// the original query string.
	
		// Make sure GET params are part of the URL and not the query string
		if (count($_GET) > 0) {
			$getVars = '';
			foreach ($_GET as $key => $value) {
				$getVars .= '/' . urlencode(urlencode($value));
			}
			
			header('Location: ' . $page . $getVars);
			exit(0);
		}
		
		// Fix request URL
		if (substr($page, -1) !== '/') {
			$page .= '/';
		}
		
		// Check to see if there is a / in the URL
		if (strpos($page, '/') !== false) {
			// Split up the URL at the /
			$parts = array_merge(explode('/', $page), array_values($_GET));
			
			// Attempt to parse the URL
			$controllerBaseName = $parts[0];
			$methodName = $parts[1];
			if (count($parts) > 2) {
				$params = array_filter(array_slice($parts, 2));
			}
			
			// Fix up method/controller names
			$originalMethodName = $methodName;
			$controllerBaseName = $this->getControllerBaseName(
				$controllerBaseName);
			$controllerName = $this->getControllerName($controllerBaseName);
			$methodName = $this->getMethodName($methodName);
			
			// If POSTing to page, ignore GET vars (this is by design)
			if (!empty($_POST)) {
				$params = array_values($_POST);
			}
			
			// Verify that we have interpretted everything correctly
			if (!class_exists($controllerName, true)) {
				// We parsed the controller incorrectly
				// Assume that this is the root directory, and therefore, the
				// controller is "WebsiteController"

				// Update the method name
				$methodName = $controllerBaseName;
				
				// Update the controller name
				$controllerBaseName = 'Website';
				$controllerName = $this->getControllerName($controllerBaseName);
				
				// Update the parameters
				$params = array_filter(array_slice($parts, 1));
				

				// If POSTing to page, ignore GET vars (this is by design)
				if (!empty($_POST)) {
					$params = array_values($_POST);
				}
			}
		}
		else {
			// There is no / in the URL
			// Assume that this is the root directory, and therefore, the
			// controller is "WebsiteController"

			// Set controller name
			$controllerBaseName = 'Website';
			$controllerName = $this->getControllerName($controllerBaseName);
			
			// Set method name
			$methodName = $this->getMethodName($page);
		}
		
		// Create a reference to the method
		$method = array($controllerName, $methodName);
		
		// If the method doesn't exist
		if (!is_callable($method)) {
			// See if _method() exists in controller (so that _New() can be used
			// for New())
			$methodName = '_' . $methodName;
			$method = array($controllerName, $methodName);
		}
		
		// If prepending _ didn't fix anything
		if (!is_callable($method)) {
			// Try Index()
			if (!is_array($params)) {
				$params = array();
			}
			array_unshift($params, $originalMethodName);
			$methodName = 'Index';
			$method = array($controllerName, $methodName);
		}
		
		// Attempt to URL decode the parameters
		if (is_array($params)) {
			$params = array_map('urldecode', $params);
		}
		else {
			$params = array();
		}
	}
	
	/**
	 * Output a 404 not found error.  Attempt to load a custom 404 page if
	 * possible.
	 */
	private function kill($has404 = true) {
		// Output 404 response
		header('HTTP/1.1 404 Not Found');
		
		// Attempt to load a custom 404 page if we haven't already
		if ($has404) {
			$this->load('Website', 'WebsiteController', 'PageNotFound',
				array($_SERVER['REQUEST_URI']));
		}
		
		exit(0);
	}
	
	/**
	 * Gets the expected class or method name for a string.
	 *
	 * e.g.,	method-name		becomes	MethodName,
	 *			controller-name	becomes	ControllerName
	 */
	private function getName($urlArg) {
		// Remove non-alphanumeric, -, and _
		$urlArg = preg_replace('/[^a-zA-Z0-9_\\-]+/', '', $urlArg);
		
		// Replace - and _ with spaces (to preserve word boundaries)
		$urlArg = strtr($urlArg, '-', ' ');
		$urlArg = strtr($urlArg, '_', ' ');

		// Capitalize words and then remove spaces
		$urlArg = ucwords($urlArg);
		$urlArg = str_replace(' ', '', $urlArg);
		
		return $urlArg;
	}
	
	/**
	 * Gets the base name of a controller (removes "Controller" from the end of
	 * the name).  If the input is an empty string, "Website" is the assumed
	 * controller.
	 */
	private function getControllerBaseName($controllerBaseName) {
		$controllerBaseName = $this->getName($controllerBaseName);
		$controllerBaseName = ($controllerBaseName === '') ? 'Website' :
			$controllerBaseName;
		
		return $controllerBaseName;
	}
	
	/**
	 * Gets the full name of a controller (includes "Controller" at the end of
	 * the name).
	 */
	private function getControllerName($controllerBaseName) {
		return $controllerBaseName . 'Controller';
	}
	
	/**
	 * Gets the name of a method.  If the input is an empty string, "Index" is
	 * the assumed method.
	 */
	private function getMethodName($methodName) {
		$methodName = $this->getName($methodName);
		$methodName = ($methodName === '') ? 'Index' : $methodName;
		
		return $methodName;
	}
}

// Create a new loader and handle the request!
new Loader();
?>