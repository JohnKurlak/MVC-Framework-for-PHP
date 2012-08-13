<?php
class HostModel extends Model {
	public $host;
	public $fileHost;
	public $page;
	
	public function __construct($className, $methodName) {
		$secure = ($_SERVER['HTTPS'] === 'on') ? 's' : '';
		$protocol = 'http' . $secure . '://';
		$port = ($_SERVER['SERVER_PORT'] != 80) ?
			':' . $_SERVER['SERVER_PORT'] :
			'';
		$host = $protocol . $_SERVER['HTTP_HOST'] . $port;
		$ending = str_replace('mvc/', '', str_replace(basename(
			$_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']));
		$this->host = $host . $ending;
		$this->fileHost = $_SERVER['DOCUMENT_ROOT'] . $ending;
		
		$className = ($className === 'WebsiteController') ?
			'' :
			str_replace('Controller', '', $className);
		$methodName = ($methodName === 'Index') ? '' : $methodName;
		$className = View::getUrlized($className);
		$methodName = View::getUrlized($methodName);
		
		if ($className === '' && $methodName === '') {
			$this->page = 'index';
		}
		else if ($className === '') {
			$this->page = $methodName;
		}
		else if ($methodName === '') {
			$this->page = $className;
		}
		else {
			$this->page = $className . '/' . $methodName;
		}
	}
	
	private function fixCase($matches) {
		return '-' . strtolower($matches[0]);
	}
}
?>