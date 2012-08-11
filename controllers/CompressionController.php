<?php
/**
 * Compresses/minifies CSS and JS files.
 */
class CompressionController extends BaseController {
	/**
	 * Compresses/minifies CSS.
	 */
	public function compressCSS($filename) {
		$css = $this->CompressionModel->compressCSS($filename);
		return $this->Css($css);
	}
		
	/**
	 * Compresses/minifies JS.
	 */
	public function compressJavaScript($filename) {
		$js = $this->CompressionModel->compressJavaScript($filename);
		return $this->Javascript($js);
	}
}
?>