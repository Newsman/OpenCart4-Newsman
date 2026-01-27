<?php

namespace Newsman;

/**
 * Autoload Newsman classes
 */
class Nzmloader extends \Newsman\Library {
	public function autoload() {
		$filepath = DIR_EXTENSION . 'newsman/system/library/newsman/vendor/autoload.php';
		if (file_exists($filepath)) {
			require_once $filepath;
		}
	}
}
