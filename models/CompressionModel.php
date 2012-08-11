<?php
class CompressionModel extends Model {
	public function compressJavaScript($filename) {
		$stylesheet = '../js/' . $filename . '.js';
		$f = @file($stylesheet);

		if ($f) {
			$output = '';
			$length = count($f);

			for ($i = 0; $i < $length; $i++) {
				$output .= $f[$i];
			}

			$output = preg_replace('/[\s]+[\/]{2}[^\n\r]+/', '', $output);
			$output = str_replace("\r\n", '', $output);
			$output = str_replace("\n", '', $output);
			$output = str_replace("\r", '', $output);
			$output = str_replace("\t", '', $output);
			$output = str_replace(': ', ':', $output);
			$output = str_replace(', ', ',', $output);
			$output = str_replace('; ', ';', $output);
			$output = str_replace('   ', '', $output);
			$output = str_replace('  ', '', $output);
			$output = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $output);
			
			return $output;
		}
	}

	public function compressCSS($filename) {
		$sheet = preg_replace('/[^a-zA-Z0-9\-\.\_]+/', '', $filename);
		$stylesheet = '../css/' . $sheet . '.css';
		$f = @file($stylesheet);

		if ($f) {
			$output = '';
			$length = count($f);

			for ($i = 0; $i < $length; $i++) {
				$output .= $f[$i];
			}

			$output = str_replace("\r\n", '', $output);
			$output = str_replace("\n", '', $output);
			$output = str_replace("\r", '', $output);
			$output = str_replace("\t", '', $output);
			$output = str_replace(': ', ':', $output);
			$output = str_replace(', ', ',', $output);
			$output = str_replace('; ', ';', $output);
			$output = str_replace('   ', '', $output);
			$output = str_replace('  ', '', $output);
			$output = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $output);

			return $output;
		}
	}
}
?>