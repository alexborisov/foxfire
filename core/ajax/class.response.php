<?php

/**
 * FOXFIRE AJAX RESPONSE CLASS
 * Renders responses into JSON and sends them back to the client
 *
 * @version 1.0
 * @since 1.0
 *
 * @author Based on the Ext JS 4 server-side MVC framework "RESTful" example
 * @link http://www.sencha.com
 * @link /ext/examples/restful/remote/
 *
 * @package FoxFire
 * @subpackage AJAX
 * @license GPL v3.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_AJAX_response {

    public $success, $data, $message, $errors, $tid, $trace;

    public function __construct($params = array()) {
        $this->success  = isset($params["success"]) ? $params["success"] : false;
        $this->message  = isset($params["message"]) ? $params["message"] : '';
        $this->data     = isset($params["data"])    ? $params["data"]    : array();
    }

    public function to_json() {
        return json_encode(array(
            'success'   => $this->success,
            'message'   => $this->message,
            'data'      => $this->data
        ));
    }
}