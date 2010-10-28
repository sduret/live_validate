<?php
/**
 *  jQuery Live Form Validation Component
 *
 *  @author Marc Grabanski <m@marcgrabanski.com>
 *  @author Jeff Loiselle <jeff@newnewmedia.com>
 */	
class JqueryFormComponent extends Object {
	
	var $controller = null;
	var $components = array('RequestHandler');
	
	/**
	 * Runs validation if the incoming data requests to be validated and is AJAX
	 */
	function startup(&$controller) {
		$this->controller =& $controller;
		//if ($this->RequestHandler->isAjax() && !empty($this->controller->data['__validate']) && $this->controller->data['__validate']['action'] == $this->controller->params['action'] && !empty($this->controller->data)) {
		if ($this->RequestHandler->isAjax() && !empty($this->controller->data['__validate']) && !empty($this->controller->data)) {
			$this->validate($this->controller->data, $this->controller->params['action']);
		}
	}
	
	/**
	 *  Returns a JSON encoded array of invalid fields for the models in the POST data
	 */	
	function validate($data) {
		$validated = array();
		foreach ($data as $model => $d) {
			if($model == '_Token' || $model == '__validate') {
				continue;
			}
			$class = ClassRegistry::init($model);
			$class->set(array_filter($d));
			$validated[$model] = $class->invalidFields();
		}
		$output = array();
		foreach($validated as $model => $data) {
			foreach ($data as $k => $d) {
				$output[] = array(
					'id' => $model.Inflector::camelize($k),
					'message' => $d
				);
			}
		}
		die(json_encode($output));
	}
	
}

?>