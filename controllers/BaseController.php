<?php
/**
 * This abstract controller class is meant to be extended by all controllers.
 * Its purpose is to instantiate models that would typically be used by all
 * controllers anyway.  It also provides support for 404 pages.
 */
abstract class BaseController extends Controller {
	/**
	 * Constructs a new controller and loads configuration information.  Then,
	 * connects to the database.
	 */
	public function __construct() {
		$this->ConfigModel = new GlobalModel('Config');
		$this->DatabaseModel = new GlobalModel('Database',
			$this->ConfigModel->databaseInfo);
	}
	
	/**
	 * Handles 404 pages.
	 */
	public function PageNotFound($url) {
		$this->ViewBag->title = 'Page Not Found';
		$this->ViewBag->keywords = 'page not found, 404, not found, error';
		$this->ViewBag->description = 'Page not found.';
		$this->ViewBag->url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		
		return $this->View('Shared');
	}
}
?>