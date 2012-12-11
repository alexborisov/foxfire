<?php

/**
 * FOXFIRE DEFAULT CONFIG CLASS - KEYS
 * Manages creating configuration keys during initial plugin setup, and during a plugin reset
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

class FOX_config_defaultKeys {
    
    
	var $config_class;		    // System config class
	
	var $page_modules_class;	    // Page modules class instance	
	var $album_modules_class;	    // Album modules class instance
	var $media_modules_class;	    // Media modules class instance
	var $network_modules_class;	    // Network modules class instance	
	
	
	// ================================================================================================================
	
	
	public function __construct($args=null) {

	    
		// Handle dependency-injection for unit tests
		if($args){

			$this->config_class = &$args['config_class'];			
			$this->page_modules_class = &$args['page_modules_class'];
			$this->album_modules_class = &$args['album_modules_class'];
			$this->media_modules_class = &$args['media_modules_class'];
			$this->network_modules_class = &$args['network_modules_class'];
		}
		else {
			global $fox;			
			$this->config_class = &$fox->config;			
			$this->page_modules_class = &$fox->pageModules;
			$this->album_modules_class = &$fox->albumModules;
			$this->media_modules_class = &$fox->mediaModules;
			$this->network_modules_class = &$fox->networkModules;
		}

	}
	
	
	/**
         * Core Settings
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_coreSettings() {
	    
	    
		$keys = array(

		array('tree'=>'system',		'branch'=>'core',	'key'=>'installed',	    'val'=>true,	    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'system',		'branch'=>'core',	'key'=>'version',	    'val'=>1900,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'system',		'branch'=>'core',	'key'=>'releaseDate',	    'val'=>'2011-09-21',    'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'system',		'branch'=>'core',	'key'=>'buildName',	    'val'=>'Tesla',	    'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'integration',	'branch'=>'buddypress', 'key'=>'activityStreamID',  'val'=>'fox',	    'filter'=>'textAndNumbers', 'ctrl'=>null)	

		);	    
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
			    	    	    
	}
	
	
	/**
         * Album Modules
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_albumModules() {
	    

		$keys = array(

		array('tree'=>'albums', 'branch'=>'module', 'key'=>'adminDisplayPerPage', 'val'=>10, 'filter'=>'int', 'ctrl'=>null)

		);	    
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
			    	    
	}

	
	/**
         * Plugin Setup -> Display
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_pluginSetup_display() {
	    
    	    
		$keys = array(

		array('tree'=>'albums', 'branch'=>'albumsList', 'key'=>'displayGridX',		    'val'=>6,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'albums', 'branch'=>'albumsList', 'key'=>'displayGridY',		    'val'=>4,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'albums', 'branch'=>'nav',	'key'=>'maxBreadcrumbTitleLength',  'val'=>50,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'albums', 'branch'=>'ajax',	'key'=>'enablePagination',	    'val'=>true,    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'albums', 'branch'=>'user',	'key'=>'enableSorting',		    'val'=>false,   'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'albums', 'branch'=>'user',	'key'=>'defaultSortMode',	    'val'=>'date',  'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'albums', 'branch'=>'user',	'key'=>'defaultSortDirection',	    'val'=>'ASC',   'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'media',	'branch'=>'mediasList', 'key'=>'displayGridX',		    'val'=>6,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'media',	'branch'=>'mediasList', 'key'=>'displayGridY',		    'val'=>4,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'media',	'branch'=>'nav',	'key'=>'maxBreadcrumbTitleLength',  'val'=>50,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'media',	'branch'=>'ajax',	'key'=>'enablePagination',	    'val'=>true,    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'media',	'branch'=>'user',	'key'=>'enableSorting',		    'val'=>false,   'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'media',	'branch'=>'user',	'key'=>'defaultSortMode',	    'val'=>'date',  'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'media',	'branch'=>'user',	'key'=>'defaultSortDirection',	    'val'=>'ASC',   'filter'=>'textAndNumbers', 'ctrl'=>null)		    

		);	    
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
		
	}	
	
	
	/**
         * Plugin Setup -> Page Slugs
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_pluginSetup_pageSlugs() {
	    

		$keys = array(

		array('tree'=>'slugs',	    'branch'=>'base',	'key'=>'base',		'val'=>'media',		    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'base',	'key'=>'home',		'val'=>'media-home',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'albums', 'key'=>'home',		'val'=>'albums',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'albums', 'key'=>'single',	'val'=>'album',		    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'albums', 'key'=>'create',	'val'=>'create-album',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'albums', 'key'=>'edit',		'val'=>'edit-album',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'albums', 'key'=>'items',		'val'=>'edit-items',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'albums', 'key'=>'sort',		'val'=>'sort-items',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'albums', 'key'=>'delete',	'val'=>'delete-album',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'albums', 'key'=>'me',		'val'=>'albums-me',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'media',	'key'=>'home',		'val'=>'items',		    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'media',	'key'=>'single',	'val'=>'item',		    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'media',	'key'=>'create',	'val'=>'add-item',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'media',	'key'=>'edit',		'val'=>'edit-item',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'media',	'key'=>'delete',	'val'=>'delete-item',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'media',	'key'=>'tag',		'val'=>'media-tag',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'media',	'key'=>'me',		'val'=>'media-me',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'slugs',	    'branch'=>'media',	'key'=>'firstAction',	'val'=>'firstAction',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'member',	    'branch'=>'tags',	'key'=>'tagSelf',	'val'=>'tag-self',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'member',	    'branch'=>'tags',	'key'=>'unTagSelf',	'val'=>'untag-self',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'member',	    'branch'=>'tags',	'key'=>'tagMember',	'val'=>'tag-member',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'member',	    'branch'=>'tags',	'key'=>'unTagMember',	'val'=>'untag-member',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'member',	    'branch'=>'tags',	'key'=>'mediaOfMe',	'val'=>'media-of-me',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'member',	    'branch'=>'tags',	'key'=>'removeFavorite','val'=>'remove-favorite',   'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'keyword',    'branch'=>'tags',	'key'=>'showTag',	'val'=>'keyword-show',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'keyword',    'branch'=>'tags',	'key'=>'tagsByMember',	'val'=>'keyword-tags-by-member', 'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'keyword',    'branch'=>'tags',	'key'=>'addTag',	'val'=>'keyword-add',	    'filter'=>'slug', 'ctrl'=>null),
		array('tree'=>'keyword',    'branch'=>'tags',	'key'=>'removeTag',	'val'=>'keyword-remove',    'filter'=>'slug', 'ctrl'=>null)		    		    		    
		    
		);	
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
		
	}
	
	
	/**
         * Plugin Setup -> Adding Media
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_pluginSetup_addingMedia() {
	    	   

		$keys = array(

		array('tree'=>'file', 'branch'=>'uploader',	'key'=>'enableHTTP',		    'val'=>true,	'filter'=>'bool',   'ctrl'=>null),
		array('tree'=>'file', 'branch'=>'uploader',	'key'=>'singleMaxSize',		    'val'=>'10485760',	'filter'=>'float',  'ctrl'=>null),
		array('tree'=>'file', 'branch'=>'uploader',	'key'=>'singleMaxStreams',	    'val'=>4,		'filter'=>'int',    'ctrl'=>null),
		array('tree'=>'file', 'branch'=>'uploader',	'key'=>'enableMulti',		    'val'=>true,	'filter'=>'bool',   'ctrl'=>null),
		array('tree'=>'file', 'branch'=>'uploader',	'key'=>'multiMaxSize',		    'val'=>'10485760',	'filter'=>'float',  'ctrl'=>null),
		array('tree'=>'file', 'branch'=>'uploader',	'key'=>'multiMaxStreams',	    'val'=>1,		'filter'=>'int',    'ctrl'=>null),
		array('tree'=>'file', 'branch'=>'downloader',	'key'=>'maxSize',		    'val'=>'10485760',	'filter'=>'float',  'ctrl'=>null),
		array('tree'=>'file', 'branch'=>'downloader',	'key'=>'maxTime',		    'val'=>600,		'filter'=>'int',    'ctrl'=>null),
		array('tree'=>'file', 'branch'=>'downloader',	'key'=>'requireLengthHeader',	    'val'=>true,	'filter'=>'bool',   'ctrl'=>null),
		array('tree'=>'file', 'branch'=>'downloader',	'key'=>'requireStrictValidation',   'val'=>true,	'filter'=>'bool',   'ctrl'=>null),
		array('tree'=>'file', 'branch'=>'downloader',	'key'=>'validateContentType',	    'val'=>true,	'filter'=>'bool',   'ctrl'=>null)		    
		    
		);
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}		
		
	}	
	
	
	/**
         * Social Settings -> Keyword Tags
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_socialSettings_keywordTags() {
	    
		
		$keys = array(

		array('tree'=>'keyword',    'branch'=>'core',	    'key'=>'enable',		    'val'=>true,    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'keyword',    'branch'=>'template',   'key'=>'maxDisplayTagsPerItem', 'val'=>30,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'keyword',    'branch'=>'abuse',	    'key'=>'maxTagsPerItem',	    'val'=>50,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'keyword',    'branch'=>'add',	    'key'=>'friendPolicy',	    'val'=>'all',   'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'keyword',    'branch'=>'accept',	    'key'=>'friendPolicy',	    'val'=>'all',   'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'keyword',    'branch'=>'remove',	    'key'=>'friendPolicy',	    'val'=>'all',   'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'keyword',    'branch'=>'abuse',	    'key'=>'maxDailyTagAdds',	    'val'=>250,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'keyword',    'branch'=>'abuse',	    'key'=>'maxDailyTagDrops',	    'val'=>250,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'keyword',    'branch'=>'abuse',	    'key'=>'notifyOnTrip',	    'val'=>true,    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'search',	    'branch'=>'keyword',    'key'=>'enableSiteWide',	    'val'=>true,    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'search',	    'branch'=>'keyword',    'key'=>'enableByUser',	    'val'=>true,    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'search',	    'branch'=>'keyword',    'key'=>'addToSiteSearch',	    'val'=>true,    'filter'=>'bool',		'ctrl'=>null)		    
		    
		);
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
		
	}	
	
	
	/**
         * Social Settings -> Member Tags
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_socialSettings_memberTags() {
	    
	    
		$keys = array(

		array('tree'=>'member', 'branch'=>'core',	    'key'=>'enable',		    'val'=>true,    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'member', 'branch'=>'abuse',	    'key'=>'maxTagsPerItem',	    'val'=>50,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'member', 'branch'=>'ajax',	    'key'=>'enableAutoComplete',    'val'=>false,   'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'member', 'branch'=>'inboundTag',	    'key'=>'friendPolicy',	    'val'=>'all',   'filter'=>'textAndNumbers',	'ctrl'=>null),
		array('tree'=>'member', 'branch'=>'inboundAccept',  'key'=>'friendPolicy',	    'val'=>'all',   'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'member', 'branch'=>'outboundTag',    'key'=>'friendPolicy',	    'val'=>'all',   'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'member', 'branch'=>'outboundAccept', 'key'=>'friendPolicy',	    'val'=>'all',   'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'member', 'branch'=>'crossTag',	    'key'=>'enable',		    'val'=>false,   'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'member', 'branch'=>'crossTag',	    'key'=>'itemPolicy',	    'val'=>'all',   'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'member', 'branch'=>'crossTag',	    'key'=>'memberPolicy',	    'val'=>'all',   'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'member', 'branch'=>'crossTag',	    'key'=>'friendPolicy',	    'val'=>'all',   'filter'=>'textAndNumbers', 'ctrl'=>null)		    
		    
		);
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
		
	}	
	
	
	/**
         * Social Settings -> Activity Stream
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_socialSettings_activityStream() {
	    	   

		$keys = array(

		array('tree'=>'activity', 'branch'=>'comment',	    'key'=>'attachmentEnable',	'val'=>true,	    'filter'=>'bool',		'ctrl'=>null),	
		array('tree'=>'activity', 'branch'=>'comment',	    'key'=>'allowImages',	'val'=>true,	    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'activity', 'branch'=>'comment',	    'key'=>'allowAudio',	'val'=>true,	    'filter'=>'bool',		'ctrl'=>null),	
		array('tree'=>'activity', 'branch'=>'comment',	    'key'=>'allowVideo',	'val'=>true,	    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'activity', 'branch'=>'comment',	    'key'=>'allowEmbedded',	'val'=>true,	    'filter'=>'bool',		'ctrl'=>null),	
		array('tree'=>'activity', 'branch'=>'userStream',   'key'=>'postImages',	'val'=>true,	    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'activity', 'branch'=>'globalStream', 'key'=>'postImages',	'val'=>true,	    'filter'=>'bool',		'ctrl'=>null),	
		array('tree'=>'activity', 'branch'=>'postFolding',  'key'=>'windowSize',	'val'=>24,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'activity', 'branch'=>'postFolding',  'key'=>'maxPostsPerWindow', 'val'=>10,	    'filter'=>'int',		'ctrl'=>null),	
		array('tree'=>'activity', 'branch'=>'postFolding',  'key'=>'maxItemsPerPost',	'val'=>5,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'activity', 'branch'=>'comment',	    'key'=>'enableComments',	'val'=>true,	    'filter'=>'bool',		'ctrl'=>null),	
		array('tree'=>'activity', 'branch'=>'comment',	    'key'=>'enableGlobal',	'val'=>true,	    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'activity', 'branch'=>'comment',	    'key'=>'disablePolicy',	'val'=>'delete',    'filter'=>'textAndNumbers',	'ctrl'=>null),	
		array('tree'=>'activity', 'branch'=>'comment',	    'key'=>'friendPolicy',	'val'=>'all',	    'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'activity', 'branch'=>'comment',	    'key'=>'URLPolicy',		'val'=>'sanitize',  'filter'=>'textAndNumbers', 'ctrl'=>null),	
		array('tree'=>'activity', 'branch'=>'foxLike',	    'key'=>'enable',		'val'=>true,	    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'activity', 'branch'=>'foxLike',	    'key'=>'friendPolicy',	'val'=>'all',	    'filter'=>'textAndNumbers', 'ctrl'=>null),	
		array('tree'=>'activity', 'branch'=>'foxLike',	    'key'=>'showOnPosts',	'val'=>true,	    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'activity', 'branch'=>'foxLike',	    'key'=>'displayMaxTags',	'val'=>5,	    'filter'=>'int',		'ctrl'=>null)		    
		    
		);
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}		
		
	}
	
	
	/**
         * Social Settings -> Notifications
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_socialSettings_notifications() {
	    
			
		$keys = array(

		array('tree'=>'notifications', 'branch'=>'core',			'key'=>'enable',	    'val'=>true,    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'notifications', 'branch'=>'memberTagAutoApproved',	'key'=>'messageType',	    'val'=>'email', 'filter'=>'textAndNumbers', 'ctrl'=>null),
		
		array('tree'=>'notifications', 'branch'=>'memberTagAutoApproved',	'key'=>'messageBody',
		      'val'=>'Message Text Goes Here',
		      'filter'=>'textAndNumbers', 'ctrl'=>null),
		
		array('tree'=>'notifications', 'branch'=>'memberTagRequiresApproval',	'key'=>'messageType',	    'val'=>'email', 'filter'=>'textAndNumbers', 'ctrl'=>null),
		
		array('tree'=>'notifications', 'branch'=>'memberTagRequiresApproval',	'key'=>'messageBody',
		      'val'=>'Message Text Goes Here',
		      'filter'=>'textAndNumbers', 'ctrl'=>null),
		
		array('tree'=>'notifications', 'branch'=>'ownerAcceptedTagRequest',	'key'=>'messageType',	    'val'=>'email', 'filter'=>'textAndNumbers', 'ctrl'=>null),
		
		array('tree'=>'notifications', 'branch'=>'ownerAcceptedTagRequest',	'key'=>'messageBody',
		      'val'=>'Message Text Goes Here',
		      'filter'=>'textAndNumbers', 'ctrl'=>null),
		
		array('tree'=>'notifications', 'branch'=>'memberAcceptedTagRequest',	'key'=>'messageType',	    'val'=>'email', 'filter'=>'textAndNumbers', 'ctrl'=>null),
		
		array('tree'=>'notifications', 'branch'=>'memberAcceptedTagRequest',	'key'=>'messageBody',
		      'val'=>'Message Text Goes Here',
		      'filter'=>'textAndNumbers', 'ctrl'=>null),
		
		array('tree'=>'notifications', 'branch'=>'memberPostedComment',		'key'=>'messageType',	    'val'=>'email', 'filter'=>'textAndNumbers', 'ctrl'=>null),
		
		array('tree'=>'notifications', 'branch'=>'memberPostedComment',		'key'=>'messageBody',
		      'val'=>'Message Text Goes Here',
		      'filter'=>'textAndNumbers', 'ctrl'=>null),
		
		array('tree'=>'notifications', 'branch'=>'ownerRespondedToComment',	'key'=>'messageType',	    'val'=>'email', 'filter'=>'textAndNumbers', 'ctrl'=>null),
		
		array('tree'=>'notifications', 'branch'=>'ownerRespondedToComment',	'key'=>'messageBody',
		      'val'=>'Message Text Goes Here',
		      'filter'=>'textAndNumbers', 'ctrl'=>null)		    		    		    
		);
		
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}		
		
	}
	
	
	/**
         * Social Settings -> Media Rating
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_socialSettings_mediaRating() {
	    

		$keys = array(

		array('tree'=>"stats", 'branch'=>"viewCounts", 'key'=>"enable",		'val'=>true,	    'filter'=>"bool",		'ctrl'=>null),
		array('tree'=>"stats", 'branch'=>"viewCounts", 'key'=>"trackingMethod", 'val'=>"cookie",    'filter'=>"textAndNumbers", 'ctrl'=>null),
		array('tree'=>"stats", 'branch'=>"viewCounts", 'key'=>"showCountsTo",	'val'=>"all",	    'filter'=>"textAndNumbers", 'ctrl'=>null),
		array('tree'=>"stats", 'branch'=>"itemRating", 'key'=>"enable",		'val'=>true,	    'filter'=>"bool",		'ctrl'=>null),
		array('tree'=>"stats", 'branch'=>"itemRating", 'key'=>"friendPolicy",	'val'=>"all",	    'filter'=>"textAndNumbers", 'ctrl'=>null),
		array('tree'=>"stats", 'branch'=>"itemRating", 'key'=>"showCountsTo",	'val'=>"all",	    'filter'=>"textAndNumbers", 'ctrl'=>null),		    
		    
		);
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
		
	}
	
	
	/**
         * Widgets -> Member Uploads
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_widgets_memberUploads() {
	    

		$keys = array(

		array('tree'=>'featured', 'branch'=>'memberLatestUploads', 'key'=>'enable',		    'val'=>true,    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'featured', 'branch'=>'memberLatestUploads', 'key'=>'maxItems',		    'val'=>64,	    'filter'=>'int',		'ctrl'=>null),	
		array('tree'=>'featured', 'branch'=>'memberLatestUploads', 'key'=>'thumbPixelsX',	    'val'=>100,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'featured', 'branch'=>'memberLatestUploads', 'key'=>'thumbPixelsY',	    'val'=>100,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'featured', 'branch'=>'memberLatestUploads', 'key'=>'resizeMethod',	    'val'=>'scale', 'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'featured', 'branch'=>'memberLatestUploads', 'key'=>'allowImages',	    'val'=>true,    'filter'=>'bool',		'ctrl'=>null),	
		array('tree'=>'featured', 'branch'=>'memberLatestUploads', 'key'=>'allowAudio',		    'val'=>false,   'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'featured', 'branch'=>'memberLatestUploads', 'key'=>'allowVideo',		    'val'=>false,   'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'featured', 'branch'=>'memberLatestUploads', 'key'=>'allowEmbedded',	    'val'=>false,   'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'featured', 'branch'=>'memberLatestUploads', 'key'=>'itemSourceOptOut',	    'val'=>'optout','filter'=>'textAndNumbers', 'ctrl'=>null),	
		array('tree'=>'featured', 'branch'=>'memberLatestUploads', 'key'=>'itemSourceFriendPolicy', 'val'=>'all',   'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'featured', 'branch'=>'memberLatestUploads', 'key'=>'sortMethod',		    'val'=>'date',  'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'featured', 'branch'=>'memberLatestUploads', 'key'=>'sortDirection',	    'val'=>'DESC',  'filter'=>'textAndNumbers', 'ctrl'=>null)		    
		    
		);
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
		
	}
	
	
	/**
         * Widgets -> Sitewide Uploads
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_widgets_sitewideUploads() {
	    

		$keys = array(

		array('tree'=>'featured', 'branch'=>'siteLatestUploads', 'key'=>'enable',	    'val'=>true,    'filter'=>'bool',		'ctrl'=>null), 
		array('tree'=>'featured', 'branch'=>'siteLatestUploads', 'key'=>'maxItems',	    'val'=>64,	    'filter'=>'int',		'ctrl'=>null), 
		array('tree'=>'featured', 'branch'=>'siteLatestUploads', 'key'=>'thumbSize',	    'val'=>'system','filter'=>'textAndNumbers', 'ctrl'=>null), 
		array('tree'=>'featured', 'branch'=>'siteLatestUploads', 'key'=>'thumbPixelsX',	    'val'=>100,	    'filter'=>'int',		'ctrl'=>null), 	
		array('tree'=>'featured', 'branch'=>'siteLatestUploads', 'key'=>'thumbPixelsY',	    'val'=>100,	    'filter'=>'int',		'ctrl'=>null), 
		array('tree'=>'featured', 'branch'=>'siteLatestUploads', 'key'=>'allowImages',	    'val'=>true,    'filter'=>'bool',		'ctrl'=>null), 
		array('tree'=>'featured', 'branch'=>'siteLatestUploads', 'key'=>'allowAudio',	    'val'=>false,   'filter'=>'bool',		'ctrl'=>null), 
		array('tree'=>'featured', 'branch'=>'siteLatestUploads', 'key'=>'allowVideo',	    'val'=>false,   'filter'=>'bool',		'ctrl'=>null), 
		array('tree'=>'featured', 'branch'=>'siteLatestUploads', 'key'=>'allowEmbedded',    'val'=>false,   'filter'=>'bool',		'ctrl'=>null), 
		array('tree'=>'featured', 'branch'=>'siteLatestUploads', 'key'=>'itemSourceOptOut', 'val'=>'optout','filter'=>'textAndNumbers', 'ctrl'=>null), 
		array('tree'=>'featured', 'branch'=>'siteLatestUploads', 'key'=>'sortMethod',	    'val'=>'date',  'filter'=>'textAndNumbers', 'ctrl'=>null), 
		array('tree'=>'featured', 'branch'=>'siteLatestUploads', 'key'=>'sortDirection',    'val'=>'DESC',  'filter'=>'textAndNumbers', 'ctrl'=>null)			    
		    
		);
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}		
				
	}
	
	
	/**
         * Media Cache -> File System
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_mediaCache_fileSystem() {
	    	    

		$keys = array(

		array('tree'=>'cache', 'branch'=>'server',  'key'=>'touchFilesOnHit',	'val'=>true,									'filter'=>'bool',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L0',	    'key'=>'mode',		'val'=>'global',								'filter'=>'textAndNumbers',	'ctrl'=>null), 	
		array('tree'=>'cache', 'branch'=>'L0',	    'key'=>'maxInodes',		'val'=>'1000',									'filter'=>'float',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L0',	    'key'=>'maxMB',		'val'=>'5000',									'filter'=>'float',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L0',	    'key'=>'flushInterval',	'val'=>60,									'filter'=>'int',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L0',	    'key'=>'siteFolder',	'val'=> bp_media_upload_path() . '/fox-cache/L0',				'filter'=>'fileStringLocal',	'ctrl'=>null), 	
		array('tree'=>'cache', 'branch'=>'L0',	    'key'=>'siteURI',		'val'=> bp_media_get_url_from_path( bp_media_upload_path() . '/fox-cache/L0'),	'filter'=>'fileStringLocal',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L0',	    'key'=>'blogFolderOffset',	'val'=>'/fox-cache/L0',								'filter'=>'fileStringLocal',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L0',	    'key'=>'blogURIOffset',	'val'=>'/fox-cache/L0',								'filter'=>'fileStringLocal',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L1',	    'key'=>'mode',		'val'=>'global',								'filter'=>'textAndNumbers',	'ctrl'=>null), 	
		array('tree'=>'cache', 'branch'=>'L1',	    'key'=>'maxInodes',		'val'=>'2500',									'filter'=>'float',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L1',	    'key'=>'maxMB',		'val'=>'5000',									'filter'=>'float',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L1',	    'key'=>'flushInterval',	'val'=>60,									'filter'=>'int',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L1',	    'key'=>'siteFolder',	'val'=> bp_media_upload_path() . '/fox-cache/L1',				'filter'=>'fileStringLocal',	'ctrl'=>null), 	
		array('tree'=>'cache', 'branch'=>'L1',	    'key'=>'siteURI',		'val'=> bp_media_get_url_from_path( bp_media_upload_path() . '/fox-cache/L1'),	'filter'=>'fileStringLocal',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L1',	    'key'=>'blogFolderOffset',	'val'=>'/fox-cache/L1',								'filter'=>'fileStringLocal',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L1',	    'key'=>'blogURIOffset',	'val'=>'/fox-cache/L1',								'filter'=>'fileStringLocal',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L2',	    'key'=>'mode',		'val'=>'global',								'filter'=>'textAndNumbers',	'ctrl'=>null), 	
		array('tree'=>'cache', 'branch'=>'L2',	    'key'=>'maxInodes',		'val'=>'2500',									'filter'=>'float',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L2',	    'key'=>'maxMB',		'val'=>'50000',									'filter'=>'float',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L2',	    'key'=>'flushInterval',	'val'=>60,									'filter'=>'int',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L2',	    'key'=>'siteFolder',	'val'=> bp_media_upload_path() . '/fox-cache/L2',				'filter'=>'fileStringLocal',	'ctrl'=>null), 	
		array('tree'=>'cache', 'branch'=>'L2',	    'key'=>'siteURI',		'val'=> bp_media_get_url_from_path( bp_media_upload_path() . '/fox-cache/L2'),	'filter'=>'fileStringLocal',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L2',	    'key'=>'blogFolderOffset',	'val'=>'/fox-cache/L2',								'filter'=>'fileStringLocal',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L2',	    'key'=>'blogURIOffset',	'val'=>'/fox-cache/L2',								'filter'=>'fileStringLocal',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L3',	    'key'=>'mode',		'val'=>'global',								'filter'=>'textAndNumbers',	'ctrl'=>null), 	
		array('tree'=>'cache', 'branch'=>'L3',	    'key'=>'maxInodes',		'val'=>'2500',									'filter'=>'float',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L3',	    'key'=>'maxMB',		'val'=>'100000',								'filter'=>'float',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L3',	    'key'=>'flushInterval',	'val'=>60,									'filter'=>'int',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L3',	    'key'=>'siteFolder',	'val'=> bp_media_upload_path() . '/fox-cache/L3',				'filter'=>'fileStringLocal',	'ctrl'=>null), 	
		array('tree'=>'cache', 'branch'=>'L3',	    'key'=>'siteURI',		'val'=> bp_media_get_url_from_path( bp_media_upload_path() . '/fox-cache/L3'),	'filter'=>'fileStringLocal',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L3',	    'key'=>'blogFolderOffset',	'val'=>'/fox-cache/L3',								'filter'=>'fileStringLocal',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L3',	    'key'=>'blogURIOffset',	'val'=>'/fox-cache/L3',								'filter'=>'fileStringLocal',	'ctrl'=>null), 
		    			   		    
		);
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
		
	}	
	
	
	/**
         * File Settings -> File Paths
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_fileSettings_filePaths() {
	    	    

		$keys = array(

		array('tree'=>'cache', 'branch'=>'L4', 'key'=>'mode',		    'val'=>'global',	'filter'=>'textAndNumbers',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L4', 'key'=>'mode',		    'val'=>'global',	'filter'=>'textAndNumbers',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L4', 'key'=>'maxInodes',	    'val'=>'0',		'filter'=>'float',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L4', 'key'=>'maxMB',		    'val'=>'0',		'filter'=>'float',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L4', 'key'=>'flushInterval',	    'val'=>0,		'filter'=>'int',		'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L4', 'key'=>'siteFolder',	    'val'=> bp_media_upload_path() . '/foxfire', 'filter'=>'fileStringLocal', 'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L4', 'key'=>'siteURI',	    'val'=> bp_media_get_url_from_path( bp_media_upload_path() . '/foxfire'), 'filter'=>'fileStringLocal', 'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L4', 'key'=>'blogFolderOffset',   'val'=>'/foxfire', 'filter'=>'fileStringLocal',	'ctrl'=>null), 
		array('tree'=>'cache', 'branch'=>'L4', 'key'=>'blogURIOffset',	    'val'=>'/foxfire', 'filter'=>'fileStringLocal',	'ctrl'=>null), 
		    
		);
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
		
	}
	
	
	/**
         * Media Cache -> Images
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_mediaCache_images() {
	    
	   

		$keys = array(

		array('tree'=>'activity',   'branch'=>'singlePost', 'key'=>'thumbSize',		    'val'=>'preview',	'filter'=>'textAndNumbers', 'ctrl'=>null), 
		array('tree'=>'activity',   'branch'=>'multiPost',  'key'=>'thumbSize',		    'val'=>'thumb',	'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'albums',	    'branch'=>'templates',  'key'=>'thumbSize',		    'val'=>'tile',	'filter'=>'textAndNumbers', 'ctrl'=>null), 
		array('tree'=>'media',	    'branch'=>'templates',  'key'=>'thumbSize',		    'val'=>'tile',	'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'media',	    'branch'=>'templates',  'key'=>'singleSize',	    'val'=>'middle',	'filter'=>'textAndNumbers', 'ctrl'=>null), 
		array('tree'=>'media',	    'branch'=>'templates',  'key'=>'pathEmptyAlbumImage',   'val'=> WP_CONTENT_DIR . '/plugins/foxfire/core/images/default_image_thumb_base.png', 'filter'=>'fileStringLocal', 'ctrl'=>null),
		array('tree'=>'cache',	    'branch'=>'thumb',	    'key'=>'fileType',		    'val'=>'jpg',	'filter'=>'textAndNumbers', 'ctrl'=>null), 
		array('tree'=>'cache',	    'branch'=>'thumb',	    'key'=>'pixelsX',		    'val'=>60,		'filter'=>'int',	    'ctrl'=>null),
		array('tree'=>'cache',	    'branch'=>'thumb',	    'key'=>'pixelsY',		    'val'=>60,		'filter'=>'int',	    'ctrl'=>null), 
		array('tree'=>'cache',	    'branch'=>'thumb',	    'key'=>'quality',		    'val'=>80,		'filter'=>'int',	    'ctrl'=>null),
		array('tree'=>'cache',	    'branch'=>'thumb',	    'key'=>'enableCrop',	    'val'=>false,	'filter'=>'bool',	    'ctrl'=>null), 
		array('tree'=>'cache',	    'branch'=>'thumb',	    'key'=>'enableScale',	    'val'=>true,	'filter'=>'bool',	    'ctrl'=>null)		    
		    
		);
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}		
		
	}	
	
	
	/**
         * Debug Tools -> PHP Unit Test
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_debugTools_phpUnitTest() {
	    
		
		$unix_os_names = array(

			"Darwin", "Linux", "Unix", "Ubuntu", "FreeBSD", "IRIX64", "SunOS", "AIX", "Minix", "DragonFly"
		);

		if( array_search( PHP_OS, $unix_os_names) !== false ){

			$platform = "unix";
		}
		else {
			$platform = "windows";
		}


		$keys = array(

		array('tree'=>'system', 'branch'=>'phpUnitTest', 'key'=>'platformFamily',   'val'=>$platform,	'filter'=>'textAndNumbers',	'ctrl'=>null),
		
		array('tree'=>'system', 'branch'=>'phpUnitTest', 'key'=>'dbServerName',	    'val'=>'fox_test',	'filter'=>'slug', 
		      'ctrl'=>array(	'null_input'=>'null',
					'min_len'=>1,    // MySQL requires database name to be at least 1 character long
					'max_len'=>64    // MySQL supports maximum 64 character database name @link: http://dev.mysql.com/doc/refman/5.0/en/identifiers.html
		)),
		
		array('tree'=>'system', 'branch'=>'phpUnitTest', 'key'=>'dbServerLogin',    'val'=>'test',	'filter'=>'slug', 
		      'ctrl'=>array(	'null_input'=>'null',
					'min_len'=>1,    // MySQL requires user name to be at least 1 character long
					'max_len'=>16    // MySQL supports maximum 16 characters for user name @link http://dev.mysql.com/doc/refman/5.6/en/user-names.html
		),),
		
		array('tree'=>'system', 'branch'=>'phpUnitTest', 'key'=>'dbServerPass',	    'val'=>'test',	'filter'=>'printableCharacter', 
		      'ctrl'=>array(	'null_input'=>'null',
					'min_len'=>1,    // MySQL requires all users have a password
					'max_len'=>255   // MySQL has no password length limit because passwords are stored as hashes. Since
							 // the password is hashed it can (in theory) contain any printable character (not documented on the MySQL site)
		),),
		    
		array('tree'=>'system', 'branch'=>'phpUnitTest', 'key'=>'tablePrefix',	    'val'=>'wp_',	'filter'=>'slug',		'ctrl'=>null),
		array('tree'=>'system', 'branch'=>'phpUnitTest', 'key'=>'PHPPath',	    'val'=>'php',	'filter'=>'fileStringLocal',	'ctrl'=>null),
		array('tree'=>'system', 'branch'=>'phpUnitTest', 'key'=>'wordpressPath',    'val'=>ABSPATH,	'filter'=>'fileStringLocal',	'ctrl'=>null),
		array('tree'=>'system', 'branch'=>'phpUnitTest', 'key'=>'testsuitePath',    'val'=>FOX_PATH_BASE.'/unit-test', 'filter'=>'fileStringLocal', 'ctrl'=>null),
		array('tree'=>'system', 'branch'=>'phpUnitTest', 'key'=>'logPath',	    'val'=>FOX_PATH_BASE.'/unit-test', 'filter'=>'fileStringLocal', 'ctrl'=>null),
		array('tree'=>'system', 'branch'=>'phpUnitTest', 'key'=>'options',	    'val'=>'',		'filter'=>'printableCharacter', 'ctrl'=>null),
		    		    
		);
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
		
	}	
	    
	    
	/**
         * Debug Tools -> JS Unit Test
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_debugTools_jsUnitTest() {
	    	    
		
		$keys = array(

		array('tree'=>'system', 'branch'=>'jsUnitTest', 'key'=>'engineNoGlobals',	'val'=>false,	'filter'=>'bool', 'ctrl'=>null), 
		array('tree'=>'system', 'branch'=>'jsUnitTest', 'key'=>'engineNoTryCatch',	'val'=>false,	'filter'=>'bool', 'ctrl'=>null), 
		array('tree'=>'system', 'branch'=>'jsUnitTest', 'key'=>'engineHidePassedTests', 'val'=>true,	'filter'=>'bool', 'ctrl'=>null), 	
		array('tree'=>'system', 'branch'=>'jsUnitTest', 'key'=>'showUserAgent',		'val'=>false,	'filter'=>'bool', 'ctrl'=>null), 
		array('tree'=>'system', 'branch'=>'jsUnitTest', 'key'=>'activeTests',		'val'=>array('core'=>true), 'filter'=>'arrayPattern', 'ctrl'=>array( 'key'=>'keyName', 'val'=>'bool')) 
   
		);
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
		
	}
	
	
	/**
         * Cache Salt
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_cacheSalt() {
	    	    
		
		// Code below is correct. We store the cache salt value in the main plugin config variable so we do not have to do
		// two DB calls every time a media item is loaded. A backup copy of the cache salt value is stored in the
		// site option 'FOX_cache_salt' to allow the value to be recovered if the main config variable gets
		// overwritten or deleted.
		
		$keys = array(

		array('tree'=>"cache", 'branch'=>"salt", 'key'=>"value", 'val'=>get_site_option( 'FOX_cache_salt' ), 'filter'=>"textAndNumbers", 'ctrl'=>null) 
   
		);
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->createKey($key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
		
	}
	
	
	/**
         * Setup Network Modules
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function install_networkModules() {
	    
		try {
			$this->network_modules_class->truncate();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error truncating network modules table",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}		
			
		try {
			$this->network_modules_class->flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error flushing network modules cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}		


		// Load all modules in the network modules folder and add them to the database,
		// the modules will be set as not active by the loader
		
		try {
			$this->network_modules_class->loadModules();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error loading network modules",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}
		

		// Run the install function on each loaded module. This adds the module's config
		// keys to the config class and activates the module (if it wants to be activated)
		
		try {
			$modules = $this->network_modules_class->getAdminModules();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error fetching admin modules",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}		
		

		foreach( $modules as $module ){
		    
			try {
				$module->install();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Error installing network module",
					'data'=>$module,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}	
		}
		unset($module);
		
		
		return true;
					    
	}
	
	
	/**
         * Setup Media Modules
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function install_mediaModules() {
	    
	    				
		try {
			$this->media_modules_class->truncate();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error truncating media modules table",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}		
			
		try {
			$this->media_modules_class->flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error flushing media modules cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}		


		// Load all modules in the media modules folder and add them to the database,
		// the modules will be set as not active by the loader
		
		try {
			$this->media_modules_class->loadModules();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error loading media modules",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}
		

		// Run the install function on each loaded module. This adds the module's config
		// keys to the config class and activates the module (if it wants to be activated)
		
		try {
			$modules = $this->media_modules_class->getAdminModules();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error fetching admin modules",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}		
		

		foreach( $modules as $module ){
		    
			try {
				$module->install();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Error installing media module",
					'data'=>$module,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}	
		}
		unset($module);
		
		
		return true;
							    
	}
	
	
	/**
         * Setup Album Modules
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function install_albumModules() {
	    
	    
		try {
			$this->album_modules_class->truncate();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error truncating album modules table",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}
		
		try {
			$this->album_modules_class->flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error flushing album modules cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}


		// Load all modules in the media modules folder and add them to the database,
		// the modules will be set as not active by the loader
		
		try {
			$this->album_modules_class->loadModules();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error loading album modules",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}		

		// Run the install function on each loaded module. This adds the module's config
		// keys to the config class and activates the module (if it wants to be activated)
		
		try {
			$modules = $this->album_modules_class->getAdminModules();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error fetching album modules",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}		
		

		foreach( $modules as $module ){

			try {
				$module->install();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Error installing album module",
					'data'=>$module,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}		
		}
		unset($module);
		
		
		return true;			
		
	}
	
	
	/**
         * Setup Page Modules
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function install_pageModules() {
	    
	    
		try {
			$this->page_modules_class->truncate();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error truncating page modules table",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}
		
		try {
			$this->page_modules_class->flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error flushing page modules cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}		
		

		// Load all modules in the media modules folder and add them to the database,
		// the modules will be set as not active by the loader
		
		try {
			$this->page_modules_class->loadModules();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error loading page modules",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}		

		// Run the install function on each loaded module. This adds the module's config
		// keys to the config class and activates the module (if it wants to be activated)
		
		try {
			$modules = $this->page_modules_class->getAdminModules();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error fetching admin page modules",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}
		

		foreach( $modules as $module ){

			try {
				$module->install();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Error installing page module",
					'data'=>$module,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));		    
			}
		}
		unset($module);
		
		
		return true;
							    
	}	
	
			
	/**
         * Writes all of FoxFire's base configuration keys to the config class. Note that
	 * page, album, media, and network modules independently write their keys to the
	 * config class during setup, but the actions that trigger it are fired from within
	 * this method.
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function load() {

	    
		// The cache salt value is very important. If it is deleted, all items in the cache will have
		// to be regenerated, and people running caches with unlimited disk space will end up with a
		// huge number of "dead" files. The code below makes it extremely difficult to accidentally
		// delete the salt value. It will survive all FOX tables being dropped and the plugin reactivated.

		if (!get_site_option( 'FOX_cache_salt' )) {

		    // IMPORTANT - the cache salt value is generated the same way WP generates their salt value,
		    // and WP generates their salt value this way for specific reasons. A single width MD5 is not
		    // strong enough, so two are used. Each MD5 must use its own random seed. It is important each
		    // MD5 uses only ONE random seed, not two of them added or multiplied together. A single random
		    // seed produces a flat distribution ...but random seeds multiplied together produce a *log*
		    // distribution, and random seeds added together prduce a *bell curve* distribution. This would
		    // dramatically reduce the security of the cache salt value.

		    $salt = md5( mt_rand() ) . md5( mt_rand() ); 
		    update_site_option('FOX_cache_salt', $salt);
		}
		
		// Clear the config class db table and flush its cache
		// =================================================================

		try {
			$this->config_class->truncate();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error truncating table",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}
		
		try {
			$this->config_class->flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error flushing cache",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}
		
		// Write keys to config class
		// =================================================================
		
		try {
			self::set_coreSettings();
			self::set_albumModules();
			self::set_pluginSetup_display();	
			self::set_pluginSetup_pageSlugs();
			self::set_pluginSetup_addingMedia();
			self::set_socialSettings_keywordTags();	
			self::set_socialSettings_memberTags();
			self::set_socialSettings_activityStream();
			self::set_socialSettings_notifications();
			self::set_socialSettings_mediaRating();
			self::set_widgets_memberUploads();
			self::set_widgets_sitewideUploads();
			self::set_mediaCache_fileSystem();
			self::set_fileSettings_filePaths();
			self::set_mediaCache_images();
			self::set_debugTools_phpUnitTest();
			self::set_debugTools_jsUnitTest();
			self::set_cacheSalt();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in key loader",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}
		
		
		// Write network module keys
		// =================================================================		
		
		try {
//			self::install_networkModules();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error loading network module keys",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}
		
		// Write media module keys
		// =================================================================
		
		try {
			self::install_mediaModules();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Error loading media module keys",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}	
		
		// Write album module keys
		// =================================================================
		
		try {
			self::install_albumModules();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error loading album module keys",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}
		
		// Write page module keys
		// =================================================================
		
		try {
			self::install_pageModules();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>7,
				'text'=>"Error loading page module keys",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}		
		
		
		// Set RBAC schema
		// =================================================================		
		
		try {
//			$cls = new FOX_config_defaultSchema();
//			$cls->load();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>8,
				'text'=>"Error loading RBAC schema",
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));		    
		}
		
		return true;

	}

	
	
} // End of class FOX_config_defaultKeys



/**
 * Action function to instantiate the class during setup
 *
 * @version 1.0
 * @since 1.0
 */

function FOX_config_defaultKeys_install (){

	$cls = new FOX_config_defaultKeys();
	$cls->load();
}
add_action( 'fox_setDefaults', 'FOX_config_defaultKeys_install', 2 );


?>