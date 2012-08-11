<?php
/**
 * This class represents a generic controller in the MVC design pattern.
 */
class Controller {
	private $vars;
	private $methods;
	
	/**
	 * Instantiates a view and returns a reference to it.
	 */
	protected function View($controllerName = '', $viewName = '') {
		$debug = debug_backtrace();
		
		// If a controller is not specified, use the calling method's controller
		if ($controllerName === '') {
			$className = $debug[1]['class'];
			$controllerName = substr($className, 0, -strlen('Controller'));
		}
		
		// If a view is not specified, use the name of the calling method
		if ($viewName === '') {
			$viewName = $debug[1]['function'];
		}
		
		// Instantiate and return the view
		$view = new View($controllerName, $viewName);
		return $view;
	}
	
	/**
	 * Returns a JSON view.
	 */
	protected function Json($obj) {
		header('Content-Type: application/json; charset=UTF-8');
		return json_encode($obj);
	}
	
	/**
	 * Returns a JavaScript view.
	 */
	protected function Javascript($js) {
		header('Content-Type: text/javascript; charset=UTF-8');
		return $js;
	}

	/**
	 * Returns a CSS view.
	 */
	protected function Css($css) {
		header('Content-Type: text/css; charset=UTF-8');
		return $css;
	}
	
	/**
	 * Returns a PNG view.
	 */
	protected function Png($data, $cache = true) {
		header('Content-Type: image/png');
		
		// Automatically cache, unless otherwise specified
		if (!$cache) {
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');
		}

		return $data;
	}
	
	/**
	 * Return a 404 page view.
	 */
	protected function PageNotFound() {
		return false;
	}
	
	/**
	 * Allow retrieval of ViewBag and dynamically created fields.
	 */
	public function __get($name) {
		if ($name === 'ViewBag') {
			return Loader::$ViewBag;
		}
		
		return $this->vars[$name];
	}
	
	/**
	 * Allow programmer to dynamically create and instantiate fields.
	 */
	public function __set($name, $value) {
		// Make all models that are instantiated available to the view 
		if (substr($name, -strlen('Model')) === 'Model') {
			Loader::$ViewBag->$name = $value;
		}
		
		// Store the data
		$this->vars[$name] = $value;
	}
}
?>