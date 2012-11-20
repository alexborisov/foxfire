<?php

/**
 * BP-MEDIA AJAX MODEL CLASS
 * This base class is extended by server-side model classes used in FoxFire's AJAX MVC framework
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

abstract class FOX_AJAX_model {
    
    public $id, $attributes;
    static function create($params) {
        $obj = new self(get_object_vars($params));
        $obj->save();
        return $obj;
    }
    static function find($id) {
        global $dbh;
        $found = null;
        foreach ($dbh->rs() as $rec) {
            if ($rec['id'] == $id) {
                $found = new self($rec);
                break;
            }
        }
        return $found;
    }
    static function update($id, $params) {
        global $dbh;
        $rec = self::find($id);

        if ($rec == null) {
            return $rec;
        }
        $rs = $dbh->rs();

        foreach ($rs as $idx => $row) {
            if ($row['id'] == $id) {
                $rec->attributes = array_merge($rec->attributes, get_object_vars($params));
                $dbh->update($idx, $rec->attributes);
                break;
            }
        }
        return $rec;
    }
    static function destroy($id) {
        global $dbh;
        $rec = null;
        $rs = $dbh->rs();
        foreach ($rs as $idx => $row) {
            if ($row['id'] == $id) {
                $rec = new self($dbh->destroy($idx));
                break;
            }
        }
        return $rec;
    }
    static function all() {
        global $dbh;
        return $dbh->rs();
    }

    public function __construct($params) {
        $this->id = isset($params['id']) ? $params['id'] : null;
        $this->attributes = $params;
    }
    public function save() {
        global $dbh;
        $this->attributes['id'] = $dbh->pk();
        $dbh->insert($this->attributes);
    }
    public function to_hash() {
        return $this->attributes;
    }
}