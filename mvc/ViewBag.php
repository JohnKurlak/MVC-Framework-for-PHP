<?php
/**
 * This class represents a "view bag" (a data store for passing data from the
 * controller to the view).
 */
class ViewBag implements Iterator {
	// These values cannot be set in the view bag
	private static $reserved = array('host', 'filehost', 'page', 'controller',
		'view');
	private $vars = array();
	
	/**
	 * Allows a value to be set in the view bag, unless it is a reserved word.
	 */
	public function __set($name, $value) {
		// Check to see if a reserved word is being set
		if (isset($this->vars[$name]) && in_array(strtolower($name),
			self::$reserved)) {
			trigger_error('$this->ViewBag->' . $name .
				' is reserved and cannot be reassigned', E_USER_ERROR);
			return;
		}
		
		$this->vars[$name] = $value;
	}
	
	/**
	 * Allows a value to be retrieved from the view bag.
	 */
	public function &__get($name) {
		// If the value hasn't been set, return an empty view bag
		if (!isset($this->vars[$name])) {
			$this->vars[$name] = new ViewBag();
		}
		
		return $this->vars[$name];
	}
	
	/**
	 * Returns whether or not a value has been set.
	 */
	public function __isset($name) {
		return isset($this->vars[$name]);
	}
	
	/**
	 * Returns the view bag as a string (an empty one by design).
	 */
	public function __toString() {
		return '';
	}
	
	/**
	 * Resets the iterator on the view bag's variables.
	 */
	public function rewind() {
		reset($this->vars);
	}
	
	/**
	 * Returns the current variable in the view bag as specified by the
	 * iterator.
	 */
	public function current() {
		$var = current($this->vars);
		return $var;
	}
	
	/**
	 * Returns the key of the current variable int the view bag as specified by
	 * the iterator.
	 */
	public function key() {
		$var = key($this->vars);
		return $var;
	}
	
	/**
	 * Advances the iterator for the view bag.
	 */
	public function next() {
		$var = next($this->vars);
		return $var;
	}
	
	/**
	 * Returns whether the current iterator position in the view bag is valid.
	 */
	public function valid() {
		$key = key($this->vars);
		$var = ($key !== null && $key !== false);
		return $var;
	}
}
?>