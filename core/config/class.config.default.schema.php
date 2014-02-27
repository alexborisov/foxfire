<?php

/**
 * FOXFIRE DEFAULT CONFIGURATION CLASS
 * Manages creating default settings and the initial schema during plugin installation
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Config
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_config_defaultSchema {
    

	var $token_keys_class;		    // Token key class instance. Stores the id's and names of token keys.
	var $group_keys_class;		    // Group key class instance. Stores the id's and names of group keys.
	var $system_keys_class;		    // System key class instance. Stores the id's and names of system keys.
	
	var $group_keyring_class;		
	var $group_members_class;		   
	var $album_types_class;	
	var $album_type_levels_class;		
	
	
	// ================================================================================================================
	
	
	public function __construct($args=null) {

	    
		// Handle dependency-injection for unit tests
		if($args){
			
			$this->token_keys_class = &$args['token_keys_class'];
			$this->system_keys_class = &$args['system_keys_class'];			
			$this->group_keys_class = &$args['group_keys_class'];

			$this->group_keyring_class = &$args['group_keyring_class'];			
			$this->group_members_class = &$args['group_members_class'];
			
			$this->album_types_class = &$args['album_types_class'];		
			$this->album_type_levels_class = &$args['album_type_levels_class'];
			
		}
		else {
			
			$this->token_keys_class = new FOX_uKeyType();
			$this->system_keys_class = new FOX_sysKey();
			$this->group_keys_class = new FOX_uGroupType();
			
			$this->group_keyring_class = new FOX_uGroupKeyRing();			
			$this->group_members_class = new FOX_uGroupMember();			

			$this->album_types_class = new FOX_albumTypeData();
			$this->album_type_levels_class = new FOX_albumTypeLevel();			
		}

	}


	public function createGroupTypes() {
	    
	    
		$cls =& $this->group_keys_class;
		
		try {
			$cls->truncate();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error while clearing group types table",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		
		
		try {
			$cls->flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while flushing group types cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		
		
		
		try {
		    
			$cls->createGroup( array(	
				'name'=>'free',
				'title'=>'Free Users',
				'caption'=>'The default group for all users on the system',
				'is_default'=>true,
				'icon'=>null
			));

			$cls->createGroup( array(	
				'name'=>'subscribers',
				'title'=>'Site Subscribers',
				'caption'=>'Users that are subscribers on the system',
				'is_default'=>false,
				'icon'=>null
			));

			$cls->createGroup( array(	
				'name'=>'paid',
				'title'=>'Paid Users',
				'caption'=>'Users that pay to access the system',
				'is_default'=>false,
				'icon'=>null
			));

			$cls->createGroup( array(	
				'name'=>'premium',
				'title'=>'Premium Users',
				'caption'=>'Users that pay for advanced access to access the system',
				'is_default'=>false,
				'icon'=>null
			));

			$cls->createGroup( array(	
				'name'=>'forum_ops',
				'title'=>'Forum Operators',
				'caption'=>'Users that run forums on the system',
				'is_default'=>false,
				'icon'=>null
			));

			$cls->createGroup( array(	
				'name'=>'admin',
				'title'=>'Admin Group',
				'caption'=>'Admin users group',
				'is_default'=>false,
				'icon'=>null
			));
			
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error while adding group type",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}
		
		return true;
		
	}
	
	
	public function createKeyTypes(){
	    
	    
		$cls =& $this->token_keys_class;
		
		try {
			$cls->truncate();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error while clearing token key types table",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		
		
		try {
			$cls->flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while flushing token key types cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

		
		try {
		    
			$data = array(			    
				    array(  'tree'=>'usr',	'branch'=>'groups', 'name'=>'free',	    'descr'=>'Group Key'),
				    array(	'tree'=>'usr',	'branch'=>'groups', 'name'=>'subscriber',   'descr'=>'Group Key'),
				    array(	'tree'=>'usr',	'branch'=>'groups', 'name'=>'paid',	    'descr'=>'Group Key'),
				    array(	'tree'=>'usr',	'branch'=>'groups', 'name'=>'premium',	    'descr'=>'Group Key'),
				    array(	'tree'=>'usr',	'branch'=>'groups', 'name'=>'forum_op',	    'descr'=>'Group Key'),
				    array(	'tree'=>'usr',	'branch'=>'groups', 'name'=>'admin',	    'descr'=>'Group Key')
			);
			
			$cls->createKeyMulti($data);
			
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error while creating new token key type",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}
		
		return true;
		
	}
	
	
	public function setGroupKeys(){
	    
	    
		$cls =& $this->group_keyring_class;
	    
		try {
			$cls->truncate();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error while clearing group keyrings table",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		
		
		try {
			$cls->flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while flushing group keyrings cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

		
		try {
			$cls->addKey(1, array(1));
			$cls->addKey(2, array(2));
			$cls->addKey(3, array(3));
			$cls->addKey(4, array(4));	
			$cls->addKey(5, array(1, 2, 3, 4, 5));	    // Forum ops	
			$cls->addKey(6, array(1, 2, 3, 4, 5, 6));   // Site Admins
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error while adding group keyring",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}
		
		return true;						
		
	}
	
	
	public function addUsersToGroups(){
	    
	    
		$cls =& $this->group_members_class;

		try {
			$cls->truncate();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error while clearing group members table",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		
		
		try {
			$cls->flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while flushing group members cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		

		
		try {
			$cls->addToGroup(1, 6);    // Add admin to admin group	
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error while adding members to groups",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}				    
		
		return true;
	    
	}
	
	
	public function createAlbumTypes(){
	    
	    
		$cls =& $this->album_types_class;
		
		try {
			$cls->truncate();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error while clearing album types table",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		
		
		try {
			$cls->flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while flushing album types cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}

		try {
			$cls->addType( array(  
				"module_id"=> 1,
				"type_slug"=> "photoAlbum",
				"name_admin"=> "Photo Album",
				"txt_admin"=> "Photo album imported from BP-Album+",
				"action_txt"=> "Upload Photos",
				"name_user"=> "Photo Album",
				"txt_user"=> "Photo album imported from BP-Album+"
			));

			$cls->addType( array(  
				"module_id"=> 2,
				"type_slug"=> "imageAlbum",
				"name_admin"=> "Image Album",
				"txt_admin"=> "FoxFire Image Album",
				"action_txt"=> "Upload Photos",
				"name_user"=> "Image Album",
				"txt_user"=> "FoxFire Image Album"
			));


			$cls->addType( array(  
				"module_id"=> 3,
				"type_slug"=> "audioAlbum",
				"name_admin"=> "Audio Album",
				"txt_admin"=> "FoxFire Audio Album",
				"action_txt"=> "Upload Audio Tracks",
				"name_user"=> "Audio Album",
				"txt_user"=> "FoxFire Image Album"
			));

			$cls->addType( array(  
				"module_id"=> 4,
				"type_slug"=> "videoAlbum",
				"name_admin"=> "Video Album",
				"txt_admin"=> "FoxFire Video Album",
				"action_txt"=> "Upload Videos",
				"name_user"=> "Video Album",
				"txt_user"=> "FoxFire Video Album"
			));

			$cls->addType( array(  
				"module_id"=> 5,
				"type_slug"=> "imageAlbum",
				"name_admin"=> "Image Album",
				"txt_admin"=> "Portfolio Image Album",
				"action_txt"=> "Upload Images",
				"name_user"=> "Image Album",
				"txt_user"=> "Portfolio Image Album"
			));

			$cls->addType( array(  
				"module_id"=> 5,
				"type_slug"=> "audioAlbum",
				"name_admin"=> "Audio Album",
				"txt_admin"=> "Portfolio Audio Album",
				"action_txt"=> "Upload Audio Tracks",
				"name_user"=> "Audio Album",
				"txt_user"=> "Portfolio Audio Album"
			));

			$cls->addType( array(  
				"module_id"=> 5,
				"type_slug"=> "videoAlbum",
				"name_admin"=> "Video Album",
				"txt_admin"=> "Portfolio Video Album",
				"action_txt"=> "Upload Videos",
				"name_user"=> "Video Album",
				"txt_user"=> "Portfolio Video Album"
			));	
			
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error while adding album type",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}
		
		return true;
	    
	}
	
	
	public function createAlbumTypeLevels(){
	    
	    
		$cls =& $this->album_type_levels_class;

		try {
			$cls->truncate();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error while clearing album type levels table",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		
		
		try {
			$cls->flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while flushing album type levels cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}		

		
		try {
		    
			// FOX-Legacy
			// =============================================================================

			$cls->addLevel( array(	
				"module_id"=>1, "type_id"=>1, "level_slug"=> "free", "level_name"=>"Free User",
				"level_desc"=>"All users get access", "rank"=>6, "key_id"=>1
			));

			$cls->addLevel( array(	
				"module_id"=>1, "type_id"=>1, "level_slug"=> "subscriber", "level_name"=>"Subscribers",
				"level_desc"=>"Site Subscribers", "rank"=>5, "key_id"=>2
			));

			$cls->addLevel( array(	
				"module_id"=>1, "type_id"=>1, "level_slug"=> "paid", "level_name"=>"Paid Users",
				"level_desc"=>"Paid users get access", "rank"=>4, "key_id"=>3
			));

			$cls->addLevel( array(	
				"module_id"=>1, "type_id"=>1, "level_slug"=> "premium", "level_name"=>"Premium Users",
				"level_desc"=>"Premium users get access", "rank"=>3, "key_id"=>4
			));

			$cls->addLevel( array(	
				"module_id"=>1, "type_id"=>1, "level_slug"=> "forum_op", "level_name"=>"Forum Operators",
				"level_desc"=>"Forum operators get access", "rank"=>2, "key_id"=>5
			));

			$cls->addLevel( array(	
				"module_id"=>1, "type_id"=>1, "level_slug"=> "admin", "level_name"=>"Administrators",
				"level_desc"=>"Site Admins get access", "rank"=>1, "key_id"=>6
			));


			// FOX-Image
			// =============================================================================

			$cls->addLevel( array(	
				"module_id"=>2, "type_id"=>2, "level_slug"=> "free", "level_name"=>"Free User",
				"level_desc"=>"All users get access", "rank"=>6, "key_id"=>1
			));

			$cls->addLevel( array(	
				"module_id"=>2, "type_id"=>2, "level_slug"=> "subscriber", "level_name"=>"Subscribers",
				"level_desc"=>"Site Subscribers", "rank"=>5, "key_id"=>2
			));

			$cls->addLevel( array(	
				"module_id"=>2, "type_id"=>2, "level_slug"=> "paid", "level_name"=>"Paid Users",
				"level_desc"=>"Paid users get access", "rank"=>4, "key_id"=>3
			));

			$cls->addLevel( array(	
				"module_id"=>2, "type_id"=>2, "level_slug"=> "premium", "level_name"=>"Premium Users",
				"level_desc"=>"Premium users get access", "rank"=>3, "key_id"=>4
			));

			$cls->addLevel( array(	
				"module_id"=>2, "type_id"=>2, "level_slug"=> "forum_op", "level_name"=>"Forum Operators",
				"level_desc"=>"Forum operators get access", "rank"=>2, "key_id"=>5
			));

			$cls->addLevel( array(	
				"module_id"=>2, "type_id"=>2, "level_slug"=> "admin", "level_name"=>"Administrators",
				"level_desc"=>"Site Admins get access", "rank"=>1, "key_id"=>6
			));


			// FOX-Audio
			// =============================================================================

			$cls->addLevel( array(	
				"module_id"=>3, "type_id"=>3, "level_slug"=> "free", "level_name"=>"Free User",
				"level_desc"=>"All users get access", "rank"=>6, "key_id"=>1
			));

			$cls->addLevel( array(	
				"module_id"=>3, "type_id"=>3, "level_slug"=> "subscriber", "level_name"=>"Subscribers",
				"level_desc"=>"Site Subscribers", "rank"=>5, "key_id"=>2
			));

			$cls->addLevel( array(	
				"module_id"=>3, "type_id"=>3, "level_slug"=> "paid", "level_name"=>"Paid Users",
				"level_desc"=>"Paid users get access", "rank"=>4, "key_id"=>3
			));

			$cls->addLevel( array(	
				"module_id"=>3, "type_id"=>3, "level_slug"=> "premium", "level_name"=>"Premium Users",
				"level_desc"=>"Premium users get access", "rank"=>3, "key_id"=>4
			));

			$cls->addLevel( array(	
				"module_id"=>3, "type_id"=>3, "level_slug"=> "forum_op", "level_name"=>"Forum Operators",
				"level_desc"=>"Forum operators get access", "rank"=>2, "key_id"=>5
			));

			$cls->addLevel( array(	
				"module_id"=>3, "type_id"=>3, "level_slug"=> "admin", "level_name"=>"Administrators",
				"level_desc"=>"Site Admins get access", "rank"=>1, "key_id"=>6
			));


			// FOX-Video
			// =============================================================================

			$cls->addLevel( array(	
				"module_id"=>4, "type_id"=>4, "level_slug"=> "free", "level_name"=>"Free User",
				"level_desc"=>"All users get access", "rank"=>6, "key_id"=>1
			));

			$cls->addLevel( array(	
				"module_id"=>4, "type_id"=>4, "level_slug"=> "subscriber", "level_name"=>"Subscribers",
				"level_desc"=>"Site Subscribers", "rank"=>5, "key_id"=>2
			));

			$cls->addLevel( array(	
				"module_id"=>4, "type_id"=>4, "level_slug"=> "paid", "level_name"=>"Paid Users",
				"level_desc"=>"Paid users get access", "rank"=>4, "key_id"=>3
			));

			$cls->addLevel( array(	
				"module_id"=>4, "type_id"=>4, "level_slug"=> "premium", "level_name"=>"Premium Users",
				"level_desc"=>"Premium users get access", "rank"=>3, "key_id"=>4
			));

			$cls->addLevel( array(	
				"module_id"=>4, "type_id"=>4, "level_slug"=> "forum_op", "level_name"=>"Forum Operators",
				"level_desc"=>"Forum operators get access", "rank"=>2, "key_id"=>5
			));

			$cls->addLevel( array(	
				"module_id"=>4, "type_id"=>4, "level_slug"=> "admin", "level_name"=>"Administrators",
				"level_desc"=>"Site Admins get access", "rank"=>1, "key_id"=>6
			));


			// FOX-Portfolio
			// =============================================================================

			// Images --------------------------------------

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>5, "level_slug"=> "free", "level_name"=>"Free User",
				"level_desc"=>"All users get access", "rank"=>6, "key_id"=>1
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>5, "level_slug"=> "subscriber", "level_name"=>"Subscribers",
				"level_desc"=>"Site Subscribers", "rank"=>5, "key_id"=>2
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>5, "level_slug"=> "paid", "level_name"=>"Paid Users",
				"level_desc"=>"Paid users get access", "rank"=>4, "key_id"=>3
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>5, "level_slug"=> "premium", "level_name"=>"Premium Users",
				"level_desc"=>"Premium users get access", "rank"=>3, "key_id"=>4
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>5, "level_slug"=> "forum_op", "level_name"=>"Forum Operators",
				"level_desc"=>"Forum operators get access", "rank"=>2, "key_id"=>5
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>5, "level_slug"=> "admin", "level_name"=>"Administrators",
				"level_desc"=>"Site Admins get access", "rank"=>1, "key_id"=>6
			));

			// Audio --------------------------------------

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>6, "level_slug"=> "free", "level_name"=>"Free User",
				"level_desc"=>"All users get access", "rank"=>6, "key_id"=>1
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>6, "level_slug"=> "subscriber", "level_name"=>"Subscribers",
				"level_desc"=>"Site Subscribers", "rank"=>5, "key_id"=>2
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>6, "level_slug"=> "paid", "level_name"=>"Paid Users",
				"level_desc"=>"Paid users get access", "rank"=>4, "key_id"=>3
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>6, "level_slug"=> "premium", "level_name"=>"Premium Users",
				"level_desc"=>"Premium users get access", "rank"=>3, "key_id"=>4
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>6, "level_slug"=> "forum_op", "level_name"=>"Forum Operators",
				"level_desc"=>"Forum operators get access", "rank"=>2, "key_id"=>5
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>6, "level_slug"=> "admin", "level_name"=>"Administrators",
				"level_desc"=>"Site Admins get access", "rank"=>1, "key_id"=>6
			));

			// Images --------------------------------------

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>7, "level_slug"=> "free", "level_name"=>"Free User",
				"level_desc"=>"All users get access", "rank"=>6, "key_id"=>1
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>7, "level_slug"=> "subscriber", "level_name"=>"Subscribers",
				"level_desc"=>"Site Subscribers", "rank"=>5, "key_id"=>2
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>7, "level_slug"=> "paid", "level_name"=>"Paid Users",
				"level_desc"=>"Paid users get access", "rank"=>4, "key_id"=>3
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>7, "level_slug"=> "premium", "level_name"=>"Premium Users",
				"level_desc"=>"Premium users get access", "rank"=>3, "key_id"=>4
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>7, "level_slug"=> "forum_op", "level_name"=>"Forum Operators",
				"level_desc"=>"Forum operators get access", "rank"=>2, "key_id"=>5
			));

			$cls->addLevel( array(	
				"module_id"=>5, "type_id"=>7, "level_slug"=> "admin", "level_name"=>"Administrators",
				"level_desc"=>"Site Admins get access", "rank"=>1, "key_id"=>6
			));

		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error while adding album type level",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}					

		return true;	    
	    
	}
	
	
	
	public function load() {
	    	
		
		try {
			self::createGroupTypes();
			self::createKeyTypes();
			self::setGroupKeys();
			self::addUsersToGroups();
			self::createAlbumTypes();
			self::createAlbumTypeLevels();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in schema loader",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}
		
		return true;

	}

	
	
} // End of class FOX_config_defaultSchema


?>