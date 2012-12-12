<?php

/**
 * FOXFIRE QUERY BUILDER - WHERE MATRIX
 * A self-optimizing doubly-linked trie class that accepts condition arguments as a flat array, assembles
 * them into a trie, reduces it to a minimum spanning set, then renders it to a SQL statement.
 * 
 * FEATURES
 * --------------------------------------
 *  -> Generates a minimum SQL statement every time
 *  -> Traps condition structures that reduce to the universal set
 *  -> Supports column prefixes for use in JOIN queries
 *  -> Token hashing for reduced memory usage
 *  -> Extremely fast O(log n!)
 *
 * @see http://en.wikipedia.org/wiki/Trie
 * @see http://en.wikipedia.org/wiki/Spanning_set
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

class FOX_queryBuilder_whereMatrix {

	
	var $struct;				    // Structure of database table	
	var $args;				    // Args trie
	var $columns;				    // Key columns array	
	var $max_depth;				    // Maximum depth to iterate to
	var $prefix;				    // Prefix string to prepend to key names
	
	var $ctrl;				    // Control args
	var $null_token;			    // Null token identifier string
	var $mode;				    // 'matrix' or 'trie'	
	
	var $hash_table;			    // Dictionary singleton
	var $children;				    // Array of child nodes
	
	var $iterator;				    // Iterator object
	

	// ============================================================================================================ //


	/**
         * Implodes an array of heirarchical datastore keys into a single query $args array
         *
         * @version 1.0
         * @since 1.0
	 * 
	 * @param array $columns | Array of column names
	 * 
	 * @param array $args | Array of key arrays
	 *	=> ARR @param int '' | Array containing "column_name"=>"value"
	 * 
	 * @param array $ctrl | Control args
	 *	=> VAL @param bool $optimize | True to optimize the $args matrix
	 * 	=> VAL @param string $prefix | Prefix to add to column names
	 *	=> VAL @param bool $trap_null | Throw an exception if one or more walks 
	 *					in the args array collapses to "WHERE 1 = 1"
	 *
         * @return array | Exception on failure. True on success.
         */
	
	function __construct($struct, $columns, $args, $ctrl=null){
		
	    
		$this->struct = $struct;			
		$this->columns = $columns;
		$this->args = $args;
		$this->max_depth = count($columns) - 1;
		
		$ctrl_default = array(
			'prefix' => '',
			'optimize' => true,
			'trap_null' => true,
			'hash_token_vals' => true,
			'null_token' => '*',
			'mode' => 'matrix'
		);

		$this->ctrl = wp_parse_args($ctrl, $ctrl_default);
		
		if($this->ctrl['hash_token_vals'] == true){
		    
			$this->hash_table = new FOX_hashTable();
		}
		
		$this->null_token = $this->ctrl['null_token'];
		$this->mode = $this->ctrl['mode'];		
						
		try {		    
			$result = $this->build();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error during build process",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}
		
		return $result;
		
	}
	
	
	/**
         * Optimizes the $args matrix passed during instantiations, and assembles it into an object trie
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
	
	public function build() {
	    
	    
		if( $this->ctrl['mode'] == 'matrix' ){
		    
		    
			if($this->ctrl['optimize'] == true){

				$this->columns = FOX_trie::optimizeMatrix($this->args, $this->columns);			
			}

			if($this->ctrl['hash_token_vals'] == true){	

				$args_array = FOX_trie::loftMatrixHash($this->args, $this->columns, $this->hash_table, $ctrl=null);							
			}
			else {
				$args_array = FOX_trie::loftMatrix($this->args, $this->columns, $ctrl=null);
			}									
			
		}		
		else {
		    
			if($this->ctrl['optimize'] == true){

			    
				$flatten_ctrl = array(
					'null_token' => $this->null_token
				);
				
				$flattened = FOX_trie::flattenAssocTrie($this->args, $this->columns, $flatten_ctrl);
				
				$this->columns = FOX_trie::optimizeMatrix($flattened, $this->columns);
				
				$loft_ctrl = array(
					'null_token' => $this->null_token
				);				
				
				$args_array = FOX_trie::loftMatrix($flattened, $this->columns, $loft_ctrl);	
			
			
			}
			else {				   
				$args_array = $this->args;
			}
		    
		}
		
		

		// Convert the array into a SQL statement and params array
		// =====================================================================
		
		try {
		    
			$this->iterator = new FOX_queryBuilder_whereMatrix_iterator(array(

				'base'	    => $this,
				'parent'    => null,			    
				'value'	    => $this->null_token,   // Since this is the root node, we 
								    // set its key to NULL			    
				'args'	    => $args_array,			    			    
				'depth'	    => -1,		    // We set the depth to -1 to flag this as the root
								    // node. -1 is used as the flag value because its 
								    // impossible for a node to have an order less than zero.
			    
								    // @see http://en.wikipedia.org/wiki/Trie
			));	
			
		}
		catch (FOX_Exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error creating root node",
				'data'=>array("args"=>$this->args, "columns"=>$this->columns, 
					      "max_depth"=>$this->max_depth, "ctrl"=>$this->ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}	    
	    
		return true;
		
	}
	
	
	public function render() {
		
		try {
			$result = $this->iterator->reduce();
		}
		catch (FOX_Exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error during reduce",
				'data'=>array("args"=>$this->args, "columns"=>$this->columns, 
					      "max_depth"=>$this->max_depth, "ctrl"=>$this->ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}		

		if( $result == false ){
		    
			if( $this->ctrl['trap_null'] == true ){

				// @see http://en.wikipedia.org/wiki/Universal_set 
			    
				$error_msg =  "INTERLOCK TRIP: One or more walks in the args array reduces to the universal set, ";
				$error_msg .= "which is equivalent to 'WHERE 1 = 1'. If used in a DELETE operation, this would ";				
				$error_msg .= "have destroyed an entire table. If this is actually your design intent, set \$ctrl['trap_null'] = ";
				$error_msg .= "false to disable this interlock."; 

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"$error_msg",
					'data'=>array("args"=>$this->args, "columns"=>$this->columns, 
						    "max_depth"=>$this->max_depth, "ctrl"=>$this->ctrl),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));			
			}
			else {
				return array('where'=> " AND TRUE", 'params'=>array());
			}
		    
		}
		else {
		
			try {
				$sql_result = $this->iterator->render();
			}
			catch (FOX_Exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error during render",
					'data'=>array("args"=>$this->args, "columns"=>$this->columns, 
						    "max_depth"=>$this->max_depth, "ctrl"=>$this->ctrl),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}		    
		    
			
			return array('where'=> $sql_result["where"], 'params'=>$sql_result["params"]);
		}
		
		
	}
	
	
	/**
         * Recursively dumps the entire node tree
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param int $depth | Depth of this node in the tree. First node is (int)0
         * @return string | Node tree in string form
         */
	
	public function dump(){	    

		echo "\n\n";	    
		echo $this->iterator->dump(0);
	}


	
} // End of class FOX_queryBuilder_whereMatrix

?>