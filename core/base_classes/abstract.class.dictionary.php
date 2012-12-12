<?php

/**
 * FOXFIRE DICTIONARY BASE CLASS
 * Implements an efficient bidirectional token<=>id store. Tokens and ids are guaranteed to be
 * unique. Each pair uses two cache pages.
 * 
 * FEATURES
 * --------------------------------------
 *  -> Transient cache support
 *  -> Persistent cache support
 *  -> Fully atomic operations
 *  -> Advanced error handling
 *  -> Multi-thread safe 
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Base Classes
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

abstract class FOX_dictionary_base extends FOX_db_base {

    
    	var $process_id;				// Unique process id for this thread. Used by FOX_db_base for cache 
							// locking. Loaded by descendent class.
	
	var $cache = array(				// Main cache array for this class
			    'tokens' => array(),
			    'ids' => array()
			  );	
	
	var $mCache;					// Local copy of memory cache singleton. Used by FOX_db_base for cache 
							// operations. Loaded by descendent class.		
	
	
	/* ================================================================================================================
	 *	Cache Strategy: "paged"
	 *
	 *	=> ARR array $cache | Main cache array
	 * 
	 *	    => ARR array $tokens | Token dictionary
	 * 
	 *		=> KEY string '' | token name  ------- ( cache page 'token_*' ) ------
	 *		    => VAL int | token id
	 *		----------------------------------------------------------------------
	 * 
	 *	    => ARR array $ids | Id dictionary
	 * 
	 *		=> KEY int '' | token id  ------------ ( cache page 'id_*' ) ---------
	 *		    => VAL string | token name
	 *		---------------------------------------------------------------------- 
	 * 
	 * */
	
	// ================================================================================================================


	/**
	 * Fetches one or more tokens from the db
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int/array $tokens | Single token as string. Multiple tokens as array of strings.
	 * @return array | Exception on failure. Array of tokens on success.
	 */
	
	public function dbFetchToken($tokens){
		
		$db = new FOX_db();
		$struct = $this->_struct();
		
		if( is_null($tokens)){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"null parameter passed as tokens exception",
				'data'=>array("tokens"=>$tokens),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
	    
		// Build the query
		// ==========================================

		$args = array( array("col"=>"token", "op"=>"=", "val"=>$tokens) );
	
		$ctrl = array("format"=>"array_key_single", "key_col"=>"token", "val_col"=>"id", 
				"sort"=>array( array( "col"=>"id", "sort"=>"ASC"))
			);
		
		try {
			$result = $db->runSelectQuery($struct, $args, $columns=null, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				'numeric'=>2,
				'text'=>"DB select exception",
				'data'=>array("args"=>$args, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Update the cache with the fetched values
		// ==========================================

		if($result){
			
			$cache_update = array();

			foreach($result as $token_name => $token_id){
				
				// This class is unique because each token<=>key pair has two cache pages, one for
				// the token=>key relationship and one for the key=>token relationship. We separate
				// them in the class's cache namespace by prefixing them with "token_" and "id_". We
				// combine the prefixed items into a single array so we can write it to the cache
				// in one operation.
				
				$cache_update["id_".$token_id] = $token_name;
				$cache_update["token_".$token_name] = $token_id;
				
			}
			unset($token_name, $token_id);
			
			try {
				self::writeCachePage($cache_update);
			}
			catch(FOX_exception $child){
			    
				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Cache write error",
					'data'=>array("cache update"=>$cache_update),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}
			
			// Update the class cache
			foreach($result as $token_name => $token_id){

				$this->cache["tokens"][$token_name] = $token_id;
				$this->cache["ids"][$token_id] = $token_name;							
			}
			unset($token_name, $token_id);
			
			return $result;
			
		}
		else {
			return array();
		}
		
	}
	
	
	/**
	 * Fetches one or more ids from the db
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int/array $ids | Single id as int. Multiple ids as array of ints.
	 * @return array | Exception on failure. Array of ids on success.
	 */
	
	public function dbFetchId($ids){
	
		$db = new FOX_db();
		$struct = $this->_struct();		
		
	    	if( is_null($ids)) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"null parameter passed as ids exception",
				'data'=>array("ids"=>$ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		// Build the query
		// ==========================================

		$args = array( array("col"=>"id", "op"=>"=", "val"=>$ids) );
	
		$ctrl = array("format"=>"array_key_single", "key_col"=>"id", "val_col"=>"token" , 
				"sort"=>array( array( "col"=>"id", "sort"=>"ASC"))
			);


		try {
			$result = $db->runSelectQuery($struct, $args, $columns=null, $ctrl);
		}
		catch(FOX_exception $child){
		    
		    	throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"DB select exception",
				'data'=>array("args"=>$args, "ctrl"=>$ctrl),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		
		// Update the cache with the fetched values
		// ==========================================

		if($result){
			
			$cache_update = array();

			foreach($result as $token_id => $token_name){
				
				// This class is unique because each token<=>key pair has two cache pages, one for
				// the token=>key relationship and one for the key=>token relationship. We separate
				// them in the class's cache namespace by prefixing them with "token_" and "id_". We
				// combine the prefixed items into a single array so we can write it to the cache
				// in one operation.
				
				$cache_update["id_".$token_id] = $token_name;
				$cache_update["token_".$token_name] = $token_id;
				
			}
			unset($token_name, $token_id);
			
			try {
				self::writeCachePage($cache_update);
			}
			catch(FOX_exception $child){
			    
				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Cache write error",
					'data'=>array("cache update"=>$cache_update),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}
			
			// Update the class cache
			
			foreach($result as $token_id => $token_name){

				$this->cache["tokens"][$token_name] = $token_id;
				$this->cache["ids"][$token_id] = $token_name;				
			}
			unset($token_name, $token_id);			
			
			
			return $result;
			
		}
		else {
			return array();
		}
		
	}	
	
	
	/**
	 * Fetches one or more tokens from the persistent cache
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string/array $tokens | Single token as string. Multiple tokens as array of strings.
	 * @return array | Exception on failure. Array of tokens on success.
	 */
	
	public function cacheFetchToken($tokens){

		
	    	if(is_null($tokens)){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"null parameter passed as tokens exception",
				'data'=>array("tokens"=>$tokens),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}		
		
		if(!is_array($tokens) ){
			$tokens = array($tokens);
		}
		
		$cache_keys = array();
		
		foreach($tokens as $token){

			$cache_keys[] = "token_" . $token;
		}
		unset($token);
		
		
		try {			
			$raw_cache_result = self::readCachePage($cache_keys);
		}
		catch(FOX_exception $child){
		    
		    	throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Cache getMulti exception",
				'data'=>array("cache_keys"=>$cache_keys),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}
		
		$result = array();
		
		foreach($raw_cache_result as $raw_token_name => $token_id){
		    
			// Remove prefix from token name
			$prefix_length = strlen("token_");
			$token_name = substr($raw_token_name, $prefix_length, strlen($raw_token_name) - $prefix_length);
			
			// Update class cache
			$this->cache["tokens"][$token_name] = $token_id;	
			$this->cache["ids"][$token_id] = $token_name;
			
			$result[$token_name] = $token_id;
			
		}
		unset($raw_token_name, $token_id, $prefix_length, $token_name);	
				
		return $result;
		
	}	
	
	
	/**
	 * Fetches one or more tokens from the persistent cache
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int/array $ids | Single id as int. Multiple ids as array of int.
	 * @return array | Exception on failure. Array of ids with the tokens as their values.
	 */
	
	public function cacheFetchId($ids){
   
	    
	    	if( is_null($ids)) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"null parameter passed as ids exception",
				'data'=>array("tokens"=>$ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
				
		if(!is_array($ids) ){
			$ids = array($ids);
		}
		
		$cache_keys = array();
		
		foreach($ids as $id){

			$cache_keys[] = "id_" . $id;
		}
		unset($id);
		
		try {		
			$raw_cache_result = self::readCachePage($cache_keys);
		}
		catch(FOX_exception $child){
		    
		    	throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Cache getMulti exception",
				'data'=>array("cache_keys"=>$cache_keys),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));  
		}		
		
		$result = array();
		
		foreach($raw_cache_result as $raw_token_id => $token_name){
		    
			// Remove prefix from token name
			$prefix_length = strlen("id_");
			$token_id = substr($raw_token_id, $prefix_length, strlen($raw_token_id) - $prefix_length);
			
			// Update class cache
			$this->cache["tokens"][$token_name] = $token_id;	
			$this->cache["ids"][$token_id] = $token_name;
			
			$result[$token_id] = $token_name;
			
		}
		unset($raw_token_id, $token_name, $prefix_length, $token_id);		
		
		return $result;
		
	}
	
	
	/**
	 * Adds one or more tokens to the database
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string/array $tokens | Single token as string. Multiple tokens as array of strings.
	 * @return int/array | Exception on failure. Int id if passed single string. token_name=>token_id array if passed array of string.
	 */
	
	public function addToken($tokens){
		

		$db = new FOX_db();
		$struct = $this->_struct();
		
	    	if( is_null($tokens)){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Null parameter passed as tokens exception",
				'data'=>array("tokens"=>$tokens),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}	 	    
		
		if(!is_array($tokens) ){
		    
			$tokens = array($tokens);
			$single = true;
		}
		else {
			$single = false;
		}
				
		// Spin the supplied tokens into the correct format for runInsertQueryMulti()
		$data = array();
		
		foreach($tokens as $token){
		    
			$data[] = array("token"=>$token);
		}
		unset($token);		

		try {
		    
			$db->runInsertQueryMulti($struct, $data, $columns, $ctrl=null);			
		}
		catch(FOX_exception $child){
		    
		    	throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error writing to database",
				'data'=>array("data"=>$data),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}
		
			
		// Update the persistent cache
		// ==============================================
		
		$cache_update = array();
		
		// The $db->insert_id will contain the id of the *first* item inserted
		$token_id = $db->insert_id;			
		
		foreach($tokens as $token_name){

			// This class is unique because each token<=>key pair has two cache pages, one for
			// the token=>key relationship and one for the key=>token relationship. We separate
			// them in the class's cache namespace by prefixing them with "token_" and "id_". We
			// combine the prefixed items into a single array so we can write it to the cache
			// in one operation.

			$cache_update["id_".$token_id] = $token_name;
			$cache_update["token_".$token_name] = $token_id;
			
			// Id values will be incremented by $db->auto_increment_increment for 
			// each successive row
			
			$token_id += $db->auto_increment_increment;

		}
		unset($token_name, $token_id);
		
		
		try {
			self::writeCachePage($cache_update);
		}
		catch(FOX_exception $child){

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Cache write error",
				'data'=>array("cache update"=>$cache_update),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}
		
		
		// Update the class cache / build $result
		// ==============================================

		$token_id = $db->insert_id;
		$result = array();
		
		foreach($tokens as $token_name){

			$this->cache["tokens"][$token_name] = $token_id;
			$this->cache["ids"][$token_id] = $token_name;
			
			$result[$token_name] = $token_id;			
			
			$token_id += $db->auto_increment_increment;

		}
		unset($token_name, $token_id);		

		
		if($single == true){
		   
			return $result[$tokens[0]];
		}
		else {
			return $result;
		}
		
	}	


	/**
	 * Fetches one or more tokens. If the tokens do not exist in the dictionary, new ids
	 * will be created for them
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string/array $token | Single token as string. Multiple tokens as array of strings.
	 * @return int/array | Exception on failure. Int (single token). Array of int (multiple tokens)
	 */

	public function getToken($tokens){

	    
	    	if( is_null($tokens)){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"null parameter passed as tokens exception",
				'data'=>array("tokens"=>$tokens),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));  
		}	
		
		if(!is_array($tokens) ){
		    
			$tokens = array($tokens);
			$single = true;
		}
		else {
			$single = false;
		}
		
		// Fetch all tokens currently stored in the class cache
		// ======================================================
		
		$result = array();		
		$missing_tokens = array();
		
		foreach($tokens as $token){
		    
			if( FOX_sUtil::keyExists($token, $this->cache["tokens"]) ){

				$result[$token] = $this->cache["tokens"][$token];
			}
			else {
				$missing_tokens[] = $token;
			}
		}
		unset($token);
		
		
		// Try to fetch missing tokens from the persistent cache
		// ======================================================
						
		if( count($missing_tokens) > 0 ){
		    
			try {
				$cache_tokens = $this->cacheFetchToken($missing_tokens);
			}
			catch(FOX_exception $child) {
			    
				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"CacheFetchToken exception",
					'data'=>array("tokens"=>$missing_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}
			
			$result = array_merge($result, $cache_tokens);			
			$missing_tokens = array_diff($missing_tokens, array_keys($cache_tokens) );
			
		}
		
		
		// Try to fetch missing tokens from the database
		// ======================================================
		
		if( count($missing_tokens) > 0 ){
		    
			try {
				$db_tokens = $this->dbFetchToken($missing_tokens);
			}
			catch(FOX_exception $child) {
			    			
				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error in self::dbFetchToken()",
					'data'=>array("tokens"=>$missing_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}
			
			$result = array_merge($result, $db_tokens);			
			$missing_tokens = array_diff($missing_tokens, array_keys($db_tokens) );
			
		}
		
		
		// Generate id's for any remaining tokens
		// ======================================================
		
		if( count($missing_tokens) ){
		    
			try {
				$insert_tokens = $this->addToken($missing_tokens);
			}
			catch(FOX_exception $child) {
			    			
				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error in self::addToken()",
					'data'=>array("tokens"=>$missing_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}	
			
			$result = array_merge($result, $insert_tokens);
			
		}
		
		
		if($single == true){
		    
			return $result[$tokens[0]];
		}
		else {
			return $result;
		}		
	

	}
	
	
	/**
	 * Fetches one or more ids. 
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int/array $ids | Single id as int. Multiple ids as array of int.
	 * @return string/array | Exception on failure. String (single id). Array of string (multiple id's).
	 */

	public function getId($ids){
		
	    
	    	 if( is_null($ids)) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"null parameter passed as ids exception",
				'data'=>array("tokens"=>$ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		if(!is_array($ids) ){
		    
			$ids = array($ids);
			$single = true;
		}
		else {
			$single = false;
		}
		
		// Fetch all ids currently stored in the class cache
		// ======================================================
		
		$result = array();
		$missing_ids = array();
		
		foreach($ids as $id){
		    
			if( FOX_sUtil::keyExists($id, $this->cache["ids"]) ){

				$result[$id] = $this->cache["ids"][$id];
			}
			else {
				$missing_ids[] = $id;
			}
		}
		unset($id);
		
		
		// Try to fetch missing ids from the persistent cache
		// ======================================================
		
		if( count($missing_ids) ){
		    
			try {
				$cache_ids= $this->cacheFetchId($missing_ids);
			}
			catch(FOX_exception $child) {
			    			
				throw new FOX_exception(  array(
					'numeric'=>2,
					'text'=>"Error in self::cacheFetchId()",
					'data'=>array("ids"=>$missing_ids),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}			
			
			if($cache_ids) {
				$result = $result + $cache_ids;
			}
			
			$missing_ids = array_diff($missing_ids, array_keys($cache_ids) );
			
		}
		
		
		// Try to fetch missing ids from the database
		// ======================================================
		
		if( count($missing_ids) ){
		    
			try {
				$db_ids = $this->dbFetchId($missing_ids);
			}
			catch(FOX_exception $child) {
			    			
				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error in self::dbFetchId()",
					'data'=>array("ids"=>$missing_ids),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}
			
			if($db_ids){
				$result = $result + $db_ids;
			}	
			
		}

		if($single == true){
		    
			return $result[$ids[0]];			
		}
		else {		    
			return $result;			
		}
		
	
	}	


	/**
	 * Drops one or more tokens.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string/array $token | Single token as string. Multiple tokens as array of strings.
	 * @return int | Exception on failure. Int number of rows affected on success.
	 */

	public function dropToken($tokens) {

	    
		$db = new FOX_db();
		$struct = $this->_struct();
		
	    	if( is_null($tokens)){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"null parameter passed as tokens exception",
				'data'=>array("tokens"=>$tokens),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		if(!is_array($tokens) ){		    
			$tokens = array($tokens);
		}		
			
		// Get the ids of all the tokens, so we can clear them from the cache
		// ==========================================================
		try {
			$token_data = $this->getToken($tokens);
		}
		catch(FOX_exception $child) {
		    
		    	throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"getToken exception",
				'data'=>array("tokens"=>$tokens),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Lock the items' cache pages (prevents them getting regenerated 
		// by other threads during the delete query)
		// ==================================================================
		
		$cache_pages = array();
		
		foreach($token_data as $token_name => $token_id){
		    
			$cache_pages[] = "id_".$token_id;
			$cache_pages[] = "token_".$token_name;		    
		}
		unset($token_name, $token_id);

		try { 
			self::lockCachePage($cache_pages); 
		}
		catch(FOX_exception $child) { 

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error locking cache pages",
				'data'=>$cache_pages,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}		
					
		// Drop the tokens from the database
		// ==========================================================				

		$args = array(
				array("col"=>"token", "op"=>"=", "val"=>$tokens)
		);

		
		try {
			$rows_changed = $db->runDeleteQuery($struct, $args);		
		}
		catch(FOX_exception $child){
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error deleting from database",
				'data'=>array("args"=>$args),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}
				
		// Flush the item's cache pages
		// ===============================		

		try { 
			self::flushCachePage($cache_pages); 
		}
		catch(FOX_exception $child) { 

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Error flushing cache pages",
				'data'=>$cache_pages,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}		
		
		// Update the class cache
		// ===============================
		
		foreach($token_data as $token_name => $token_id){
		    		   
			unset($this->cache["tokens"][$token_name]);
			unset($this->cache["ids"][$token_id]);					    
		}
		unset($token_name, $token_id);			
		
		
		return (int)$rows_changed;				

	}
	
	
	/**
	 * Drops one or more ids.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int/array $ids | Single id as int. Multiple ids as array of ints.
	 * @return int | Exception on failure. Int number of rows affected on success.
	 */

	public function dropId($ids) {


		$db = new FOX_db();
		$struct = $this->_struct();
		
	    	if( is_null($ids)){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"null parameter passed as ids exception",
				'data'=>array("tokens"=>$ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			)); 
		}	    
		
		if(!is_array($ids) ){		    
			$ids = array($ids);
		}		
			
		// Get the token names for all the ids, so we can clear 
		// them from the cache
		// ==========================================================
		
		try{
			$id_data = self::getId($ids);
		}
		catch(FOX_exception $child){
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in self::getId()",
				'data'=>array("tokens"=>$ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Lock the items' cache pages (prevents them getting regenerated 
		// by other threads during the delete query)
		// ==================================================================
		
		$cache_pages = array();
		
		foreach($id_data as $token_id => $token_name){
		    
			$cache_pages[] = "id_".$token_id;
			$cache_pages[] = "token_".$token_name;		    
		}
		unset($token_id, $token_name);

		try { 
			self::lockCachePage($cache_pages); 
		}
		catch(FOX_exception $child) { 

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error locking cache pages",
				'data'=>$cache_pages,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}
				
		
		// Drop the tokens from the database
		// ==========================================================			

		$args = array(
				array("col"=>"id", "op"=>"=", "val"=>$ids)
		);
		
		try{
			$rows_changed = $db->runDeleteQuery($struct, $args);		
		}
		catch(FOX_exception $child){
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error deleting from database",
				'data'=>array("args"=>$args),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}
		
		
		// Flush the item's cache pages
		// ===============================		

		try { 
			self::flushCachePage($cache_pages); 
		}
		catch(FOX_exception $child) { 

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Error flushing cache pages",
				'data'=>$cache_pages,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}		
		
		// Update the class cache
		// ===============================
		
		foreach($id_data as $token_id => $token_name){
		    		   
			unset($this->cache["tokens"][$token_name]);
			unset($this->cache["ids"][$token_id]);					    
		}
		unset($token_id, $token_name);	
				
	
		return (int)$rows_changed;				

	}	


	/**
	 * Deletes ALL DATA and empties the cache. Generally used for testing and debug.
	 * 
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function dropAll() {
	    
	    
		$db = new FOX_db();
		$struct = $this->_struct();

		try{
			self::flushCache();
		}
		catch(FOX_exception $child){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error flushing cache",
				'data'=>null,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));	    
		}
						
		try{
			$result = $db->runTruncateTable($struct);
		}
		catch(FOX_exception $child){
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error truncating table",
				'data'=>null,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));	    
		}	

		return $result;	
		
	}
	

	
} // End of abstract class FOX_module_dictionary_base

?>