<?php

namespace Newsman\Util;

/**
 * Class Version
 *
 * @class \Newsman\Util\Version
 */
class Version extends \Newsman\Nzmbase {
	/**
	 * Get newsman extension version from install.json
	 *
	 * @return string
	 */
	public function getVersion() {
		$path = DIR_EXTENSION . 'newsman/install.json';

		if (file_exists($path)) {
			$content = file_get_contents($path);
			if ($content) {
				$json = json_decode($content, true);
				if (isset($json['version'])) {
					return $json['version'];
				}
			}
		}

		return '0.0.0';
	}
}
