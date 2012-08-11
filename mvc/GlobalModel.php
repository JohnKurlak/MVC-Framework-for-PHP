<?php
/**
 * Creates a singleton model to be shared throughout a controller and among
 * other models.
 */
class GlobalModel extends Model {
	/**
	 * Constructs an instance of the model and returns it.
	 */
	public function __construct(/* $modelName[, $arg1[, $arg2[, ...]]] */) {
		$args = func_get_args();
		if (func_num_args() == 0) {
			return;
		}
		
		// Stores a static reference to the global model in the model
		$model = $args[0] . 'Model';
		if (!array_key_exists($model, self::$vars)) {
			self::$vars[$model] = $this;
		}
		
		// Construct and return a reference to the model
		return call_user_func_array(array(parent, '__construct'), $args);
	}
}
?>