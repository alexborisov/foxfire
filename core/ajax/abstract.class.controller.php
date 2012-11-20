<?php

/**
 * BP-MEDIA AJAX CONTROLLER CLASS
 * This base class is extended by server-side controller classes used in FoxFire's AJAX MVC framework
 *
 * @version 0.1.9
 * @since 0.1.9
 *
 * @author Based on the Ext JS 4 server-side MVC framework "RESTful" example
 * @link http://www.sencha.com
 * @link /ext/examples/restful/remote/
 *
 * @package FoxFire
 * @subpackage AJAX
 * @license GPL v3.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

abstract class FOX_AJAX_controller {

    public $request, $id, $params;

    /**
     * dispatch
     * Dispatch request to appropriate controller-action by convention according to the HTTP method.
     */
    public function dispatch($request) {
        $this->request = $request;
        $this->id = $request->id;
        $this->params = $request->params;

        if ($request->isRestful()) {
            return $this->dispatchRestful();
        }
        if ($request->action) {
            return $this->{$request->action}();
        }
    }

    protected function dispatchRestful() {
        switch ($this->request->method) {
            case 'GET':
                return $this->view();
                break;
            case 'POST':
                return $this->create();
                break;
            case 'PUT':
                return $this->update();
                break;
            case 'DELETE':
                return $this->destroy();
                break;
        }
    }
}