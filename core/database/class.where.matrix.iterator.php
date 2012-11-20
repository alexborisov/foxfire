<?php

/**
 * BP-MEDIA QUERY BUILDER - WHERE MATRIX ITERATOR
 * A self-optimizing doubly-linked trie class that accepts condition arguments as a flat array, assembles
 * them into a trie, reduces it to a minimum spanning set, then renders it to a SQL statement.
 *
 * @see http://en.wikipedia.org/wiki/Trie
 * @see http://en.wikipedia.org/wiki/Spanning_set
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

class BPM_queryBuilder_whereMatrix_iterator {


	var $base;				    // Controller class reference
	var $parent;				    // Parent node reference	
	var $children;				    // Array of child nodes
		
	var $value;				    // The key name for this node
	var $column;				    // The column name for this node	
	var $args;				    // Args trie	
	var $depth;				    // Current depth of this node. Root node is (int)-1	
	
	var $is_null;				    // True if this node has a null value
	var $is_end_node;			    // True if this is an end node
	var $is_root_node;			    // True if this is the root node
	
	var $is_nested;				    // True during the render process if this is a nested node
	

	// ============================================================================================================ //

	
	// ============================================================================================================ 
	// Due to the limitations of PHP's pointer aka "reference" implementation, its currently not possible to 
	// use "pointer short-circuiting", "pointer tables" or any other memory and CPU optimization techniques used
	// in modern directed acyclic graph libraries. If we ever reach the point where we need to run matrix-queries
	// with huge numbers of rows in complex nested structures, we might have to write a PHP extension in C++
	// ============================================================================================================	   	

	
	/**
         * Recursively reduces a BPM args matrix into a minimum SQL WHERE clause
         *
         * @version 0.1.9
         * @since 0.1.9
         *
         * @param array $struct | Structure of db table, @see class BPM_db header for examples
	 * @param array $args | Nested array of args.
	 * @param array $key_col | Heirarchical array of key columns
	 * @param int $depth | Levels of recursion remaining.
	 * 
	 * @param array $ctrl | Control parameters for the query
	 *	=> VAL @param string $value | Set current page (used when traversing multi-page data sets)
	 *	=> VAL @param int $per_page | Max number of rows to return in 
	 *
         * @return NULL | Exception on failure. Null on success.
         */
	
	function __construct($args){

	    
		$this->base = $args['base'];	    
		$this->parent = $args['parent'];		
		
		$this->value = $args['value'];			
		
		$this->args = $args['args'];			
		$this->depth = $args['depth'];	
		
		$this->column = $this->base->columns[$args['depth']];
		$this->format = $this->base->struct["columns"][$this->column]["format"];		
		
		$this->offset = '';
		
		for($i=-1; $i<$this->depth; $i++){
		    
			$this->offset .= "   ";  
		}
				
		$this->children = array();
		
		if($this->value == $this->base->null_token){
		    
			$this->is_null = true;
		}
		else {		    
			$this->is_null = false;
		}
		
		if($this->depth < 0){
		    
			$this->is_root_node = true;
		}
		else {		    
			$this->is_root_node = false;
		}				
								
	}
	
	
	/**
         * Recursively reduces this node and all its descendents to minimal form
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
         * @return bool | Exception on failure. True on success.
         */
	
	function reduce(){

	    	    		
		// CASE 1: This is a root node or intermediate node
		// ===============================================================
	    
		if( $this->depth < $this->base->max_depth) {

		    
			// Handle specifying end-nodes as 'key'=>true instead of 'key'=>array()
			
			if( !is_array($this->args) ){
			    
				$this->args = array($this->args);
			}

			
			$this->is_end_node = false;			
			
			foreach( $this->args as $key => $data ){

			    
				if( ($this->base->ctrl['hash_token_vals'] == true) && ($key != $this->base->null_token) ){	
				    					
					$valid = false;
					$child_value = $this->base->hash_table->get($key, $valid);
					
					if(!$valid){

						throw new BPM_exception( array(
							'numeric'=>1,
							'text'=>"Invalid hash key",
							'data'=>array(	
									'current_node'=>array(
												'columns'=>$this->columns,
												'column'=>$this->column,								    
												'value'=>$this->value,
												'args'=>$this->args,
												'depth'=>$this->depth,
												'is_null'=>$this->is_null,
												'is_end_node'=>$this->is_end_node,
												'is_root_node'=>$this->is_root_node						
									),
									'child_node'=>array(
												'args'=>$data, 								    
												"key"=>$key, 
									)
							),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));
					
					}
										
				}
				else {
					$child_value = $key;
				}
			
				try {
					$child_node = new BPM_queryBuilder_whereMatrix_iterator(array(

						'base'	    => $this->base,
						'parent'    => $this,
						'value'	    => $child_value,
						'args'	    => $data,
						'depth'	    => $this->depth + 1,
					));
				}
				catch (BPM_exception $child) {

					throw new BPM_exception( array(
						'numeric'=>2,
						'text'=>"Error creating child node",
						'data'=>array(	
								'current_node'=>array(
											'columns'=>$this->columns,
											'column'=>$this->column,								    
											'value'=>$this->value,
											'args'=>$this->args,
											'depth'=>$this->depth,
											'is_null'=>$this->is_null,
											'is_end_node'=>$this->is_end_node,
											'is_root_node'=>$this->is_root_node						
								),
								'child_node'=>array(
											'args'=>$data, 
											'depth'=>$this->depth + 1, 
											"value"=>$child_value, 
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
						'numeric'=>3,
						'text'=>"Error reducing child node",
						'data'=>array(	
								'current_node'=>array(
											'columns'=>$this->columns,
											'column'=>$this->column,								    
											'value'=>$this->value,
											'args'=>$this->args,
											'depth'=>$this->depth,
											'is_null'=>$this->is_null,
											'is_end_node'=>$this->is_end_node,
											'is_root_node'=>$this->is_root_node						
								),
								'child_node'=>array(
											'args'=>$data, 
											'depth'=>$this->depth + 1, 
											"value"=>$child_value, 
								)
						 ),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}				
				
				if( $this->is_null && !$reduced ){
				    
					// If this a null node, and one or more of its children are null, we 
					// can trim this branch, so there's no point continuing with the loop.
				    
					return false;	
					
				}
				elseif( !$this->is_null && ($reduced == false) ) {
				    				
					// If this is not a null node, but one of its children is a trimmed 
					// branch, this node becomes an end node
				    
					$this->children = array();
					return array($this);
				}
				else {
					// Otherwise, attach the child node, and any branches attached
					// to it, to this node
				    
					$this->children = array_merge($this->children, $reduced);				    				    
				}


			}
			unset($key, $data);
			
			
			if($this->is_null){
			    
				return $this->children;
			}
			else {			    
				// If this node has no children, it becomes an end node
			    
				if( count($this->children) == 0 ){

					$this->is_end_node = true;
				}
				
				return array($this);				
			}	

			
		}
		// CASE 2: This is an end node
		// ===============================================================
		
		else {
				
			$this->is_end_node = true;

			if( $this->is_null ){
			    
				// If this a null node, we can trim it from the tree
				return false;
			}
			else {					    
				return array($this);
			}
			
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
				
	    
		$where = '';
		$params_list = array();
		
		
		// Handle simple nodes
		// ===================================================
		
		if(!$this->is_root_node){
		
			$where .= $this->base->prefix . $this->column . " = " . $this->format;						
			$params_list[] = $this->value;		    
		}
		
		if( !count($this->children) ){
		    
			if($this->is_root_node){

				return false;		    
			}	
			else {			
				return array('where'=>$where, 'params'=>$params_list);
			}
		}
				
		// Split node into end nodes and child nodes, while
		// grouping end nodes by column
		// ===================================================
		
		$end_groups = array();

		foreach( $this->children as $node_id => $child_node ){

			if( !count($child_node->children) ){

				$end_groups[$child_node->column][$node_id] = $child_node;
				unset($this->children[$node_id]);
			}
		}
		unset($node_id, $child_node);
		
		$end_group_count = count($end_groups);
		$child_count = count($this->children);
		
		$block_count = $end_group_count + $child_count;				
		
		$where .= " AND ";
		
		if($block_count > 1){
		    
			$where .= "(";		    
		}		
		
		// Render end node groups
		// ===================================================
		
		$end_groups_left = $end_group_count - 1;
		
		foreach( $end_groups as $col_name => $nodes ){
		    
			$nodes_left = count($nodes) - 1;
			
			// If the group only has a single token, implode it 
			// to an "=" statement
			
			if(count($nodes) == 1){
			    
				// Since we're using a number-indexed array, we can't fetch
				// the first key in the array by simply using $nodes[0]
			    
				$node_keys = array_keys($nodes);
			    
				$where .= $col_name . " = ";
				$where .= $nodes[$node_keys[0]]->format;
				$params_list[] = $nodes[$node_keys[0]]->value;	

				unset($node_keys);
			}
			
			// Otherwise, render it to an "IN" statement
			else {
			
				$where .= $col_name . " IN(";

				foreach($nodes as $node_id => $node){

					$where .= $node->format;
					$params_list[] = $node->value;

					if($nodes_left > 0){

						$where .= ",";
						$nodes_left--;
					}
				}
				unset($node);

				$where .= ")";
			}
			
			if( $end_groups_left > 0 ){

				$where .= " OR ";
				$end_groups_left--;
			}			
		    
		}
		unset($col_name, $nodes);
		
		
		// If necessary, add bridging OR statement
		// ===================================================
		
		if( $end_group_count && $child_count ){
		    
			$where .= " OR ";		    
		}	
		
		
		// Render child nodes
		// ===================================================
		
		$children_left = $child_count - 1;
		
		if($children_left){	
		    
			$this->is_nested = true;
		}
		else {
			$this->is_nested = false;
			
			foreach( $this->children as $child_node ){
			    
				if( count($child_node->children) && !self::isNested($this->parent) ){

					$this->is_nested = true;
				}
			}
		}
		
		
		foreach( $this->children as $child_node ){
		    		    		    
			if($this->is_nested){	
			    
				$where .= "(";		    
			}
			
			$child_result = $child_node->render();		
			
			$where .= $child_result['where'];			
			$params_list = array_merge($params_list, $child_result['params']);			
			
			if($this->is_nested){	
			    
				$where .= ")";					
			}
			
			if( $children_left > 0 ){

				$where .= " OR ";
				$children_left--;
			}
			else {
				$this->is_nested = false;
			}
		    
		}
		unset($child_node);
				
		
		if($block_count > 1){
		    
			$where .= ")";		    
		}		
				
		return array('where'=>$where, 'params'=>$params_list);					

	}	
	
	
	/**
         * Determines if one or more of this object's ancestor nodes has opened a bracket pair
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param mixed $obj | Instance of BPM_queryBuilder_whereMatrix_iterator object 
         * @return bool | True if nested. False if not.
         */
	
	function isNested($obj){
	    
	    
		if(!$obj){

			return false;
		}	    
		elseif($obj->is_nested == true){

			return true;
		}
		else {
			return self::isNested($obj->parent);		
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

		foreach( $this->children as $child ){

			$out .= $indent . $child->dump($depth + 1);
		}
		unset($child);

		return $out;
	    	    
	}	

	
	
} // End of class BPM_queryBuilder_whereMatrix_iterator

?>