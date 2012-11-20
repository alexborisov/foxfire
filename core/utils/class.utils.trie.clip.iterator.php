<?php

/**
 * FOXFIRE TRIE - CLIP ITERATOR
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

class FOX_trie_clip_iterator {


	var $base;				    // Controller class reference
	var $children;				    // Array of child nodes	
	var $trie;				    // Trie structure	
	var $depth;				    // Current depth of this node. Root node is (int)-1	
	

	// ============================================================================================================ //

	
	/**
         * Recursively reduces a FOX args matrix into a minimum SQL WHERE clause
         *
         * @version 1.0
         * @since 1.0
         *
	 * @param array $args | Control args
	 *	=> VAL @param obj $base | Reference to base class
	 *	=> VAL @param array $trie | Trie structure to process
	 *	=> VAL @param int $depth | Depth of this recursor instance
	 *
         * @return NULL | Exception on failure. Null on success.
         */
	
	function __construct($args){
	    
		$this->base = $args['base'];	    		
		$this->trie = $args['trie'];			
		$this->depth = $args['depth'];																
		$this->children = array();												
	}
	
	
	/**
         * Recursively builds a clipped tree structure
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return array | Exception on failure. Trie structure on success.
         */
	
	function render(){

	    	    		
		// CASE 1: This is a root node or intermediate node
		// ===============================================================
	    
		if( $this->depth < $this->base->max_depth) {
		    
			
			if( !is_array($this->trie) ||	 // Handle specifying end-nodes as 'key'=>true instead of 'key'=>array()												    
			     FOX_sUtil::keyExists($this->base->null_token, $this->trie)	    // Clip null branches
			){
			    
				return array();
			}
			else {
					

				foreach( $this->trie as $key => $data ){			    
				    
					try {
						$child_node = new FOX_trie_clip_iterator(array(

							'base'	    => $this->base,
							'trie'	    => $data,
							'depth'	    => $this->depth + 1,
						));
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>1,
							'text'=>"Error creating child node",
							'data'=>array(	
									'current_node'=>array(
												'trie'=>$this->trie,
												'depth'=>$this->depth					
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
						$reduced = $child_node->render();
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>2,
							'text'=>"Error reducing child node",
							'data'=>array(	
									'current_node'=>array(
												'trie'=>$this->trie,
												'depth'=>$this->depth,					
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

					$this->children[$key] = $reduced;				    				    				

				}
				unset($key, $data);

			
			}

			return $this->children;				
				
			
		}
		// CASE 2: This is an end node
		// ===============================================================
		
		else {
		    
			return array();						
		}
		
				
	}	

		
	
} // End of class FOX_trie_clip_iterator

?>