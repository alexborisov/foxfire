<?php

/**
 * FOXFIRE AJAX SESSION DB CLASS
 * This test and debug class emulates a database by storing data in the PHP session variable.
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

// 2013-01-26
// NOTE: this block of test code should not declare a function outside of the class


//class FOX_AJAX_sessionDB {
//
//    public function __construct() {
//        if (!isset($_SESSION['pk'])) {
//            $_SESSION['pk'] = 10;           // <-- start fake pks at 10
//            $_SESSION['rs'] = getData();    // <-- populate $_SESSION with data.
//        }
//    }
//    // fake a database pk
//    public function pk() {
//        return $_SESSION['pk']++;
//    }
//    // fake a resultset
//    public function rs() {
//        return $_SESSION['rs'];
//    }
//    public function insert($rec) {
//        array_push($_SESSION['rs'], $rec);
//    }
//    public function update($idx, $attributes) {
//        $_SESSION['rs'][$idx] = $attributes;
//    }
//    public function destroy($idx) {
//        return array_shift(array_splice($_SESSION['rs'], $idx, 1));
//    }
//}
//
//// Sample data.
//function getData() {
//    return array(
//        array('id' => 1, 'first' => "Fred", 'last' => 'Flintstone', 'email' => 'fred@flintstone.com'),
//        array('id' => 2, 'first' => "Wilma", 'last' => 'Flintstone', 'email' => 'wilma@flintstone.com'),
//        array('id' => 3, 'first' => "Pebbles", 'last' => 'Flintstone', 'email' => 'pebbles@flintstone.com'),
//        array('id' => 4, 'first' => "Barney", 'last' => 'Rubble', 'email' => 'barney@rubble.com'),
//        array('id' => 5, 'first' => "Betty", 'last' => 'Rubble', 'email' => 'betty@rubble.com'),
//        array('id' => 6, 'first' => "BamBam", 'last' => 'Rubble', 'email' => 'bambam@rubble.com')
//    );
//}