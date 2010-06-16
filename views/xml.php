<?php
/**
* 	View Class for XML
* 	
* 	@author Jonathan Dalrymple
*/
class XmlView extends View {
	public $response = '';
	
	public function render ($action = null, $layout = null, $file = null) {
		if (array_key_exists('response', $this->viewVars)){
			//As a prep we want to reindex numerically index arrays to allow for proper elements
			//ie moods->mood->rowData instead of moods->row,row

			$rootTag = Inflector::tableize($this->params['controller']) . '_response';
			
			$this->encode(array($rootTag => $this->viewVars['response']));
			
			return $this->_xmlCleanup($this->response);
		}
	}
	
	protected function _xmlCleanup($xml, $header = null) {
		if ($header === null) {
			$header  = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		}

		// Indentation
		$doc = new DOMDocument('1.0');
		$doc->preserveWhiteSpace = false;
		if (!$doc->loadXML($xml)) {
			trigger_error('Invalid XML: '.$xml, E_USER_ERROR);
		}
		$doc->formatOutput = true;

		return $header . $doc->saveXML();
	}

	public function encode ($response) {
		if (!is_array($response)) {
			$this->response .= $response;
			return;
		}

		foreach ($response as $key => $val) {
			// starting tag
			if (!is_numeric($key)) {
				$this->response .= sprintf("<%s>", $key);
			}
			// Another array
			if (is_array($val)){
				// Handle non-associative arrays
				if ($this->isNumericallyIndexedArray($val)) {
					foreach ($val as $item) {
						#$tag = Inflector::singularize($key);
						$tag = 'item';
						
						$this->response .= sprintf("<%s>", $tag);
						
						$this->encode($item);
						
						$this->response .= sprintf("</%s>", $tag);
					}
				} else {
					$this->encode($val);
				}
			} elseif(is_string($val)) {
				$this->response .= $val;
			}
			// Draw closing tag
			if (!is_numeric($key)) {
				$this->response .= sprintf("</%s>", $key);
			}
		}
	}
	
	/**
	* Determine if a given array is numerically indexed
	* 
	* @return Boolean True or False depending on the makeup of the array index
	*/
	public function isNumericallyIndexedArray ($arr) {
		foreach ($arr as $key => $val) {
			if (!is_numeric($key)) {
				return false;
			}
		}
		return true;
	}
}