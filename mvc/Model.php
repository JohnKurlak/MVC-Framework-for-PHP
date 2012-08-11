<?php
/**
 * This class represents a generic model in the MVC design pattern.
 */
class Model {
	private $modelStr;	// The name of the model, including "Model" at the end
	private $model;		// The name of the model, not including "Model" at the
						// end
	public static $vars = array();
	
	/**
	 * Loads the specified model.
	 */
	public function __construct(/* $modelName[, $arg1[, $arg2[, ...]]] */) {
		$args = func_get_args();
		if (func_num_args() == 0) {
			return;
		}
		
		// Get the model's file path
		$model = $args[0];
		$this->modelStr = $model . 'Model';
		$path = '../models/' . $this->modelStr . '.php';
		if (!file_exists($path)) {
			return;
		}
		
		// Load the model
		require_once($path);

		// Determine if arguments were passed
		$numArgs = func_num_args();
		if ($numArgs === 1) {
			// Instantiate the model with no arguments
			$this->model = new $this->modelStr();
		}
		else {
			// Instantiate the model with the given arguments
			array_shift($args);
			$reflectionObj = new ReflectionClass($this->modelStr); 
			$this->model = $reflectionObj->newInstanceArgs($args); 
		}
	}
	
	/**
	 * Allows calls to methods on the generic model instance to be passed along
	 * as calls to the specific model instance.
	 *
	 * e.g.,	Suppose we have $x = new Model('Repository');
	 *			That line of code creates a private instance of RepositoryModel.
	 *			This method allows $x->getNames(); to invoke getNames() on the
	 *			RepositoryModel instance.
	 */
	public function __call($name, $args) {
		return call_user_func_array(array($this->model, $name), $args);
	}
	
	/**
	 * Allows the model to access its own statically and dynamically created
	 * fields.
	 */
	public function __get($name) {
		if (isset($this->model->$name)) {
			return $this->model->$name;
		}
		else {
			return self::$vars[$name];
		}
	}
	
	/**
	 * Allows the model to set the values of its own statically created fields.
	 */
	public function __set($name, $value) {
		$this->model->$name = $value;
	}
}
?>