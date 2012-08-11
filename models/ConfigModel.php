<?php
class ConfigModel extends Model {
	public $siteName;
	public $databaseInfo;
	
	public function __construct() {
		$this->siteName = '';
		$this->databaseInfo = array(
			'hostname' => 'localhost',
			'name' => '',
			'username' => '',
			'password' => ''
		);
	}
}
?>