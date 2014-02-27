<?php

/**
 * FOXFIRE TRIE - CLIP
 * Clips trie branches at the first null node in a walk. This method is used to
 * determine the cache levels to invalidate during delete operations.
 * 
 * BEFORE:  A --> B -> C	AFTER:	A --> B -> C
 *	      |-> * -> E		  |-> D
 *	      |-> D -> *
 * 
 * @see http://en.wikipedia.org/wiki/Trie
 * @see http://en.wikipedia.org/wiki/Linked_list
 * 
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Database
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfirewiki/DOCS_FOX_db_top
 *
 * ========================================================================================================
 */

class FOX_trie_clip {

	
	var $trie;				    // Trie structure to convert		
	var $max_depth;				    // Maximum depth to iterate to	
	var $ctrl;				    // Control args
	var $null_token;			    // Null token identifier string	
	
	var $iterator;				    // Iterator object
	
	var $result;				    // Clipped trie structure
	

	// ============================================================================================================ //


	/**
         * Implodes an array of heirarchical datastore keys into a single query $args array
         *
         * @version 1.0
         * @since 1.0
	 * 
	 * @param array $trie | Trie structure
	 * 
	 * @param array $columns | Array of column names
	 * 
	 * @param array $ctrl | Control args 
	 *	=> VAL @param string $null_token | String to use as null token 	  
	 *
         * @return bool | Exception on failure. True on success.
         */
	
	function __construct($trie, $columns, $ctrl=null){
		
	    
		$this->trie = $trie;			
		$this->columns = $columns;
		$this->max_depth = count($columns);
		
		$ctrl_default = array(
			'null_token' => '*'
		);

		$this->ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);	
		
		$this->null_token = $this->ctrl['null_token'];		
						
		return true;
		
	}		
	

	public function render() {				

	    
		try {
		    
			$this->iterator = new FOX_trie_clip_iterator(array(

				'base'	    => $this,			    
				'trie'	    => $this->trie,			    			    
				'depth'	    => 0
			));	
			
		}
		catch (FOX_Exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error creating root node",
				'data'=>array("trie"=>$this->trie, "max_depth"=>$this->max_depth, "ctrl"=>$this->ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}	    	   
		
		
		try {
			$result = $this->iterator->render();
		}
		catch (FOX_Exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error during render",
				'data'=>array("args"=>$this->args, "max_depth"=>$this->max_depth, "ctrl"=>$this->ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}		    


		return $result;				
		
	}
	

	
} // End of class FOX_trie_clip

?>