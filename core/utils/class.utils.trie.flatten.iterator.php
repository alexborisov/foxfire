<?php

/**
 * BP-MEDIA TRIE - FLATTEN ITERATOR
 * Flattens a trie structure to matrix
 * 
 * @see http://en.wikipedia.org/wiki/Trie
 * @see http://en.wikipedia.org/wiki/Linked_list
 * 
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Database
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/wiki/DOCS_BPM_db_top
 *
 * ========================================================================================================
 */

class BPM_trie_flatten_iterator {


	var $base;				    // Controller class reference
	var $parent;				    // Parent node reference	
	var $children;				    // Array of child nodes
		
	var $value;				    // The key name for this node
	var $column;				    // The column name for this node	
	var $trie;				    // Ftie structure	
	var $depth;				    // Current depth of this node. Root node is (int)-1	
	
	var $is_null;				    // True if this node has a null value
	var $is_end_node;			    // True if this is an end node
	var $is_root_node;			    // True if this is the root node
	

	// ============================================================================================================ //

	
	/**
         * Recursively reduces a BPM args matrix into a minimum SQL WHERE clause
         *
         * @version 0.1.9
         * @since 0.1.9
         *
	 * @param array $args | Control args
	 *	=> VAL @param obj $base | Reference to base class
	 *	=> VAL @param obj $parent | Reference to parent node	 
	 *	=> VAL @param array $trie | Trie structure to process
	 *	=> VAL @param int $depth | Depth of this node
	 *	=> VAL @param string $value | Value of this node		 	 
	 *
         * @return NULL | Exception on failure. Null on success.
         */
	
	function __construct($args){

	    
		$this->base = $args['base'];	    
		$this->parent = $args['parent'];
		
		$this->trie = $args['trie'];			
		$this->depth = $args['depth'];			
		
		$this->value = $args['value'];									
						
		$this->children = array();
		
		if($this->value == $this->base->null_token){
		    
			$this->is_null = true;
		}
		else {		    
			$this->is_null = false;
		}
		
		if($this->depth < 0){
		    
			$this->is_root_node = true;
			$this->column = 'ROOT';	
		}
		else {		    
			$this->is_root_node = false;
			$this->column = $this->base->columns[$args['depth']];	
		}		
								
	}
	
	
	/**
         * Recursively reduces this node and all its descendents to minimal form
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
         * @return array | Exception on failure. Array of child nodes on success.
         */
	
	function reduce(){

	    	    		
		// CASE 1: This is a root node or intermediate node
		// ===============================================================
	    
		if( $this->depth < $this->base->max_depth) {

		    
			// Handle specifying end-nodes as 'key'=>true instead of 'key'=>array()
			
			if( !is_array($this->trie) ){
			    
				$this->is_end_node = true;
			}
			else {
			
				$this->is_end_node = false;			

				foreach( $this->trie as $key => $data ){			    
				    
					try {
						$child_node = new BPM_trie_flatten_iterator(array(

							'base'	    => $this->base,
							'parent'    => $this,
							'value'	    => $key,
							'trie'	    => $data,
							'depth'	    => $this->depth + 1,
						));
					}
					catch (BPM_exception $child) {

						throw new BPM_exception( array(
							'numeric'=>1,
							'text'=>"Error creating child node",
							'data'=>array(	
									'current_node'=>array(
												'columns'=>$this->columns,
												'column'=>$this->column,								    
												'value'=>$this->value,
												'trie'=>$this->trie,
												'depth'=>$this->depth,
												'is_null'=>$this->is_null,
												'is_end_node'=>$this->is_end_node,
												'is_root_node'=>$this->is_root_node						
									),
									'child_node'=>array(
												'args'=>$data, 
												'depth'=>$this->depth + 1, 
												"value"=>$key, 
									)
							),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));
					}

					try {
						$reduced = $child_node->reduce();
					}
					catch (BPM_exception $child) {

						throw new BPM_exception( array(
							'numeric'=>2,
							'text'=>"Error reducing child node",
							'data'=>array(	
									'current_node'=>array(
												'columns'=>$this->columns,
												'column'=>$this->column,								    
												'value'=>$this->value,
												'trie'=>$this->trie,
												'depth'=>$this->depth,
												'is_null'=>$this->is_null,
												'is_end_node'=>$this->is_end_node,
												'is_root_node'=>$this->is_root_node						
									),
									'child_node'=>array(
												'args'=>$data, 
												'depth'=>$this->depth + 1, 
												"value"=>$key, 
									)
							),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));		    
					}								

					$this->children = array_merge($this->children, $reduced);				    				    				

				}
				unset($key, $data);


				if( count($this->children) == 0 ){

					$this->is_end_node = true;
				}
			
			}

			return array($this);				
				
			
		}
		// CASE 2: This is an end node
		// ===============================================================
		
		else {

			$this->is_end_node = true;	
			
			return array($this);
		}
		
				
	}	

	
	/**
         * Recursively renders this node and all its descendents to a print string and parameter
	 * array, which combined, form a valid SQL WHERE clause.
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
         * @return array | Exception on failure. String and params in vsprintf() format on success.
         */

	public function render(){
					   
		
		if( $this->is_end_node ){
		    
			$this->base->result[] = self::buildRow($this);
		}
		else {
		    
			foreach( $this->children as $child_node ){

				$child_node->render();		
			}
			unset($child_node);
		
		}
		
		return true;

	}	
	
	
	/**
         * Builds a row in the flattened result array
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param mixed $obj | Instance of BPM_queryBuilder_whereMatrix_iterator object 
         * @return array | Empty array on root node, otherwise array of child node row arrays.
         */
	
	function buildRow($obj){
	    
	    
		if(!$obj || $obj->is_root_node){

			return array();
		}
		else {
		    
			if(!$obj->is_null){
			
				if( $obj->is_end_node && ($this->base->mode == 'data') ){
				
					$this_node = array( 
							    $obj->column=>$obj->value,
							    $obj->base->data_key=>$obj->trie
					);				
				}
				else {
					$this_node = array($obj->column=>$obj->value);
				}
			}
			else {
				$this_node = array();
			}
						
			$prev_nodes = self::buildRow($obj->parent);
			
			return array_merge($prev_nodes, $this_node);		
		}
	    
	}
	
	
	/**
         * Recursively dumps the entire node tree
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param int $depth | Depth of this node in the tree. First node is (int)0
         * @return string | Node tree in string form
         */
	
	function dump($depth){
	    	    
		$indent = '';

		for( $i = 0; $i < $depth; $i++ ){

			$indent .= ' ';
		}

		$out = "\n" . $indent . '[' . $this->column . ":" . $this->value . ']';
		
		if($this->is_end_node){
		    
		    $out .= " end";
		}

		foreach( $this->children as $child ){

			$out .= $indent . $child->dump($depth + 1);
		}
		unset($child);

		return $out;
	    	    
	}	

	
	
} // End of class BPM_trie_flatten_iterator

?>