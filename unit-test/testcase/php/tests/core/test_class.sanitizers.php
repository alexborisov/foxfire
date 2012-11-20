<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - DATA SANITIZATION CLASS
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package FoxFire
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */


class database_sanatizers extends RAZ_testCase {
    
	var $cls;
	var $clsdebug;
    
	function setUp() {
	    
		parent::setUp();
		
		$this->clsdebug = new FOX_debug();		
		$this->cls = new FOX_sanitize();
		
	}
	
	function test_cleanSlate(){
	    
		$start = "FoxFire Unit Tests --> DataBase Sanitizers \n\n Starting....";

		$this->clsdebug->printToFile($start);
	    
	}
	
	   	
	function test_bool() {
	    
	    }
	    
	    
	function test_int(){
	    
	    }
	    
	    
	function test_float(){
	    
	    }
	    
	    
	function test_textAndNumbers(){
	    
	    }
	    
	    
	function test_slug(){
	    
	    }
	    
	    
	function test_keyName(){
	    
	    }
	    
	    
	function test_classOrFunction(){
	    
	    }
	    
	    
	function test_userName(){
	    
	    
	    }
	    
	    
	function test_fileStringLocal(){
	    
	    }
	    
	    
	function test_arraySimple(){
	    
	    }
	    
	    
	function test_URL(){
	    
	    }
	    
	    
	function test_commandPromptOptions(){
	    
	    }
	    
	    
	function test_i18nString(){
	    
	    }
	    
	    
	function test_removeLinks(){
	    
	    }
	    
    
	function test_stripSpecialChars(){
	    
	    
	    $test_data = array (
		
		 "test string (",
		 "ijbe\\\\woufyb][3984y)(ho  ",
		 " th////is is []a[] te\||st",
		);
	    
	    // Starting...
	    $this->clsdebug->addToFilenoDate(" \n //////////////////// test_stripSpecialChars() ///////////////////////////// ");
	    
	    foreach($test_data as $terms){

		$result = $this->cls->stripSpecialChars($terms);
		
		$this->clsdebug->addToFilenoDate("Returned string = '$result' \n");
		
		}
		
	}


	function test_cleanKeyword(){
	    
	    $test_data = array ( 
		
		"this is a test",
		" THEIH I$^&*#^$ ¢§¶ª•º¶∞¶kjd •™£",
		" W¶tº§ IT3 SPACE ()",
		
		);
	    
	    // Starting...
	    $this->clsdebug->addToFilenoDate(" \n //////////////////// test_cleanKeyword() ///////////////////////////// ");
	    
	    foreach($test_data as $terms){
		
		$result = $this->cls->cleanKeyword($terms);
		
		$this->clsdebug->addToFilenoDate("Returned string = '$result'\n");
		
		}	
	    
	}
	    
	    
	function test_makeUrlFromTerm(){
	    
	    }
	    
	    
	function test_makeTermFromUrl(){
	    
	    
	    }

    }
?>