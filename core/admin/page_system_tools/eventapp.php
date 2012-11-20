<?php
        session_start();

    // base framework
        
//    require( '/ajax/class.session_db.php');
//    require( BPM_PATH_CORE.'/ajax/abstract.class.controller.php');
//    require( BPM_PATH_CORE.'/ajax/abstract.class.model.php');
//    require( BPM_PATH_CORE.'/ajax/class.request.php');
//    require( BPM_PATH_CORE.'/ajax/class.response.php');
        // TODO: switch mappings to relative once it's working also change php.ini
    require( 'C:\xampp\htdocs\wp-content\plugins\bp-media\core\ajax\class.session.db.php');
    
    require( 'C:\xampp\htdocs\wp-content\plugins\bp-media\core\ajax\abstract.class.controller.php');
    require( 'C:\xampp\htdocs\wp-content\plugins\bp-media\core\ajax\abstract.class.model.php');
    require( 'C:\xampp\htdocs\wp-content\plugins\bp-media\core\ajax\class.request.php');
    require( 'C:\xampp\htdocs\wp-content\plugins\bp-media\core\ajax\class.response.php');

    class Event extends BPM_AJAX_model {

    }

    // Get Request
    $request = new BPM_AJAX_request(array('restful' => true));

    // Get Controller
//    require('remote/app/controllers/' . $request->controller . '.php');

    class Events extends  BPM_AJAX_controller {
        /**
        * view
        * Retrieves rows from database.
        */
        public function view() {
            $res = new BPM_AJAX_response();
            $res->success = true;
            $res->message = "Loaded data";
            $res->data = Event::all();
            return $res->to_json();
        }
        /**
        * create
        */
        public function create() {
            $res = new BPM_AJAX_response();
            $rec = Event::create($this->params);
            if ($rec) {
                $res->success = true;
                $res->message = "Created new Event" . $rec->id;
                $res->data = $rec->to_hash();
            } else {
                $res->message = "Failed to create User";
            }
            return $res->to_json();
        }
        /**
        * update
        */
        public function update() {
            $res = new BPM_AJAX_response();
            $rec = Event::update($this->id, $this->params);
            if ($rec) {
                $res->data = $rec->to_hash();
                $res->success = true;
                $res->message = 'Updated Event ' . $this->id;
            } else {
                $res->message = "Failed to find that User";
            }
            return $res->to_json();
        }
        /**
        * destroy
        */
        public function destroy() {
            $res = new BPM_AJAX_response();
            if (Event::destroy($this->id)) {
                $res->success = true;
                $res->message = 'Destroyed Event ' . $this->id;
            } else {
                $res->message = "Failed to destroy Event";
            }
            return $res->to_json();
        }
    }
//    $controller_name = ucfirst($request->controller);
    $controller = new Events;

    // Dispatch request
    echo $controller->dispatch($request);
?>
