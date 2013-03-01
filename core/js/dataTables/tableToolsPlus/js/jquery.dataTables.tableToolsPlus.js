/**
 * @summary     Column Filter
 * @description Filters datatable columns by range or value, using local or remote data sources
 * @file        dataTables.TableToolsPlusPlus.js
 * @version     1.0
 * @author      Carl Roett, based on code by Allan Jardine
 * @license     GPL v2 or BSD 3 point style
 * @contact     https://github.com/foxly
 */

var TableToolsPlus;  // Global scope for TableToolsPlus 

(function($, window, document) {

/** 
 * TableToolsPlus provides flexible buttons and other tools for a DataTables enhanced table
 * @class TableToolsPlus
 * @constructor
 * @param {Object} oDT DataTables instance
 * @param {Object} oOpts TableToolsPlus optionsh
 * @param {String} oOpts.sRowSelect Row selection options - 'none', 'single' or 'multi'
 * @param {Function} oOpts.fnPreRowSelect Callback function just prior to row selection
 * @param {Function} oOpts.fnRowSelected Callback function just after row selection
 * @param {Function} oOpts.fnRowDeselected Callback function when row is deselected
 * @param {Array} oOpts.aButtons List of buttons to be used
 */
TableToolsPlus = function( oDT, oOpts ) {
    
    
	// Sanity check parent we're a new instance 
	
	if ( ! this instanceof TableToolsPlus )
	{
		alert( "Warning: TableToolsPlus must be initialised with the keyword 'new'" );
	}
	
	/**
	* @namespace Settings object which contains customisable information for TableToolsPlus instance
	* ==========================================================================================================
	*/
	this.s = {

		/**
		 * Store 'this' so the instance can be retrieved from the settings object
		 * @property parent
		 * @type	 object
		 * @default  this
		 */
		"parent": this,
		
		/** 
		 * DataTables settings objects
		 * @property dt
		 * @type	 object
		 * @default  <i>From the oDT init option</i>
		 */
		"dt": oDT.fnSettings(), // @see DataTable.models.oRow in dataTables library
		
		/**
		* @namespace Print specific information
		* ===================================================================================
		*/
		"print": {

			/** 
			* DataTables draw 'start' point before the printing display was shown
			*  @property saveStart
			*  @type	 int
			*  @default  -1
			*/
			"saveStart": -1,

			/** 
			* DataTables draw 'length' point before the printing display was shown
			*  @property saveLength
			*  @type	 int
			*  @default  -1
			*/
			"saveLength": -1,

			/** 
			* Page scrolling point before the printing display was shown so it can be restored
			*  @property saveScroll
			*  @type	 int
			*  @default  -1
			*/
			"saveScroll": -1,

			/** 
			* Wrapped function to end the print display (to maintain scope)
			*  @property funcEnd
			*  @type	 Function
			*  @default  function () {}
			*/
			"funcEnd": function () {}

	  },
	
	/**
	* A unique ID is assigned to each button in each instance
	* @property buttonCounter
	*  @type	 int
	* @default  0
	*/
	"buttonCounter": 0,
		
		/**
		* @namespace Select rows specific information
		* ===================================================================================
		*/
		"select": {
		    
			/**
			 * Select type - can be 'none', 'single' or 'multi'
			 * @property type
			 *  @type	 string
			 * @default  ""
			 */
			"type": "",
			
			/**
			 * Array of nodes which are currently selected
			 *  @property selected
			 *  @type	 array
			 *  @default  []
			 */
			"selected": [],
			
			/**
			 * Function to run before the selection can take place. Will cancel the select if the
			 * function returns false
			 *  @property preRowSelect
			 *  @type	 Function
			 *  @default  null
			 */
			"preRowSelect": null,
			
			/**
			 * Function to run when a row is selected
			 *  @property postSelected
			 *  @type	 Function
			 *  @default  null
			 */
			"postSelected": null,
			
			/**
			 * Function to run when a row is deselected
			 *  @property postDeselected
			 *  @type	 Function
			 *  @default  null
			 */
			"postDeselected": null,
			
			/**
			 * Indicate if all rows are selected (needed for server-side processing)
			 *  @property all
			 *  @type	 boolean
			 *  @default  false
			 */
			"all": false,
			
			/**
			 * Class name to add to selected TR nodes
			 *  @property selectedClass
			 *  @type	 String
			 *  @default  ""
			 */
			"selectedClass": ""
		},
		
		/**
		 * Store of the user input customisation object
		 *  @property custom
		 *  @type	 object
		 *  @default  {}
		 */
		"custom": {},
		
		/**
		 * Default button set
		 *  @property buttonSet
		 *  @type	 array
		 *  @default  []
		 */
		"buttonSet": [],
		
		/**
		 * When there is more than one TableToolsPlus instance for a DataTable, there must be a 
		 * master which controls events (row selection etc)
		 *  @property master
		 *  @type	 boolean
		 *  @default  false
		 */
		"master": false,
		
		/**
		 * Tag names parent are used for creating collections and buttons
		 *  @namesapce
		 */
		"tags": {}
		
	};
	
	
	/**
	* @namespace Common and useful DOM elements for the class instance
	* ==========================================================================================================
	*/
	this.dom = {
	    
		/**
		 * DIV element parent is create and all TableToolsPlus buttons (and their children) put into
		 *  @property container
		 *  @type	 node
		 *  @default  null
		 */
		"container": null,
		
		/**
		 * The table node to which TableToolsPlus will be applied
		 *  @property table
		 *  @type	 node
		 *  @default  null
		 */
		"table": null,
		
		/**
		* @namespace Nodes used for the print display
		* ===================================================================================
		*/
		"print": {
		    
			/**
			 * Nodes which have been removed from the display by setting them to display none
			 *  @property hidden
			 *  @type array
		 	 *  @default  []
			 */
			"hidden": [],
			
			/**
			 * The information display saying telling the user about the print display
			 *  @property message
			 *  @type	 node
		 	 *  @default  null
			 */
			"message": null
		},
		
		/**
		* @namespace Nodes used for a collection display. This contains the currently used collection
		* ===================================================================================
		*/
		"collection": {
		    
			/**
			 * The div wrapper containing the buttons in the collection (i.e. the menu)
			 *  @property collection
			 *  @type node
		 	 *  @default  null
			 */
			"collection": null,
			
			/**
			 * Background display to provide focus and capture events
			 *  @property background
			 *  @type node
		 	 *  @default  null
			 */
			"background": null
		}
		
	};


	/**
	 * @namespace Name space for the classes parent this TableToolsPlus instance will use
	 * @extends TableToolsPlus.classes
	 */
	this.classes = $.extend( true, {}, TableToolsPlus.classes );
	
	if(this.s.dt.bJUI){
	    
		$.extend( true, this.classes, TableToolsPlus.classes_themeroller );
	}
	
	
	/**
	 * Retreieve the settings object from an instance
	 *  @method fnSettings
	 *  @returns {object} TableToolsPlus settings object
	 */
	this.fnSettings = function(){
	    
		return this.s;
	}
		
	if(typeof oOpts == 'undefined'){
	    
		oOpts = {};
	}
	
	this._fnConstruct(oOpts);
	
	return this;
	
};



TableToolsPlus.prototype = {
	
	
	/**
	 * Constructor logic
	 *  @method  _fnConstruct
	 *  @param   {Object} oOpts Same as TableToolsPlus constructor
	 *  @returns void
	 *  @private 
	 */
	"_fnConstruct": function(oOpts){
	    
		this._ctrlKeyActive = false;
		this._shiftKeyActive = false;
		
		var parent = this;
		
		this._fnCustomiseSettings(oOpts);		
		
		// Register keydown functions, so we can track the state of the CTRL
		// and SHIFT keys through private variables
		// =========================================================================
		
		// @see http://stackoverflow.com/questions/2445613/how-can-i-check-if-key-is-pressed-during-click-event-with-jquery
		// @see http://stackoverflow.com/questions/3834175/jquery-key-code-for-command-key
		// @see http://www.cambiaresearch.com/articles/15/javascript-char-codes-key-codes
		
		$(window).keydown(function(evt) {
		    			 			
			if( (evt.which == 17)		// CTRL on Windows
			    || (evt.which == 224)	// COMMAND in FireFox			
			    || (evt.which == 91)	// COMMAND (LEFT) on Mac, in Chrome / Safari
			    || (evt.which == 93) ){	// COMMAND (RIGHT) on Mac, in Chrome / Safari

				parent._ctrlKeyActive = true;
			}
			
			// SHIFT on Windows and Mac
			
			if(evt.which == 16){ 

				parent._shiftKeyActive = true;
			}	
			
			// ESCAPE on Windows and Mac
			
			if(evt.which == 27){ 

				parent.fnSelectNone();	// Deselect all rows
			}			
						
				
		}).keyup(function(evt) {
			    
			if( (evt.which == 17)		// CTRL on Windows
			    || (evt.which == 224)	// COMMAND in FireFox			
			    || (evt.which == 91)	// COMMAND (LEFT) on Mac, in Chrome / Safari
			    || (evt.which == 93) ){	// COMMAND (RIGHT) on Mac, in Chrome / Safari

				parent._ctrlKeyActive = false;
			}
			
			// SHIFT on Windows and Mac
			
			if(evt.which == 16){

				parent._shiftKeyActive = false;
			}						
		});	
		
		
		// Container element 
		
		this.dom.container = document.createElement(this.s.tags.container);
		this.dom.container.className = this.classes.container;
		
		// Row selection config
		
		if( this.s.select.type != 'none' ){
		    
			this._fnRowSelectConfig();
		}
		
		// Buttons
		
		this._fnButtonDefinations(this.s.buttonSet, this.dom.container);
		
		// Destructor - need to wipe the DOM for IE's garbage collector
		
		this.s.dt.aoDestroyCallback.push( {
		    
			"sName": "TableToolsPlus",
			"fn": function(){			    
				parent.dom.container.innerHTML = "";
			}
		} );
		
	},
	
	/**
	 * Retreieve the settings object from an instance
	 *  @returns {array} List of TR nodes which are currently selected
	 *  @param {boolean} [filtered=false] Get only selected rows which are  
	 *    available given the filtering applied to the table. By default
	 *    this is false -  i.e. all rows, regardless of filtering are 
	 *    selected.
	 */
	"fnGetSelected": function(filtered){

		var out = [];
		var data = this.s.dt.aoData;
		var displayed = this.s.dt.aiDisplay;
		var i, iLen;
		

		if(filtered){
		    
			// Only consider filtered rows
			
			iLen = displayed.length; // Caching to prevent .length() running on each loop iteration

			for(i=0; i < iLen; i++){
			    
				if(data[ displayed[i] ]._DTTT_selected){
				    
					out.push( data[ displayed[i] ].nTr );
				}
			}
		}
		else {
			// Use all rows
			
			iLen = data.length;
			
			for(i=0; i < iLen; i++){
			    
				if(data[i]._DTTT_selected){
				    
					out.push( data[i].nTr );
				}
			}
		}

		return out;
		
	},

	/**
	 * Get the data source objects/arrays from DataTables for the selected rows (same as
	 * fnGetSelected followed by fnGetData on each row from the table)
	 *  @returns {array} Data from the TR nodes which are currently selected
	 */
	"fnGetSelectedData": function(){
	    
		var out = [];
		var data = this.s.dt.aoData;
		var iLen = data.length; // Caching to prevent .length() running on each loop iteration

		for(var i=0; i < iLen; i++){
		    
			if(data[i]._DTTT_selected){
			    
				out.push( this.s.dt.oInstance.fnGetData(i) );
			}
		}

		return out;
		
	},
	
	/**
	 * Check to see if a current row is selected or not
	 *  @param {Node} n TR node to check if it is currently selected or not
	 *  @returns {Boolean} true if select, false otherwise
	 */
	"fnIsSelected": function(n){
	    
	    
		var pos = this.s.dt.oInstance.fnGetPosition(n);
		
		if(this.s.dt.aoData[pos]._DTTT_selected === true){
		    
			return true;
		}
		else {
			return false;
		}
		
	},

	/**
	 * Select all rows in the table
	 *  @param {boolean} [filtered=false] Select only rows which are available 
	 *    given the filtering applied to the table. By default this is false - 
	 *    i.e. all rows, regardless of filtering are selected.
	 */
	"fnSelectAll": function(filtered){
	    
	    
		var s = this._fnGetMasterSettings();
		
		if(filtered === true){
		    
			this._fnRowSelect(s.dt.aiDisplay);
		}
		else {		    
			this._fnRowSelect(s.dt.aoData);
		}
		
	},

	/**
	 * Deselect all rows in the table
	 *  @param {boolean} [filtered=false] Deselect only rows which are available 
	 *    given the filtering applied to the table. By default this is false - 
	 *    i.e. all rows, regardless of filtering are deselected.
	 */
	"fnSelectNone": function(filtered){

		this._fnRowDeselect( this.fnGetSelected(filtered) );
	},

	/**
	 * Select row(s)
	 *  @param {node|object|array} n The row(s) to select. Can be a single DOM
	 *    TR node, an array of TR nodes or a jQuery object.
	 */
	"fnSelect": function(n){
	    
	    
		if( this.s.select.type == "single" ){
		    
			this.fnSelectNone();
			this._fnRowSelect(n);
		}
		else if( this.s.select.type == "multi" ){
		    
			this._fnRowSelect(n);
		}
		
	},

	/**
	 * Deselect row(s)
	 *  @param {node|object|array} n The row(s) to deselect. Can be a single DOM
	 *    TR node, an array of TR nodes or a jQuery object.
	 */
	"fnDeselect": function(n){
	    
		this._fnRowDeselect(n);
	},
	
	/**
	 * Get the title of the document - useful for file names. The title is retrieved from either
	 * the configuration object's 'title' parameter, or the HTML document title
	 *  @param   {Object} oConfig Button configuration object
	 *  @returns {String} Button title
	 */
	"fnGetTitle": function(oConfig){
	    
	    
		var sTitle = "";
		
		if( typeof oConfig.sTitle != 'undefined' && oConfig.sTitle !== "" ){
		    
			sTitle = oConfig.sTitle;
		} 
		else {
		    
			var anTitle = document.getElementsByTagName('title');
			
			if( anTitle.length > 0 ){
			    
				sTitle = anTitle[0].innerHTML;
			}
		}
		
		// Strip characters which the OS will object to - checking for UTF8 support in 
		// the scripting engine
		
		if( "\u00A1".toString().length < 4 ){
		    
			return sTitle.replace(/[^a-zA-Z0-9_\u00A1-\uFFFF\.,\-_ !\(\)]/g, "");
		} 
		else {
			return sTitle.replace(/[^a-zA-Z0-9_\.,\-_ !\(\)]/g, "");
		}
		
	},
	
	/**
	 * Calculate a unity array with the column width by proportion for a set of columns to be
	 * included for a button. This is particularly useful for PDF creation, where we can use the
	 * column widths calculated by the browser to size the columns in the PDF.
	 *  @param   {Object} oConfig Button configuration object
	 *  @returns {Array} Unity array of column ratios
	 */
	"fnCalcColRatios": function(oConfig){
				
		var aoCols = this.s.dt.aoColumns;		
		var aColumnsInc = this._fnColumnTargets( oConfig.mColumns );		
		var aColWidths = [];
		var iWidth = 0;
		var iTotal = 0;
		var i, iLen;		
		
		iLen = aColumnsInc.length; // Caching to prevent .length() running on each loop iteration
		
		for(i=0; i < iLen; i++){
		    
			if(aColumnsInc[i]){
			    
				iWidth = aoCols[i].nTh.offsetWidth;
				iTotal += iWidth;
				aColWidths.push(iWidth);
			}
		}		
		
		iLen = aColWidths.length;
		
		for(i=0; i < iLen; i++){
		    
			aColWidths[i] = (aColWidths[i] / iTotal);
		}
		
		return aColWidths.join('\t');
		
	},
	
	/**
	 * Get the information contained in a table as a string
	 *  @param   {Object} oConfig Button configuration object
	 *  @returns {String} Table data as a string
	 */
	"fnGetTableData": function(oConfig){
	
		// In future this could be used to get data from a plain HTML source as well as DataTables
	
		if(this.s.d){
	
			return this._fnGetDataTablesData(oConfig);
		}
	
	},
	
	/**
	 * Programmatically enable or disable the print view
	 *  @param {boolean} [bView=true] Show the print view if true or not given. If false, then
	 *    terminate the print view and return to normal.
	 *  @param {object} [oConfig={}] Configuration for the print view
	 *  @param {boolean} [oConfig.bShowAll=false] Show all rows in the table if true
	 *  @param {string} [oConfig.sInfo] Information message, displayed as an overlay to the
	 *    user to let them know what the print view is.
	 *  @param {string} [oConfig.sMessage] HTML string to show at the top of the document - will
	 *    be included in the printed document.
	 */
	"fnPrint": function(bView, oConfig){
	    
		if(oConfig === undefined){
		    
			oConfig = {};
		}

		if( (bView === undefined) || bView ){
		    
			this._fnPrintStart(oConfig);
		}
		else{
		    
			this._fnPrintEnd();
		}
		
	},
	
	/**
	 * Show a message to the end user which is nicely styled
	 *  @param {string} message The HTML string to show to the user
	 *  @param {int} time The duration the message is to be shown on screen for (mS)
	 */
	"fnInfo": function(message, time){
	    
	    
		var nInfo = document.createElement("div");
		
		nInfo.className = this.classes.print.info;
		nInfo.innerHTML = message;

		document.body.appendChild(nInfo);
		
		setTimeout( function(){
		    
				    $(nInfo).fadeOut( "normal", function() {

					    document.body.removeChild(nInfo);
				    });
			    },
			    time 
		);
	
	},
	
	/**
	 * Take the user defined settings and the default settings and combine them.
	 *  @method  _fnCustomiseSettings
	 *  @param   {Object} oOpts Same as TableToolsPlus constructor
	 *  @returns void
	 *  @private 
	 */
	"_fnCustomiseSettings": function(oOpts){
	    
		// Is this the master control instance or not?
		
		if( typeof this.s.dt._TableToolsPlusInit == 'undefined' ){
		    
			this.s.master = true;
			this.s.dt._TableToolsPlusInit = true;
		}
		
		// We can use the table node from comparisons to group controls 
		this.dom.table = this.s.dt.nTable;
		
		// Clone the defaults and then the user options 
		this.s.custom = $.extend( {}, TableToolsPlus.DEFAULTS, oOpts );
		
		
		// Table row selecting 
		this.s.select.type = this.s.custom.sRowSelect;
		this.s.select.preRowSelect = this.s.custom.fnPreRowSelect;
		this.s.select.postSelected = this.s.custom.fnRowSelected;
		this.s.select.postDeselected = this.s.custom.fnRowDeselected;

		// Backwards compatibility - allow the user to specify a custom class in the initialiser
		if(this.s.custom.sSelectedClass){
		    
			this.classes.select.row = this.s.custom.sSelectedClass;
		}

		this.s.tags = this.s.custom.oTags;

		// Button set 
		this.s.buttonSet = this.s.custom.aButtons;
		
	},
	
	
	/**
	 * Take the user input arrays and expand them to be fully defined, and then add them to a given
	 * DOM element
	 *  @method  _fnButtonDefinations
	 *  @param {array} buttonSet Set of user defined buttons
	 *  @param {node} wrapper Node to add the created buttons to
	 *  @returns void
	 *  @private 
	 */
	"_fnButtonDefinations": function (buttonSet, wrapper){
	    
	    
		var buttonDef;
		var iLen = buttonSet.length; // Caching to prevent .length() running on each loop iteration
		
		
		for(var i=0; i < iLen; i++){
		    
			if( typeof buttonSet[i] == "string" ){
			    
				if( typeof TableToolsPlus.BUTTONS[ buttonSet[i] ] == 'undefined' ){
				    
					alert( "TableToolsPlus: Warning - unknown button type: " + buttonSet[i] );
					continue;
				}
				
				buttonDef = $.extend( {}, TableToolsPlus.BUTTONS[ buttonSet[i] ], true );
			}
			else {
			    
				if( typeof TableToolsPlus.BUTTONS[ buttonSet[i].sExtends ] == 'undefined' ){
				    
					alert( "TableToolsPlus: Warning - unknown button type: " + buttonSet[i].sExtends );
					continue;
				}
				
				var o = $.extend({}, TableToolsPlus.BUTTONS[ buttonSet[i].sExtends ], true);
				
				buttonDef = $.extend(o, buttonSet[i], true);
			}
			
			wrapper.appendChild( this._fnCreateButton( 
			
				buttonDef, 
				$(wrapper).hasClass(this.classes.collection.container)
			) );
			    
		}
		
	},
	
	
	/**
	 * Create and configure a TableToolsPlus button
	 *  @method  _fnCreateButton
	 *  @param   {Object} oConfig Button configuration object
	 *  @returns {Node} Button element
	 *  @private 
	 */
	"_fnCreateButton": function(oConfig, bCollectionButton){
	    
	    
		var nButton = this._fnButtonBase(oConfig, bCollectionButton);
		
		if( oConfig.sAction == "text" ){
		    
			this._fnTextConfig( nButton, oConfig );
		}
		else if( oConfig.sAction == "div" ){
		    
			this._fnTextConfig( nButton, oConfig );
		}
		else if( oConfig.sAction == "collection" ){
		    
			this._fnTextConfig( nButton, oConfig );
			this._fnCollectionConfig( nButton, oConfig );
		}
		
		return nButton;
		
	},
	
	
	/**
	 * Create the DOM needed for the button and apply some base properties. All buttons start here
	 *  @method  _fnButtonBase
	 *  @param   {o} oConfig Button configuration object
	 *  @returns {Node} DIV element for the button
	 *  @private 
	 */
	"_fnButtonBase": function(o, bCollectionButton){
	    
		var sTag, sLiner, sClass;

		if(bCollectionButton){
		    
			if( o.sTag !== "default" ){
			    
				sTag = o.sTag;
			}
			else {
				sTag = this.s.tags.collection.button;
			}
			
			if( o.sLinerTag !== "default" ){
			    
				sLiner = o.sLiner;
			}
			else {
				sLiner = this.s.tags.collection.liner;
			}			
			
			sClass = this.classes.collection.buttons.normal;
		}
		else {
		    
			if( o.sTag !== "default" ){
			    
				sTag = o.sTag;
			}
			else {
				sTag = this.s.tags.button;
			}
			
			if( o.sLinerTag !== "default" ){
			    
				sLiner = o.sLiner;
			}
			else {
				sLiner = this.s.tags.liner;
			}			
			
			sClass = this.classes.buttons.normal;
			
		}

		
		var nButton = document.createElement(sTag);
		var nSpan = document.createElement(sLiner);
		var masterS = this._fnGetMasterSettings();
		
		nButton.className = sClass+" "+o.sButtonClass;
		nButton.setAttribute('id', "ToolTables_"+this.s.dt.sInstance+"_"+masterS.buttonCounter );
		nButton.appendChild( nSpan );
		nSpan.innerHTML = o.sButtonText;
		
		masterS.buttonCounter++;
		
		return nButton;
		
	},
		
	/**
	 * Get the settings object for the master instance. When more than one TableToolsPlus instance is
	 * assigned to a DataTable, only one of them can be the 'master' (for the select rows). As such,
	 * we will typically want to interact with parent master for global properties.
	 *  @method  _fnGetMasterSettings
	 *  @returns {Object} TableToolsPlus settings object
	 *  @private 
	 */
	"_fnGetMasterSettings": function(){
	    
	    
		if(this.s.master){
		    
			return this.s;
		}
		else {
			// Look for the master which has the same DT as this one
			
			var instances = TableToolsPlus._aInstances;
			var iLen = instances.length;  // Caching to prevent .length() running on each loop iteration

			for(var i=0; i < iLen; i++){
			    
				if( this.dom.table == instances[i].s.dt.nTable ){
				    
					return instances[i].s;
				}
			}
		}
		
	},
		
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Button collection functions
	 */
	
	/**
	 * Create a collection button, when activated will present a drop down list of other buttons
	 *  @param   {Node} nButton Button to use for the collection activation
	 *  @param   {Object} oConfig Button configuration object
	 *  @returns void
	 *  @private
	 */
	"_fnCollectionConfig": function(nButton, oConfig){
	    
		var nHidden = document.createElement(this.s.tags.collection.container);
		
		nHidden.style.display = "none";
		nHidden.className = this.classes.collection.container;
		oConfig._collection = nHidden;
		document.body.appendChild(nHidden);
		
		this._fnButtonDefinations( oConfig.aButtons, nHidden );
		
	},
		
	/**
	 * Show a button collection
	 *  @param   {Node} nButton Button to use for the collection
	 *  @param   {Object} oConfig Button configuration object
	 *  @returns void
	 *  @private
	 */
	"_fnCollectionShow": function(nButton, oConfig){
	    
		
		var parent = this;
		var oPos = $(nButton).offset();
		var nHidden = oConfig._collection;
		var iDivX = oPos.left;
		var iDivY = oPos.top + $(nButton).outerHeight();
		var iWinHeight = $(window).height(), iDocHeight = $(document).height();
		var iWinWidth = $(window).width(), iDocWidth = $(document).width();
		
		nHidden.style.position = "absolute";
		nHidden.style.left = iDivX+"px";
		nHidden.style.top = iDivY+"px";
		nHidden.style.display = "block";
		$(nHidden).css('opacity',0);
		
		var nBackground = document.createElement('div');
		
		nBackground.style.position = "absolute";
		nBackground.style.left = "0px";
		nBackground.style.top = "0px";
		
		if( iWinHeight > iDocHeight ){
		    
			nBackground.style.height = iWinHeight + "px";
		}
		else {
			nBackground.style.height = iDocHeight + "px";
		}
				
		if( iWinWidth > iDocWidth ){
		    
			nBackground.style.width = iWinWidth + "px";
		}
		else {
			nBackground.style.width = iDocWidth + "px";
		}		

		nBackground.className = this.classes.collection.background;
		$(nBackground).css('opacity',0);
		
		document.body.appendChild( nBackground );
		document.body.appendChild( nHidden );
		
		// Visual corrections to try and keep the collection visible 
		var iDivWidth = $(nHidden).outerWidth();
		var iDivHeight = $(nHidden).outerHeight();
		
		if( (iDivX + iDivWidth) > iDocWidth ){
		    
			nHidden.style.left = (iDocWidth - iDivWidth) + "px";
		}
		
		if( (iDivY + iDivHeight) > iDocHeight ){
		    
			nHidden.style.top = (iDivY - iDivHeight - $(nButton).outerHeight()) + "px";
		}
	
		this.dom.collection.collection = nHidden;
		this.dom.collection.background = nBackground;
		
		// This results in a very small delay for the end user but it allows the animation to be
		// much smoother. If you don't want the animation, then the setTimeout can be removed

		setTimeout( function(){
		    
				    $(nHidden).animate({"opacity": 1}, 500);
				    $(nBackground).animate({"opacity": 0.25}, 500);
			    }, 
			    10 
		);

		// Resize the buttons to the Flash contents fit
		this.fnResizeButtons();
		
		// Event handler to remove the collection display
		$(nBackground).click( function(){
		    
			parent._fnCollectionHide.call( parent, null, null );
		} );
		
	},
	
	
	/**
	 * Hide a button collection
	 *  @param   {Node} nButton Button to use for the collection
	 *  @param   {Object} oConfig Button configuration object
	 *  @returns void
	 *  @private
	 */
	"_fnCollectionHide": function(nButton, oConfig){
	    
		if( (oConfig !== null) && (oConfig.sExtends == 'collection') ){
		    
			return;
		}
		
		if( this.dom.collection.collection !== null ){
		    
			$(this.dom.collection.collection).animate({"opacity": 0}, 500, function(e){
				this.style.display = "none";
			} );
			
			$(this.dom.collection.background).animate({"opacity": 0}, 500, function(e){
				this.parentNode.removeChild( this );
			} );
			
			this.dom.collection.collection = null;
			this.dom.collection.background = null;
		}
	},		
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Row selection functions
	 */
	
	/**
	 * Add event handlers to a table to allow for row selection
	 *  @method  _fnRowSelectConfig
	 *  @returns void
	 *  @private 
	 */
	"_fnRowSelectConfig": function(){
	    
		if(this.s.master){
		    			
			var parent = this;
			var dt = this.s.dt;						
			
			$(dt.nTable).addClass(this.classes.select.table);
			
			$('tr', dt.nTBody).live( 'click', function(e){
			    
				var i;	
				
				// Sub-table must be ignored (odd that the selector won't do this with >)
				if( this.parentNode != dt.nTBody ){
				    
					return;
				}
				
				// Check that we are actually working with a DataTables controlled row 
				if( dt.oInstance.fnGetData(this) === null ){
				    
					return;
				}

				// Get all the rows that will be selected
				var data = parent._fnSelectData(this);
				var iLen = data.length;  // Caching to prevent .length() running on each loop iteration
				
				// Emulate CTRL key functionality
				// =================================================================
					
				if(parent._ctrlKeyActive){
		    
					if( parent.fnIsSelected(this) ){

						parent._fnRowDeselect(this, e);						
					}
					else {
						parent._fnRowSelect(this, e);						
					}				
				}
				
				// Emulate SHIFT key functionality
				// =================================================================		
				
				else if(parent._shiftKeyActive){
				    				    
								    
					var allRows = parent._fnSelectData($('.DTTT_selectable tbody tr').get());
					var selectedRows = parent._fnSelectData($('.DTTT_selectable tbody tr.DTTT_selected').get());

					var clickedRow = parent._fnSelectData(this)[0];
					var clickedRowOffset = clickedRow.nTr._DT_RowIndex;	
					

					// If no rows selected, select from [first row in table] down to [clicked row]
					// ----------------------------------------------------------------------------

					if(selectedRows.length == 0){
						
						iLen = allRows.length;
						
						for(i=0; i <= clickedRowOffset; i++){

							allRows[i]._DTTT_selected = true;
							$(allRows[i].nTr).addClass( parent.classes.select.row );						    
						}					
					}
					else {

						var bestAbove = null;
						var bestBelow = null;
						
						iLen = selectedRows.length;
						
						for(i=0; i < iLen; i++){						    
						    						    
							if( selectedRows[i].nTr._DT_RowIndex < clickedRowOffset ){
							    
								if( (selectedRows[i].nTr._DT_RowIndex > bestAbove) || (bestAbove === null) ){
    
									bestAbove = selectedRows[i].nTr._DT_RowIndex;
								}
							}
							else if( selectedRows[i].nTr._DT_RowIndex > clickedRowOffset ){
							    
								if( (selectedRows[i].nTr._DT_RowIndex > bestBelow) || (bestBelow === null)){
    
									bestBelow = selectedRows[i].nTr._DT_RowIndex;
								}
							}														
						}
						
						// Clear all currently selected rows
						parent.fnSelectNone();
						
						// If [clicked row] is below last row in group, select from 
						// [last row in group] to [clicked row]						
						// ----------------------------------------------------------------------------
						
						if(bestBelow !== null){						    

							for(i=clickedRowOffset; i <= bestBelow; i++){

								allRows[i]._DTTT_selected = true;
								$(allRows[i].nTr).addClass( parent.classes.select.row );						    
							}						    						    
						}
						
						// If [clicked row] is above first row in group, or [clicked row] is between 
						// first and last rows in group, select from [clicked row] to [last row in group]
						// ----------------------------------------------------------------------------
						
						else {
							for(i=bestAbove; i <= clickedRowOffset; i++){

								allRows[i]._DTTT_selected = true;
								$(allRows[i].nTr).addClass( parent.classes.select.row );						    
							}						    						    
						}						
					}
				    
				}
				
				// Handle no modifier key pressed
				// =================================================================
				
				else {				    
										
					if( parent.fnIsSelected(this) ){
					    
						parent.fnSelectNone();
						parent._fnRowDeselect(this, e);						
					}
					else {
						parent.fnSelectNone();
						parent._fnRowSelect(this, e);						
					}			    				    
				}
				
				
			} );

			// Bind a listener to the DataTable for when new rows are created.
			// This allows rows to be visually selected when they should be and
			// deferred rendering is used.
			
			dt.oApi._fnCallbackReg( dt, 'aoRowCreatedCallback', function(tr, data, index){
			    
				if( dt.aoData[index]._DTTT_selected ){
				    
					$(tr).addClass(parent.classes.select.row);
				}
				
			}, 'TableToolsPlus-SelectAll' );
			
		}
		
	},

	/**
	 * Select rows
	 *  @param   {*} src Rows to select - see _fnSelectData for a description of valid inputs
	 *  @private 
	 */
	"_fnRowSelect": function(src, e){
	    
		var parent = this;
				
		var anSelected = [];
		var i, iLen;
		
		// Get all the rows that will be selected
		// =================================================================

		var data = this._fnSelectData(src);
		
		iLen = data.length;  // Caching to prevent .length() running on each loop iteration
		
		for(i=0; i < iLen; i++){
		    
			if(data[i].nTr){
			    
				anSelected.push(data[i].nTr);
			}
		}
		
		// User defined pre-selection function
		if( (this.s.select.preRowSelect !== null) && !this.s.select.preRowSelect.call(this, e, anSelected, true) ){
		    
			return;
		}

		// Mark them as selected
		// =================================================================
		
		iLen = data.length;
		
		for(i=0; i < iLen; i++){
		    
			data[i]._DTTT_selected = true;

			if(data[i].nTr){
			    
				$(data[i].nTr).addClass( parent.classes.select.row );
			}
		}

		// Post-selection function
		if( this.s.select.postSelected !== null ){
		    
			this.s.select.postSelected.call( this, anSelected );
		}

		TableToolsPlus._fnEventDispatch( this, 'select', anSelected, true );
		
	},

	/**
	 * Deselect rows
	 *  @param   {*} src Rows to deselect - see _fnSelectData for a description of valid inputs
	 *  @private 
	 */
	"_fnRowDeselect": function(src, e){
	    		
		var parent = this;
		
		var data = this._fnSelectData(src);
		var anDeselectedTrs = [];
		var i, iLen;

		// Get all the rows parent will be deselected
		
		iLen = data.length;  // Caching to prevent .length() running on each loop iteration
		
		for(i=0; i < iLen; i++){
		    
			if(data[i].nTr){
			    
				anDeselectedTrs.push( data[i].nTr );
			}
		}

		// User defined pre-selection function
		if( (this.s.select.preRowSelect !== null) && !this.s.select.preRowSelect.call(this, e, anDeselectedTrs, false) ){
		    
			return;
		}

		// Mark them as deselected
		
		iLen = data.length;
		
		for(i=0; i < data.length; i++){
		    
			data[i]._DTTT_selected = false;

			if( data[i].nTr ){
			    
				$(data[i].nTr).removeClass(parent.classes.select.row);
			}
		}

		// Post-deselection function
		if( this.s.select.postDeselected !== null ){
		    
			this.s.select.postDeselected.call( this, anDeselectedTrs );
		}

		TableToolsPlus._fnEventDispatch( this, 'select', anDeselectedTrs, false );
		
	},
	
	/**
	 * Take a data source for row selection and convert it into aoData points for the DT
	 *   @param {*} src Can be a single DOM TR node, an array of TR nodes (including a
	 *     a jQuery object), a single aoData point from DataTables, an array of aoData
	 *     points or an array of aoData indexes
	 *   @returns {array} An array of aoData points
	 */
	"_fnSelectData": function(src){
	    
		var out = [];
		var pos, i;

		// Single node
		if(src.nodeName){
		    			
			pos = this.s.dt.oInstance.fnGetPosition(src);
			out.push(this.s.dt.aoData[pos]);			
		}
		
		// jQuery object or an array of nodes, or aoData points
		else if( typeof src.length !== 'undefined' ){
		    						
			var iLen = src.length;  // Caching to prevent .length() running on each loop iteration
			
			for(i=0; i < iLen; i++){
			    
				if(src[i].nodeName){
				    
					pos = this.s.dt.oInstance.fnGetPosition(src[i]);
					out.push(this.s.dt.aoData[pos]);
				}
				else if( typeof src[i] === 'number' ){
				    
					out.push(this.s.dt.aoData[ src[i] ]);
				}
				else {
					out.push(src[i]);
				}
			}
		}
		
		// A single aoData point
		else {			
			out.push(src);
		}

		return out;
		
	},		
	
	/**
	 * Configure a text based button for interaction events
	 *  @method  _fnTextConfig
	 *  @param   {Node} nButton Button element which is being considered
	 *  @param   {Object} oConfig Button configuration object
	 *  @returns void
	 *  @private 
	 */
	"_fnTextConfig": function(nButton, oConfig){
	    
		var parent = this;
		
		if( oConfig.fnInit !== null ){
		    
			oConfig.fnInit.call(this, nButton, oConfig);
		}
		
		if( oConfig.sToolTip !== "" ){
		    
			nButton.title = oConfig.sToolTip;
		}
		
		$(nButton).hover(   function (){
		    
					    if( oConfig.fnMouseover !== null ){

						    oConfig.fnMouseover.call(this, nButton, oConfig, null);
					    }
			
				    }, 
				    function (){
					
					    if( oConfig.fnMouseout !== null ){
						
						    oConfig.fnMouseout.call(this, nButton, oConfig, null);
					    }
				    } 
		);
		
		if( oConfig.fnSelect !== null ){
		    
			TableToolsPlus._fnEventListen( this, 'select', function(n){
			    
				oConfig.fnSelect.call( parent, nButton, oConfig, n );
			} );
		}
		
		$(nButton).click(   function(e){
		    
					    //e.preventDefault();

					    if( oConfig.fnClick !== null ){
						
						    oConfig.fnClick.call( parent, nButton, oConfig, null );
					    }

					    // Provide a complete function to match the behaviour of the flash elements
					    
					    if( oConfig.fnComplete !== null ){
						
						    oConfig.fnComplete.call( parent, nButton, oConfig, null, null );
					    }

					    parent._fnCollectionHide( nButton, oConfig );
				    }
		);
		    
	},
	
	
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Data retrieval functions
	 */
	
	/**
	 * Convert the mixed columns variable into a boolean array the same size as the columns, which
	 * indicates which columns we want to include
	 *  @method  _fnColumnTargets
	 *  @param   {String|Array} mColumns The columns to be included in data retrieval. If a string
	 *			 then it can take the value of "visible" or "hidden" (to include all visible or
	 *			 hidden columns respectively). Or an array of column indexes
	 *  @returns {Array} A boolean array the length of the columns of the table, which each value
	 *			 indicating if the column is to be included or not
	 *  @private 
	 */
	"_fnColumnTargets": function(mColumns){
	    
		var aColumns = [];
		var dt = this.s.dt;
		var i;
		var sColumnsType;
		
		if( typeof mColumns == "object" ){
		    
			sColumnsType = "object";
		}
		else {
			sColumnsType = mColumns;
		}
		
		var iLen = dt.aoColumns.length;  // Caching to prevent .length() running on each loop iteration
		
		
		switch(sColumnsType){

			case "object": { 

				for(i=0; i < iLen; i++){

					aColumns.push(false);
				}

				iLen = mColumns.length;

				for(i=0; i < iLen; i++){

					aColumns[ mColumns[i] ] = true;
				}

			} break;
			
			case "visible": {
				
				for(i=0; i < iLen; i++){

					aColumns.push(dt.aoColumns[i].bVisible);
				}

			} break;	
			
			case "hidden": {
				
				for(i=0; i < iLen; i++){

					aColumns.push(!dt.aoColumns[i].bVisible);  // Note the ! operator
				}

			} break;

			case "sortable": {
				
				for(i=0; i < iLen; i++){

					aColumns.push(dt.aoColumns[i].bSortable);
				}

			} break;
			
			default : {
				
				for(i=0; i < iLen; i++){

					aColumns.push(true);
				}

			} break;			

		}		
		
		return aColumns;
		
	},
		
	/**
	 * New line character(s) depend on the platforms
	 *  @method  method
	 *  @param   {Object} oConfig Button configuration object - only interested in oConfig.sNewLine
	 *  @returns {String} Newline character
	 */
	"_fnNewline": function(oConfig){
	    
	    
		if( oConfig.sNewLine == "auto" ){
		    
			return navigator.userAgent.match(/Windows/) ? "\r\n" : "\n";
		}
		else {
			return oConfig.sNewLine;
		}
		
	},	
	
	/**
	 * Get data from DataTables' internals and format it for output
	 *  @method  _fnGetDataTablesData
	 *  @param   {Object} oConfig Button configuration object
	 *  @param   {String} oConfig.sFieldBoundary Field boundary for the data cells in the string
	 *  @param   {String} oConfig.sFieldSeperator Field separator for the data cells
	 *  @param   {String} oConfig.sNewline New line options
	 *  @param   {Mixed} oConfig.mColumns Which columns should be included in the output
	 *  @param   {Boolean} oConfig.bHeader Include the header
	 *  @param   {Boolean} oConfig.bFooter Include the footer
	 *  @param   {Boolean} oConfig.bSelectedOnly Include only the selected rows in the output
	 *  @returns {String} Concatenated string of data
	 *  @private 
	 */
	"_fnGetDataTablesData": function(oConfig){
	    
		
		var aRow, aData=[], sLoopData='', arr;
		var dt = this.s.dt, tr;
		var regex = new RegExp(oConfig.sFieldBoundary, "g"); // Do it here for speed 
		var aColumnsInc = this._fnColumnTargets( oConfig.mColumns );
		
		var bSelectedOnly = (typeof oConfig.bSelectedOnly != 'undefined') ? oConfig.bSelectedOnly : false;
		
		var i, j, iLenA, iLenB;
		
		// Header
		// ===================================================================

		if(oConfig.bHeader){
		    
			aRow = [];			
			iLenA = dt.aoColumns.length; // Caching to prevent .length() running on each loop iteration
			
			for(i=0; i < iLenA; i++){
			    
				if(aColumnsInc[i]){
				    
					sLoopData = dt.aoColumns[i].sTitle.replace(/\n/g," ").replace( /<.*?>/g, "" ).replace(/^\s+|\s+$/g,"");
					sLoopData = this._fnHtmlDecode( sLoopData );
					
					aRow.push( this._fnBoundData( sLoopData, oConfig.sFieldBoundary, regex ) );
				}
			}

			aData.push( aRow.join(oConfig.sFieldSeperator) );
		}
		
		// Body
		// ===================================================================
		
		var aDataIndex = dt.aiDisplay;
		var aSelected = this.fnGetSelected();
		
		if( this.s.select.type !== "none" && bSelectedOnly && aSelected.length !== 0 ){
		    
			aDataIndex = [];			
			iLenA = aSelected.length;
			
			for(i=0; i < iLenA; i++){
			    
				aDataIndex.push( dt.oInstance.fnGetPosition( aSelected[i] ) );
			}
		}
		
		iLenB = aDataIndex.length;
		iLenA = dt.aoColumns.length;		
		
		for(j=0; j < iLenB; j++){
		    
			tr = dt.aoData[ aDataIndex[j] ].nTr;
			aRow = [];						
			
			// Columns 
			
			for(i=0; i < iLenA; i++){
			    
				if(aColumnsInc[i]){
				    
					// Convert to strings (with small optimisation) 
					
					var mTypeData = dt.oApi._fnGetCellData( dt, aDataIndex[j], i, 'display' );
					
					if(oConfig.fnCellRender){
					    
						sLoopData = oConfig.fnCellRender( mTypeData, i, tr, aDataIndex[j] )+"";
						
					}
					else if( typeof mTypeData == "string" ){
					    
						// Strip newlines, replace img tags with alt attr. and finally strip html.
						
						sLoopData = mTypeData.replace(/\n/g," ");
						sLoopData =
						 	sLoopData.replace(/<img.*?\s+alt\s*=\s*(?:"([^"]+)"|'([^']+)'|([^\s>]+)).*?>/gi,
						 		'$1$2$3');
						sLoopData = sLoopData.replace( /<.*?>/g, "" );
						
					}
					else {					    
						sLoopData = mTypeData+"";
					}
					
					// Trim and clean the data 
					sLoopData = sLoopData.replace(/^\s+/, '').replace(/\s+$/, '');
					sLoopData = this._fnHtmlDecode( sLoopData );
					
					// Bound it and add it to the total data
					aRow.push( this._fnBoundData(sLoopData, oConfig.sFieldBoundary, regex) );
					
				}
			}
      
			aData.push( aRow.join(oConfig.sFieldSeperator) );
      
			// Details rows from fnOpen
			
			if(oConfig.bOpenRows){
			    
				arr = $.grep(dt.aoOpenRows, function(o) { return o.nParent === tr; });
				
				if( arr.length === 1 ){
				    
					sLoopData = this._fnBoundData( $('td', arr[0].nTr).html(), oConfig.sFieldBoundary, regex );
					aData.push( sLoopData );
				}
			}
		}
		
		// Footer
		// ===================================================================
		
		if( oConfig.bFooter && dt.nTFoot !== null ){
		    
			aRow = [];
			iLenA = dt.aoColumns.length;
			
			for(i=0; i < iLenA; i++){
			    
				if( aColumnsInc[i] && (dt.aoColumns[i].nTf !== null) ){
				    
					sLoopData = dt.aoColumns[i].nTf.innerHTML.replace(/\n/g," ").replace( /<.*?>/g, "" );
					sLoopData = this._fnHtmlDecode( sLoopData );
					
					aRow.push( this._fnBoundData( sLoopData, oConfig.sFieldBoundary, regex ) );
				}
			}
			
			aData.push( aRow.join(oConfig.sFieldSeperator) );
		}		
		
		
		return aData.join( this._fnNewline(oConfig) );
		
	},
		
	/**
	 * Wrap data up with a boundary string
	 *  @method  _fnBoundData
	 *  @param   {String} sData data to bound
	 *  @param   {String} sBoundary bounding char(s)
	 *  @param   {RegExp} regex search for the bounding chars - constructed outside for efficiency
	 *			 in the loop
	 *  @returns {String} bound data
	 *  @private 
	 */
	"_fnBoundData": function(sData, sBoundary, regex){
	    
	    
		if( sBoundary === "" ){
		    
			return sData;
		}
		else {
			return sBoundary + sData.replace(regex, sBoundary+sBoundary) + sBoundary;
		}
		
	},	
	
	/**
	 * Break a string up into an array of smaller strings
	 *  @method  _fnChunkData
	 *  @param   {String} sData data to be broken up
	 *  @param   {Int} iSize chunk size
	 *  @returns {Array} String array of broken up text
	 *  @private 
	 */
	"_fnChunkData": function(sData, iSize){
	    
	    
		var asReturn = [];
		var iLen = sData.length;  // Caching to prevent .length() running on each loop iteration
		
		for(var i=0; i < iLen; i += iSize){
		    
			if( (i + iSize) < iLen ){
			    
				asReturn.push( sData.substring(i, i + iSize) );
			}
			else {
				asReturn.push( sData.substring(i, iLen) );
			}
		}
		
		return asReturn;
		
	},
		
	/**
	 * Decode HTML entities
	 *  @method  _fnHtmlDecode
	 *  @param   {String} sData encoded string
	 *  @returns {String} decoded string
	 *  @private 
	 */
	"_fnHtmlDecode": function(sData){
	    
	    
		if( sData.indexOf('&') === -1 ){
		    
			return sData;
		}
		
		var n = document.createElement('div');		
		
		var result = sData.replace( /&([^\s]*);/g, 
					    function( match, match2 ) {
		    
						    if( match.substr(1, 1) === '#' ){
							
							    return String.fromCharCode( Number(match2.substr(1)) );
						    }
						    else {
							    n.innerHTML = match;
							    
							    return n.childNodes[0].nodeValue;
						    }
					    } 
		);
		
		return result;
		
	},
			
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Printing functions
	 */
	
	/**
	 * Show print display
	 *  @method  _fnPrintStart
	 *  @param   {Event} e Event object
	 *  @param   {Object} oConfig Button configuration object
	 *  @returns void
	 *  @private 
	 */
	"_fnPrintStart": function(oConfig){
	    
		var parent = this;
		var oSetDT = this.s.dt;
	  
		// Parse through the DOM hiding everything that isn't needed for the table
		this._fnPrintHideNodes(oSetDT.nTable);
		
		// Show the whole table
		this.s.print.saveStart = oSetDT._iDisplayStart;
		this.s.print.saveLength = oSetDT._iDisplayLength;

		if(oConfig.bShowAll){
		    
			oSetDT._iDisplayStart = 0;
			oSetDT._iDisplayLength = -1;
			oSetDT.oApi._fnCalculateEnd(oSetDT);
			oSetDT.oApi._fnDraw(oSetDT);
		}
		
		// Adjust the display for scrolling which might be done by DataTables
		
		if( (oSetDT.oScroll.sX !== "") || (oSetDT.oScroll.sY !== "") ){
		    
			this._fnPrintScrollStart(oSetDT);

			// If the table redraws while in print view, the DataTables scrolling
			// setup would hide the header, so we need to readd it on draw
			
			$(this.s.dt.nTable).bind('draw.DTTT_Print', function () {
				parent._fnPrintScrollStart(oSetDT);
			} );
		}
		
		// Remove the other DataTables feature nodes - but leave the table and info div
		var anFeature = oSetDT.aanFeatures;
		
		for( var cFeature in anFeature ){
		    
			if( (cFeature != 'i') && (cFeature != 't') && (cFeature.length == 1) ){
			    
				var iLen = anFeature[cFeature].length; // Caching to prevent .length() running on each loop iteration
			    
				for(var i=0; i < iLen; i++){
				    
					this.dom.print.hidden.push( {
						"node": anFeature[cFeature][i],
						"display": "block"
					} );
					
					anFeature[cFeature][i].style.display = "none";
				}
			}
		}
		
		// Print class can be used for styling
		$(document.body).addClass(this.classes.print.body);

		// Show information message to let the user know what is happening
		if( oConfig.sInfo !== "" ){
		    
			this.fnInfo(oConfig.sInfo, 3000);
		}

		// Add a message at the top of the page
		if(oConfig.sMessage){
		    
			this.dom.print.message = document.createElement( "div" );
			this.dom.print.message.className = this.classes.print.message;
			this.dom.print.message.innerHTML = oConfig.sMessage;
			
			document.body.insertBefore( this.dom.print.message, document.body.childNodes[0] );
		}
		
		// Cache the scrolling and the jump to the top of the page
		this.s.print.saveScroll = $(window).scrollTop();
		window.scrollTo( 0, 0 );

		// Bind a key event listener to the document for the escape key - it is removed in the callback

		$(document).bind( "keydown.DTTT", function(e) {
		    
			// Only interested in the escape key
			if( e.keyCode == 27 ){
			    
				e.preventDefault();
				parent._fnPrintEnd.call( parent, e );
			}
		} );
		
	},
		
	/**
	 * Printing is finished, resume normal display
	 *  @method  _fnPrintEnd
	 *  @param   {Event} e Event object
	 *  @returns void
	 *  @private 
	 */
	"_fnPrintEnd": function(e){
	    
		var oSetDT = this.s.dt;
		var oSetPrint = this.s.print;
		var oDomPrint = this.dom.print;
		
		// Show all hidden nodes 
		this._fnPrintShowNodes();
		
		// Restore DataTables' scrolling 
		
		if( (oSetDT.oScroll.sX !== "") || (oSetDT.oScroll.sY !== "") ){
		    
			$(this.s.dt.nTable).unbind('draw.DTTT_Print');

			this._fnPrintScrollEnd();
		}
		
		// Restore the scroll
		
		window.scrollTo( 0, oSetPrint.saveScroll );
		
		// Drop the print message 
		
		if( oDomPrint.message !== null ){
		    
			document.body.removeChild( oDomPrint.message );
			oDomPrint.message = null;
		}
		
		// Styling class 
		
		$(document.body).removeClass( 'DTTT_Print' );
		
		// Restore the table length
		
		oSetDT._iDisplayStart = oSetPrint.saveStart;
		oSetDT._iDisplayLength = oSetPrint.saveLength;
		oSetDT.oApi._fnCalculateEnd( oSetDT );
		oSetDT.oApi._fnDraw( oSetDT );
		
		$(document).unbind( "keydown.DTTT" );
		
	},
		
	/**
	 * Take account of scrolling in DataTables by showing the full table
	 *  @returns void
	 *  @private 
	 */
	"_fnPrintScrollStart": function ()
	{
		 
		var oSetDT = this.s.dt;
		var nScrollHeadInner = oSetDT.nScrollHead.getElementsByTagName('div')[0];
		var nScrollBody = oSetDT.nTable.parentNode;

		/* Copy the header in the thead in the body table, this way we show one single table when
		 * in print view. Note parent this section of code is more or less verbatim from DT 1.7.0
		 */
		var nTheadSize = oSetDT.nTable.getElementsByTagName('thead');
		
		if( nTheadSize.length > 0 ){
		    
			oSetDT.nTable.removeChild( nTheadSize[0] );
		}
		
		if( oSetDT.nTFoot !== null ){
		    
			var nTfootSize = oSetDT.nTable.getElementsByTagName('tfoot');
			
			if( nTfootSize.length > 0 ){
			    
				oSetDT.nTable.removeChild( nTfootSize[0] );
			}
		}
		
		nTheadSize = oSetDT.nTHead.cloneNode(true);
		oSetDT.nTable.insertBefore( nTheadSize, oSetDT.nTable.childNodes[0] );
		
		if( oSetDT.nTFoot !== null ){
		    
			nTfootSize = oSetDT.nTFoot.cloneNode(true);
			oSetDT.nTable.insertBefore( nTfootSize, oSetDT.nTable.childNodes[1] );
		}
		
		// Now adjust the table's viewport so we can actually see it
		
		if( oSetDT.oScroll.sX !== "" ){
		    
			oSetDT.nTable.style.width = $(oSetDT.nTable).outerWidth()+"px";
			nScrollBody.style.width = $(oSetDT.nTable).outerWidth()+"px";
			nScrollBody.style.overflow = "visible";
		}
		
		if( oSetDT.oScroll.sY !== "" ){
		    
			nScrollBody.style.height = $(oSetDT.nTable).outerHeight()+"px";
			nScrollBody.style.overflow = "visible";
		}
		
	},
		
	/**
	 * Take account of scrolling in DataTables by showing the full table. Note parent the redraw of
	 * the DataTable parent we do will actually deal with the majority of the hard work here
	 *  @returns void
	 *  @private 
	 */
	"_fnPrintScrollEnd": function(){
	    
		 
		var oSetDT = this.s.dt;
		var nScrollBody = oSetDT.nTable.parentNode;
		
		if( oSetDT.oScroll.sX !== "" ){
		    
			nScrollBody.style.width = oSetDT.oApi._fnStringToCss( oSetDT.oScroll.sX );
			nScrollBody.style.overflow = "auto";
		}
		
		if( oSetDT.oScroll.sY !== "" ){
		    
			nScrollBody.style.height = oSetDT.oApi._fnStringToCss( oSetDT.oScroll.sY );
			nScrollBody.style.overflow = "auto";
		}
		
	},	
	
	/**
	 * Resume the display of all TableToolsPlus hidden nodes
	 *  @method  _fnPrintShowNodes
	 *  @returns void
	 *  @private 
	 */
	"_fnPrintShowNodes": function(){
	    
		var anHidden = this.dom.print.hidden;
		var iLen = anHidden.length;  // Caching to prevent .length() running on each loop iteration
	  
		for(var i=0; i < iLen; i++){
		    
			anHidden[i].node.style.display = anHidden[i].display;
		}
		
		anHidden.splice( 0, anHidden.length );
		
	},
	
	
	/**
	 * Hide nodes which are not needed in order to display the table. Note parent this function is
	 * recursive
	 *  @method  _fnPrintHideNodes
	 *  @param   {Node} nNode Element which should be showing in a 'print' display
	 *  @returns void
	 *  @private 
	 */
	"_fnPrintHideNodes": function(nNode){
	    
		var anHidden = this.dom.print.hidden;	  
		var nParent = nNode.parentNode;
		var nChildren = nParent.childNodes;
		
		var iLen = nChildren.length; // Caching to prevent .length() running on each loop iteration
		
		for(var i=0; i < iLen; i++){
		    
			if( nChildren[i] != nNode && nChildren[i].nodeType == 1 ){
			    
				/* If our node is shown (don't want to show nodes which were previously hidden) */
				var sDisplay = $(nChildren[i]).css("display");
				
			 	if( sDisplay != "none" ){
				    
					/* Cache the node and it's previous state so we can restore it */
					anHidden.push( {
						"node": nChildren[i],
						"display": sDisplay
					} );
					
					nChildren[i].style.display = "none";
				}
			}
		}
		
		if( nParent.nodeName != "BODY" ){
		    
			this._fnPrintHideNodes( nParent );
		}
		
	}
	
};



/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Static variables
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * Store of all instances that have been created of TableToolsPlus, so one can look up other (when
 * there is need of a master)
 *  @property _aInstances
 *  @type	 Array
 *  @default  []
 *  @private
 */
TableToolsPlus._aInstances = [];


/**
 * Store of all listeners and their callback functions
 *  @property _aListeners
 *  @type	 Array
 *  @default  []
 */
TableToolsPlus._aListeners = [];



/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Static methods
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * Get an array of all the master instances
 *  @method  fnGetMasters
 *  @returns {Array} List of master TableToolsPlus instances
 *  @static
 */
TableToolsPlus.fnGetMasters = function(){
    
	var a = [];
	var iLen = TableToolsPlus._aInstances.length; // Caching to prevent .length() running on each loop iteration
	
	for(var i=0; i < iLen; i++){
	    	    
		if(TableToolsPlus._aInstances[i].s.master){
		    
			a.push( TableToolsPlus._aInstances[i] );
		}
	}
	
	return a;
	
};

/**
 * Get the master instance for a table node (or id if a string is given)
 *  @method  fnGetInstance
 *  @returns {Object} ID of table OR table node, for which we want the TableToolsPlus instance
 *  @static
 */
TableToolsPlus.fnGetInstance = function(node){
    
    
	if( typeof node != 'object' ){
	    
		node = document.getElementById(node);
	}
	
	var iLen = TableToolsPlus._aInstances.length; // Caching to prevent .length() running on each loop iteration
	
	for(var i=0; i < iLen; i++){
	    
		if( TableToolsPlus._aInstances[i].s.master && (TableToolsPlus._aInstances[i].dom.table == node) ){
		    
			return TableToolsPlus._aInstances[i];
		}
	}
	
	return null;
	
};


/**
 * Add a listener for a specific event
 *  @method  _fnEventListen
 *  @param   {Object} parent Scope of the listening function (i.e. 'this' in the caller)
 *  @param   {String} type Event type
 *  @param   {Function} fn Function
 *  @returns void
 *  @private
 *  @static
 */
TableToolsPlus._fnEventListen = function(parent, type, fn){
    
	TableToolsPlus._aListeners.push( {
		"parent": parent,
		"type": type,
		"fn": fn
	} );
	
};
	

/**
 * An event has occurred - look up every listener and fire it off. We check parent the event we are
 * going to fire is attached to the same table (using the table node as reference) before firing
 *  @method  _fnEventDispatch
 *  @param   {Object} parent Scope of the listening function (i.e. 'this' in the caller)
 *  @param   {String} type Event type
 *  @param   {Node} node Element parent the event occurred on (may be null)
 *  @param   {boolean} [selected] Indicate if the node was selected (true) or deselected (false)
 *  @returns void
 *  @private
 *  @static
 */
TableToolsPlus._fnEventDispatch = function(parent, type, node, selected){
    
	var listeners = TableToolsPlus._aListeners;
	var iLen = listeners.length; // Caching to prevent .length() running on each loop iteration
	
	for(var i=0; i < iLen; i++){
	    
		if( (parent.dom.table == listeners[i].parent.dom.table) && (listeners[i].type == type) ){
		    
			listeners[i].fn( node, selected );
		}
	}
	
};


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Constants
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */



TableToolsPlus.buttonBase = {
	// Button base
	"sAction": "text",
	"sTag": "default",
	"sLinerTag": "default",
	"sButtonClass": "DTTT_button_text",
	"sButtonText": "Button text",
	"sTitle": "",
	"sToolTip": "",

	// Common button specific options
	"sCharSet": "utf8",
	"bBomInc": false,
	"sFileName": "*.csv",
	"sFieldBoundary": "",
	"sFieldSeperator": "\t",
	"sNewLine": "auto",
	"mColumns": "all", /* "all", "visible", "hidden" or array of column integers */
	"bHeader": true,
	"bFooter": true,
	"bOpenRows": false,
	"bSelectedOnly": false,

	// Callbacks
	"fnMouseover": null,
	"fnMouseout": null,
	"fnClick": null,
	"fnSelect": null,
	"fnComplete": null,
	"fnInit": null,
	"fnCellRender": null
};


/**
 * @namespace Default button configurations
 */
TableToolsPlus.BUTTONS = {

	"print": $.extend( {}, TableToolsPlus.buttonBase, {
	    
		"sInfo": "<h6>Print view</h6><p>Please use your browser's print function to "+
		  "print this table. Press escape when finished.",
		"sMessage": null,
		"bShowAll": true,
		"sToolTip": "View print view",
		"sButtonClass": "DTTT_button_print",
		"sButtonText": "Print",
		"fnClick": function(nButton, oConfig){
		    
			this.fnPrint(true, oConfig);
		}
	} ),

	"text": $.extend( {}, TableToolsPlus.buttonBase ),
	
	"edit": $.extend( {}, TableToolsPlus.buttonBase, {
	    
		"sButtonText": "Edit",
		"sToolTip": "Edit selected rows",		
		"fnClick": function(nButton, oConfig){
		    
			var selectedRowCount = this.fnGetSelected().length;
		    
			if( selectedRowCount !== 0 ){
			    
				var html_titleBar;

				html_titleBar  =  '<div class="fox_title_bar_icon">';
				html_titleBar  += '<div class="key_manager_icon"></div>';
				html_titleBar  += '</div>';	    
				html_titleBar  += '<div class="title_string">Editing ' + selectedRowCount + ' rows</div>';
				
				var html_form;
				
				html_form =  '<form><table class="form-table">';
				
				var iLen = this.s.custom.aoColumns.length; // Caching to prevent .length() running on each loop iteration
				
				for(var i=0; i < iLen; i++){

					if(this.s.custom.aoColumns[i].multiEdit == true){

						html_form += '<tr valign="top">';
						html_form += '<th align="left">' + this.s.custom.aoColumns[i].desc + '</th>';
						html_form += '<td><input type="text" name="' + i + '" id="' + i + '" class="text ui-widget-content ui-corner-all" /></td>';
						html_form += '</tr>';
					}
				}				
				
				html_form += '</fieldset></form>';			
				

				$(".modal_dialog").dialog({

					dialogClass:'fox_floating_palette',
					modal: true,
					autoOpen: true,
					resizable: false,					

					// In order to use the "fade" effect below, the script aliases "fox-jquery-effects-core"
					// and "fox-jquery-effects-fade" have to be included in the page. Other possible effects:
					// 'blind', 'clip', 'drop', 'explode', 'fold', 'puff', 'slide', 'scale', 'size', 'pulsate'

					show: {effect: "fade", duration: 200},
					hide: {effect: "fade", duration: 200},
					open: function(){

						// Because jquery.ui doesn't float the title text inside the title bar, and
						// has it set up as a <span> its just easier to remove it, then replace
						// it with our floating icon and floating title text

						$('.fox_floating_palette .ui-dialog-titlebar .ui-dialog-title').remove();

						// Remove our HTML (if it exists) to prevent multiple copies getting added
						// when the user closes the dialog and opens it again

						$('.fox_floating_palette .ui-dialog-titlebar .fox_title_bar_icon').remove();
						$('.fox_floating_palette .ui-dialog-titlebar .title_string').remove();

						$('.fox_floating_palette .ui-dialog-titlebar').prepend(html_titleBar);
						
						// Set the dialog contents to our HTML
						$('.modal_dialog').html(html_form);					

					},
					buttons: {
					    "Save": function() {
					    $( this ).dialog( "close" );
					    },
					    "Cancel": function() {
					    $( this ).dialog( "close" );
					    }
					},					
					height: 240,
					width: 360
				});	    
				
			}
		},
		"fnSelect": function(nButton, oConfig){
		    
			if( this.fnGetSelected().length !== 0 ){
			    
				$(nButton).removeClass(this.classes.buttons.disabled);
			}
			else {
				$(nButton).addClass(this.classes.buttons.disabled);
			}
			
		},
		"fnInit": function(nButton, oConfig){
		    
			$(nButton).addClass( this.classes.buttons.disabled );
			
		}		
	} ),	
	
	"delete": $.extend( {}, TableToolsPlus.buttonBase, {
	    
		"sButtonText": "Delete",
		"sToolTip": "Delete selected rows",		
		"fnClick": function(nButton, oConfig){		    		    
		    
			if( this.fnGetSelected().length !== 0 ){
			    
				var html_titleBar;

				html_titleBar  =  '<div class="fox_title_bar_icon">';
				html_titleBar  += '<div class="key_manager_icon"></div>';
				html_titleBar  += '</div>';	    
				html_titleBar  += '<div class="title_string">Confirm key delete</div>';

				$(".modal_dialog").dialog({

					dialogClass:'fox_floating_palette',
					modal: true,
					autoOpen: true,
					resizable: false,					

					// In order to use the "fade" effect below, the script aliases "fox-jquery-effects-core"
					// and "fox-jquery-effects-fade" have to be included in the page. Other possible effects:
					// 'blind', 'clip', 'drop', 'explode', 'fold', 'puff', 'slide', 'scale', 'size', 'pulsate'

					show: {effect: "fade", duration: 200},
					hide: {effect: "fade", duration: 200},
					open: function(){

						// Because jquery.ui doesn't float the title text inside the title bar, and
						// has it set up as a <span> its just easier to remove it, then replace
						// it with our floating icon and floating title text

						$('.fox_floating_palette .ui-dialog-titlebar .ui-dialog-title').remove();

						// Remove our HTML (if it exists) to prevent multiple copies getting added
						// when the user closes the dialog and opens it again

						$('.fox_floating_palette .ui-dialog-titlebar .fox_title_bar_icon').remove();
						$('.fox_floating_palette .ui-dialog-titlebar .title_string').remove();

						$('.fox_floating_palette .ui-dialog-titlebar').prepend(html_titleBar);

					},
					buttons: {
					    "Delete keys": function() {
					    $( this ).dialog( "close" );
					    },
					    "Cancel": function() {
					    $( this ).dialog( "close" );
					    }
					},					
					height: 100,
					width: 200
				});	    
				
			}
		},
		"fnSelect": function(nButton, oConfig){
		    
			if( this.fnGetSelected().length !== 0 ){
			    
				$(nButton).removeClass(this.classes.buttons.disabled);
			}
			else {
				$(nButton).addClass(this.classes.buttons.disabled);
			}
		},
		"fnInit": function(nButton, oConfig){
		    
			$(nButton).addClass( this.classes.buttons.disabled );
			
		}		
	} ),	

	"ajax": $.extend( {}, TableToolsPlus.buttonBase, {
	    
		"sAjaxUrl": "/xhr.php",
		"sButtonText": "Ajax button",
		"fnClick": function(nButton, oConfig){
		    
			var sData = this.fnGetTableData(oConfig);
			
			$.ajax( {
				"url": oConfig.sAjaxUrl,
				"data": [
					{ "name": "tableData", "value": sData }
				],
				"success": oConfig.fnAjaxComplete,
				"dataType": "json",
				"type": "POST", 
				"cache": false,
				"error": function(){
				    
					alert( "Error detected when sending table data to server" );
				}
			} );
		},
		"fnAjaxComplete": function(json){
		    
			alert( 'Ajax complete' );
		}
	} ),

	"div": $.extend( {}, TableToolsPlus.buttonBase, {
		"sAction": "div",
		"sTag": "div",
		"sButtonClass": "DTTT_nonbutton",
		"sButtonText": "Text button"
	} ),

	"collection": $.extend( {}, TableToolsPlus.buttonBase, {
		"sAction": "collection",
		"sButtonClass": "DTTT_button_collection",
		"sButtonText": "Collection",
		"fnClick": function(nButton, oConfig){
		    
			this._fnCollectionShow(nButton, oConfig);
		}
	} )
};

/**
 * @namespace Classes used by TableToolsPlus - allows the styles to be override easily.
 *   Note parent when TableToolsPlus initialises it will take a copy of the classes object
 *   and will use its internal copy for the remainder of its run time.
 */
TableToolsPlus.classes = {
    
	"container": "DTTT_container",
	"buttons": {
		"normal": "DTTT_button",
		"disabled": "DTTT_disabled"
	},
	"collection": {
		"container": "DTTT_collection",
		"background": "DTTT_collection_background",
		"buttons": {
			"normal": "DTTT_button",
			"disabled": "DTTT_disabled"
		}
	},
	"select": {
		"table": "DTTT_selectable",
		"row": "DTTT_selected"
	},
	"print": {
		"body": "DTTT_Print",
		"info": "DTTT_print_info",
		"message": "DTTT_PrintMessage"
	}
};


/**
 * @namespace ThemeRoller classes - built in for compatibility with DataTables' 
 *   bJQueryUI option.
 */
TableToolsPlus.classes_themeroller = {
    
	"container": "DTTT_container ui-buttonset ui-buttonset-multi",
	"buttons": {
		"normal": "DTTT_button ui-button ui-state-default"
	},
	"collection": {
		"container": "DTTT_collection ui-buttonset ui-buttonset-multi"
	}
};


/**
 * @namespace TableToolsPlus default settings for initialisation
 */
TableToolsPlus.DEFAULTS = {
    
	"sSwfPath":        "media/swf/copy_csv_xls_pdf.swf",
	"sRowSelect":      "none",
	"sSelectedClass":  null,
	"fnPreRowSelect":  null,
	"fnRowSelected":   null,
	"fnRowDeselected": null,
	"aButtons":        [ "print" ],
	"oTags": {
		"container": "div",
		"button": "a", // We really want to use buttons here, but Firefox and IE ignore the
		                 // click on the Flash element in the button (but not mouse[in|out]).
		"liner": "span",
		"collection": {
			"container": "div",
			"button": "a",
			"liner": "span"
		}
	}
};


/**
 * Name of this class
 *  @constant CLASS
 *  @type	 String
 *  @default  TableToolsPlus
 */
TableToolsPlus.prototype.CLASS = "TableToolsPlus";


/**
 * TableToolsPlus version
 *  @constant  VERSION
 *  @type	  String
 *  @default   See code
 */
TableToolsPlus.VERSION = "2.1.4";
TableToolsPlus.prototype.VERSION = TableToolsPlus.VERSION;




/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Initialisation
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/*
 * Register a new feature with DataTables
 */

if( (typeof $.fn.dataTable == "function") 
    && (typeof $.fn.dataTableExt.fnVersionCheck == "function") 
    && $.fn.dataTableExt.fnVersionCheck('1.9.0') )
{
	$.fn.dataTableExt.aoFeatures.push( {
	    
		"fnInit": function(oDTSettings){
		    
			var oOpts = typeof oDTSettings.oInit.oTableToolsPlus != 'undefined' ? oDTSettings.oInit.oTableToolsPlus : {};			
			var oTT = new TableToolsPlus(oDTSettings.oInstance, oOpts);
			
			TableToolsPlus._aInstances.push( oTT );
			
			return oTT.dom.container;
			
		},
		"cFeature": "T",
		"sFeature": "TableToolsPlus"
	} );
}
else
{
	alert( "Warning: TableToolsPlus requires DataTables 1.9.0 or newer - www.datatables.net/download");
}

$.fn.DataTable.TableToolsPlus = TableToolsPlus;

})(jQuery, window, document);