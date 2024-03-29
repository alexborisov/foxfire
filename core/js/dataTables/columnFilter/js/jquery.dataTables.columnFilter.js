/**
 * @summary     Column Filter
 * @description Filters datatable columns by range or value, using local or remote data sources
 * @file        dataTables.columnFilter.js
 * @version     1.0
 * @author      Carl Roett, based on code by Allan Jardine and Jovan Popovic
 * @license     GPL v2 or BSD 3 point style
 * @contact     https://github.com/foxly
 */

var ColumnFilter; // Global scope for ColumnFilter 

(function($){


    ColumnFilter = function(oDT, oConfig)
    {

	    // Sanity check that we're a new instance 

	    if( !this.CLASS || this.CLASS != "ColumnFilter" )
	    {
		    alert( "Warning: ColumnFilter must be initialised with the keyword 'new'" );
		    return false;
	    }

	    if( !$.fn.dataTableExt.fnVersionCheck('1.9.0') )
	    {
		    alert( "Warning: ColumnFilter requires DataTables 1.9 or greater - www.datatables.net/download");
		    return false;
	    }


	    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	    * Public class methods
	    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	    /**
	    * Retrieve the config object from an instance
	    *  @method fnSettings
	    *  @returns {object} ColumnFilter settings object
	    */
	    this.fnSettings = function(){
		
		    return this._oConfig;
	    };

	    this._fnInit(oDT, oConfig);

	    return this;

    };


    ColumnFilter.prototype = {


	    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	    * Private class methods
	    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	   
	    /**
	    * Initializes the ColumnFilter instance
	    * 
	    *  @method _fnInit
	    *  @param {object} oDT | dataTable object
	    *  @param {object} oConfig | configuration object for ColumnFilter
	    *  @returns void
	    */
	    "_fnInit": function(oDT, oConfig){	    
						    		    

		    this._oConfig = {	// Note the _

			    /** 
			    * When true, ColumnFilter will log trace-level debugging info to the JavaScript
			    * console. Make sure this option is switched off for production.
			    *  @type     bool
			    *  @default  false
			    *  @static
			    */
			    "bTrace": false,

			    /** 
			    * Sets whether to place the filtering fields in the header or the footer of the table.
			    *  @type     string (enum) ["head" | "foot"]
			    *  @default  foot
			    *  @static
			    */
			    "sPlaceHolder": "foot",

			    /** 
			    * String used to separate the fields when rendering a pair of input fields
			    * for a column filtered by range 
			    *  @type     string
			    *  @default  "~"
			    *  @static
			    */
			    "sRangeSeparator": "~",

			    /** 
			    * Delay in milliseconds between when a user stops changing filtering field values,
			    * and when ColumnFilter updates the table based on the changed values. This setting
			    * prevents ColumnFilter from running unnecessary queries as a user types values into
			    * fields. In applications that require "search-as-you-type" functionality, set this
			    * value to around 50 milliseconds. 
			    * 
			    *  @see http://en.wikipedia.org/wiki/Hysteresis#User_interface_design
			    *  @type     int
			    *  @default  500
			    *  @static
			    */
			    "iHysteresis": 500,

			    /** 
			    * Array of the filter settings that will be applied on the columns
			    *  @type     array{object}
			    *  @default  null
			    *  @static
			    */
			    "aoColumns": null,

			    /** 
			    * Guard prefix prepended to the input fields that ColumnFilter adds to the table
			    *  @type     string
			    *  @default  "table_"
			    *  @static
			    */
			    "sTableId": "table_",
			    
			    "oLanguage": {
				
				    /**
				    * Help ribbon displayed to a user when they attempt to select a 'to' date which is
				    * before the 'from' date in a date-range filter construct
				    *  @type     string
				    *  @default  "Date must be before 'from' date"
				    *  @static
				    */				
			           "sWarningDateBefore": "Date must be before 'from' date",
				   
				    /**
				    * Help ribbon displayed to a user when they attempt to select a 'from' date which is
				    * after the 'to' date in a date-range filter construct
				    *  @type     string
				    *  @default  "Date must be after 'to' date"
				    *  @static
				    */					   
				   "sWarningDateAfter": "Date must be after 'to' date"
				   
			    }			    

		    } 		    
		    
		    if(oConfig == null){    // Prevent the 'in' constructs from crashing
					    // on a null oConfig object			
			    oConfig = {};
		    }
		    
		    if(oConfig.oLanguage == null){	// Prevent the 'in' constructs from crashing
							// on a null oConfig.oLanguage object			
			    oConfig.oLanguage = {};
		    }		    
		    
		    
		    // Merge ColumnFilter's default config options with any config options that are passed
		    // in during instantiation. 
		    // ===================================================================================
		    
		    if("bTrace" in oConfig){ this._oConfig.bTrace = oConfig.bTrace;}
		    if("sPlaceHolder" in oConfig){ this._oConfig.sPlaceHolder = oConfig.sPlaceHolder;}
		    if("sRangeSeparator" in oConfig){ this._oConfig.sRangeSeparator = oConfig.sRangeSeparator;}
		    if("iHysteresis" in oConfig){ this._oConfig.iHysteresis = oConfig.iHysteresis;}
		    if("aoColumns" in oConfig){ this._oConfig.aoColumns = oConfig.aoColumns;}
		    if("sTableId" in oConfig){ this._oConfig.sTableId = oConfig.sTableId;}
		    
		    if("sWarningDateBefore" in oConfig.oLanguage){ 
			this._oConfig.oLanguage.sWarningDateBefore = oConfig.oLanguage.sWarningDateBefore;
		    }		    
		    if("sWarningDateAfter" in oConfig.oLanguage){ 
			this._oConfig.oLanguage.sWarningDateAfter = oConfig.oLanguage.sWarningDateAfter;
		    }		    
		    
		    
		    // Set up private variables
		    // ===================================================================================
		    
		    this._oDataTable = oDT;			    // DataTable object that this ColumnFilter instance is attached to

		    this._aiCustomSearch_Indexes = new Array();	    // Array of column indices that need to be intercepted and modified
								    // during the search string build process
								    		    	
		    this._asInitVals = new Array();		    // Hinting-text strings that appear inside the filter fields 
								    // before a user clicks on them

		    this._iTimerID = null;			    // ID of timer function used to add hysteresis to input fields
		    this._bUpdateQueued = false;		    // True if the _fnQueueUpdate has scheduled an update


		    // Update the table
		    // ===================================================================================
		    
		    this._fnRender();	

	    },

	    /**
	    * Renders the filter fields, adding them to the dataTable instance
	    * 
	    *  @method _fnProcess
	    *  @returns void
	    */
	    "_fnRender": function(){	  
			
		    var parent = this;
		    
		    var sFilterRow = "tfoot tr";
		    var j, k = 0;

		    // Determine whether we're attaching to the top or the bottom of the table, then
		    // build a jQuery selector string based on the attach point.
		    // ===================================================================================

		    if(this._oConfig.sPlaceHolder == "head:after"){

			    sFilterRow = "thead tr:last";
		    } 
		    else if(this._oConfig.sPlaceHolder == "head:before"){

			    var tr = $("thead tr:last").detach();

			    tr.prependTo("thead");
			    sFilterRow = "thead tr:first";
		    }

		    // Select the target table row using jQuery, then iterate over each object inside the 
		    // object that jQuery returns. 
		    // ===================================================================================		
		    
		    $(sFilterRow + " th", this._oDataTable).each( function(index){

			    if(parent._oConfig.bTrace){ console.log({
								    method:'_fnRender',
								    action: 'Processing Column',
								    data: index
								});
			    }

			    // If custom properties are passed in options.aoColumns, transcribe them
			    // to the column that we're currently iterating through. Otherwise use
			    // default values. NOTE: since we're inside a lambda function, we have to 
			    // access outside variables through the 'parent.' variable.

			    var aoColumn = null;

			    if(parent._oConfig.aoColumns == null){

				    aoColumn = { 
						    type: "text",
						    bRegex: false,
						    bSmart: true
				    };    
			    }
			    else {
				    
				    if(parent._oConfig.aoColumns.length < index){

					    return;
				    }
				    else if(parent._oConfig.aoColumns[index] == null){

					    return;
				    }
				    else {
					    aoColumn = parent._oConfig.aoColumns[index];
				    }
			    }

			    // Fetch the 'label' and 'th' parameters from the current node that jQuery is
			    // iterating through. Note that when using the jQuery(selector).each() operator,  
			    // jQuery sets 'this' to the context of the element its currently iterating over,
			    // which is different behavior than the jQuery.each() operator.		

			    var label = $(this).text();
			    var th = $($(this)[0]);

			    // Based on column type, run the correct generator function
			    // =====================================================================

			    switch(aoColumn.type){

				    case "number": {

					    parent._fnCreateInput({						
								    index: index,
								    label: label,
								    th: th,
								    bIsNumber: true
					    });
					    
				    } break;

				    case "text": {

					    parent._fnCreateInput({						
								    index: index,
								    label: label,
								    th: th,								    
								    bIsNumber: false
					    });					    
					    
				    } break;

				    case "select": {

					    parent._fnCreateSelect({
								    index: index, 
								    label: label,
								    th: th,								    
								    aData: aoColumn.values
					    });
					    
				    } break;

				    case "number-range": {

					    parent._fnCreateRangeInput({
								    index: index,
								    label: label,
								    th: th								    
					    });
					    
				    } break;

				    case "date-range": {

					    parent._fnCreateDateRangeInput({
								    index: index,
								    label: label,
								    th: th								    
					    });					    
					    
				    } break;

				    default: {

					    alert( "Warning: ColumnFilter passed unknown column type '" + aoColumn.type + "', index '" + index + "'");
					    
				    } break;


			    }   // ENDOF: switch(aoColumn.type){


		    });	// ENDOF: $(sFilterRow + " th", this._oDataTable).each( function(index)



		    // If dataTables is using server-side processing, override its fnServerData()
		    // function with our own fnServerData() that includes column filtering.
		    // ===================================================================================			

		    if(this._oDataTable.fnSettings().oFeatures.bServerSide){

			    if(this._oConfig.bTrace){ console.log({
								    method:'_fnRender',
								    action: 'This instance is using a remote data source'
								});
			    }			    
			    
			    // Clone the original fnServerData function
			    var fnServerDataOriginal = this._oDataTable.fnSettings().fnServerData;
			    
			    // Then replace it with our own function			    
			    this._oDataTable.fnSettings().fnServerData = function(sUrl, aoData, fnCallback, oSettings){
				    				    
				    // Iterate through each of the columns that the generator functions flagged as
				    // requiring a complex index type.

				    for(j=0; j < parent._aiCustomSearch_Indexes.length; j++){
										
					    // Search through the entire 'aoData' array to get the index of the
					    // sSearch_ + 'column_index' key that the search string is sent to the
					    // server in. We have to do a full array search, because there's no 
					    // guarantee the key will be at a specific offset in the array.

					    var index = parent._aiCustomSearch_Indexes[j];

					    for(k=0; k < aoData.length; k++){

						    // Once the key is found, overwrite it with our custom search string
						    
						    if( aoData[k].name == ("sSearch_" + index) ){
							    	
							    var result = null;
							    var fieldType = parent._oConfig.aoColumns[index].type;
							    
							    switch(fieldType){
								
								    case "number": {

									    result = parent._fnProcessNumberField(index);
									    break;
								    }

								    case "text": {

									    result = parent._fnProcessTextField(index);					    
									    break;
								    }

								    case "select": {

									    result = parent._fnProcessSelectField(index);
									    break;
								    }	
								    
								    case 'number-range': {
									    
									    result = parent._fnProcessNumberRangeField(index);
									    break;
								    }								    
								    
								    case 'date-range':	{									    									    									    

									    result = parent._fnProcessDateRangeField(index);	
									    break;									    
								    }
								    								    
								    
							    } // ENDOF: switch(parent._oConfig.aoColumns[index])
							    
							    // Overwrite the column's value in the aoData array, which will cause our modified
							    // value to be posted to the server in the AJAX request
							    
							    if(parent._oConfig.bTrace){ console.log({
												    method:'_fnRender',
												    action: 'Overwriting search string',
												    data: {
													    name: "sSearch_" + index,
													    fieldType: fieldType,
													    old_val: aoData[k].value,
													    new_val: result
												    }
												});
							    }								    
							    
							    aoData[k].value = result;
							    
						    }
					    }
				    }				   

				    aoData.push({ "name": "sRangeSeparator", "value": parent._oConfig.sRangeSeparator });
				    

				    // If the dataTables fnServerData() function was already overridden, pass our modified
				    // parameters through to the existing function. 
				    
				    if(fnServerDataOriginal != null){
							
					    if(parent._oConfig.bTrace){ console.log({
										    method:'_fnRender',
										    action: 'Table has an existing remote data function',
										    data: {
											    sUrl: sUrl,
											    aoData: aoData,
											    fnCallback: fnCallback,
											    oSettings: oSettings
										    }
										});
					    }	
							    
					    fnServerDataOriginal(sUrl, aoData, fnCallback, oSettings);
				    }
				    
				    // Otherwise, run the standard getJSON() function.
				    // TODO: Is this necessary?
					
				    else {  
					
					    if(parent._oConfig.bTrace){ console.log({
										    method:'_fnRender',
										    action: 'Table has no existing remote data function',
										    data: {
											    sUrl: sUrl,
											    aoData: aoData
										    }
										});
					    }
					    
					    $.getJSON(
							sUrl, 
							aoData, 
							function(json){
									parent._oDataTable.fnSettings().fnCallback(json)
							}
					    );
				    }				   				    
				  
				   
			    };	// ENDOF: function (sUrl, aoData, fnCallback, oSettings)


		    }	// ENDOF: if(this._oDataTable.fnSettings().oFeatures.bServerSide)
		    

	    },

	    /**
	    * Creates a single-variable input field and adds it to the target table
	    * 
	    *  @method _fnCreateInput
	    *  
	    *  @param {{index: int, label: string, th: object, bIsNumber, bool}} args | Control args
	    *	=> VAL int index | the index of the field, starting at the LHS of the table
	    *	=> VAL string label | the input field's label
	    *	=> VAL object th | jQuery object containing the first object in the table header
	    *	=> VAL bool bIsNumber | treat search string as a number
	    *	
	    *  @returns void
	    */
	    "_fnCreateInput": function(args){
			
			
		    var parent = this;
		    
		    // Create a text input field with the correct filtering class, and
		    // add it to the table using jQuery's "raw HTML" mode.
		    // ==============================================================================
		    
		    var sCSSClass = args.bIsNumber ? "number_filter" : "text_filter";
		    
		    var sFieldId = this._oConfig.sTableId + args.index;	
		    
		    var input = $('<input type="text" class="search_init ' + sCSSClass + '" id="' + sFieldId + '" value="' + args.label + '"/>');

		    // Replace ALL of the HTML within the element, clearing the heading added
		    // by dataTables in the process
		    
		    args.th.html(input);
		    		    
		    // Apply the substring-search CSS class (filter_text) to text fields, and the
		    // exact-match-search CSS class (filter_number) to number fields
		    
		    if(args.bIsNumber){

			    args.th.wrapInner('<span class="filter_column filter_number" />');
		    }
		    else {
			    args.th.wrapInner('<span class="filter_column filter_text" />');
		    }

		    // Add the column's hinting text
		    this._asInitVals[args.index] = args.label;
		    
		    // Flag the column for processing
		    
		    if(parent._oConfig.bTrace){ console.log({
							    method:'_fnCreateInput',
							    action: 'Adding custom search key',
							    data: {
								    index: args.index
							    }
							});
		    }	
			    
		    this._aiCustomSearch_Indexes.push(args.index);		    


		    // Replace the column's DOM filter function with our own version
		    // ==============================================================================
		    
		    this._oDataTable.dataTableExt.afnFiltering.push( function(oSettings, aData, iDataIndex){
			
			    var searchVal = document.getElementById(sFieldId).value;
			    var value = aData[args.index] == "-" ? "" : aData[args.index];
			    
			    var result = false;
			    
			    if(value == searchVal){

				    result = true;
			    }
			    
			    if(parent._oConfig.bTrace){ console.log({
								    method:'_fnCreateInput',
								    action: 'Filter function',
								    data: {
									    oSettings: oSettings,
									    aData: aData,
									    searchVal: searchVal,
									    value: value,
									    result: result
								    }
								});
			    }	
			    
			    return result;
				
		    });	
		    

		    // When a user changes the contents of a field, update the table		    			
		    input.keyup( function(){

			    if(parent._oConfig.bTrace){ console.log({
								    method:'_fnCreateInput',
								    action: 'input.keyup fired',
								    data: {
									    mode: 'exact',
									    value: this.value,
									    index: args.index
								    }
								});
			    }

			    parent._fnQueueUpdate();
			    
		    });
	    		    		    

		    // When a user clicks in to an empty field, clear the hinting text
		    input.focus( function(){

			    if( $(this).hasClass("search_init") ){

				    $(this).removeClass("search_init");
				    this.value = "";
			    }
		    });

		    // When a user clicks out of an empty field, add back the hinting text
		    input.blur( function(){

			    if(this.value == ""){

				    $(this).addClass("search_init");
				    this.value = parent._asInitVals[args.index];
			    }
		    });

	    },

	    /**
	    * Creates a two-variable range input field for numeric data types, and adds it to the target table
	    * 
	    *  @method _fnCreateRangeInput
	    *  
	    *  @param {{index: int, label: string, th: object}} args | Control args
	    *	=> VAL int index | the index of the field, starting at the LHS of the table
	    *	=> VAL string label | the input field's label
	    *	=> VAL object th | jQuery object containing the first object in the table header
	    *	
	    *  @returns void
	    */
	    "_fnCreateRangeInput": function(args){	    
		    
		    
		    var parent = this;
		    
		    // Create two text input fields with the correct filtering class, and
		    // add them to the table using jQuery's "raw HTML" mode.
		    // ==============================================================================		    

		    var sFromId = this._oConfig.sTableId + 'range_from_' + args.index;
		    var from = $('<input type="text" class="number_range_filter search_init" id="' + sFromId + '" rel="' + args.index + '" value="' + parent._oConfig.aoColumns[args.index].oRangeFormat.from + '"/>');

		    args.th.html(from);

		    var sToId = this._oConfig.sTableId + 'range_to_' + args.index;
		    var to = $('<input type="text" class="number_range_filter search_init" id="' + sToId + '" rel="' + args.index + '" value="' + parent._oConfig.aoColumns[args.index].oRangeFormat.to + '"/>');

		    args.th.append(to);
		    
		    args.th.wrapInner('<span class="filterColumn filter_number_range" />');
		    
		    // Flag the column for processing
		    // ==============================================================================		    
		    
		    if(parent._oConfig.bTrace){ console.log({
							    method:'_fnCreateRangeInput',
							    action: 'Adding custom search key',
							    data: {
								    index: args.index
							    }
							});
		    }		
		    
		    this._aiCustomSearch_Indexes.push(args.index);  

		    
		    // Replace the column's DOM filter function with a custom filter function that 
		    // can filter by range
		    // ==============================================================================
		    
		    this._oDataTable.dataTableExt.afnFiltering.push( function(oSettings, aData, iDataIndex){
						
			    var iMin = document.getElementById(sFromId).value * 1;
			    var iMax = document.getElementById(sToId).value * 1;
			    
			    var result = false;

			    var iValue = aData[args.index] == "-" ? 0 : aData[args.index] * 1;

			    if( (iMin == "") && (iMax == "") ){

				    result = true;
			    }
			    else if( (iMin == "") && (iValue < iMax) ){

				    result = true;
			    }
			    else if( (iMin < iValue) && (iMax == "") ){

				    result = true;
			    }
			    else if( (iMin < iValue) && (iValue < iMax) ){

				    result = true;
			    }
			    
			    if(parent._oConfig.bTrace){ console.log({
								    method:'_fnCreateRangeInput',
								    action: 'Filter function',
								    data: {
									    oSettings: oSettings,
									    aData: aData,
									    iDataIndex: iDataIndex,
									    result: result
								    }
								});
			    }	

			    return result;
				
		    });		    
		    
		    // Attach event listeners to both of the column's input fields that cause
		    // the table to redraw if the contents of a field changes
		    // ==============================================================================
		    
		    $('#' + sFromId + ',#' + sToId, args.th).keyup( function(){

			    var iMin = document.getElementById(sFromId).value * 1;
			    var iMax = document.getElementById(sToId).value * 1;
			    
			    var fields_identical = ((iMin != 0) && (iMax != 0) && (iMin > iMax));
			    
			    if(parent._oConfig.bTrace){ console.log({
								    method:'_fnCreateRangeInput',
								    action: 'Change function',
								    data: {
									    sFromId: sFromId,
									    sToId: sToId,
									    fields_identical: fields_identical
								    }
								});
			    }			    
			    
			    if(!fields_identical){
				
				    parent._fnQueueUpdate();
			    }			    
			    
		    });
		    
		    // When a user clicks into an empty field, clear the hinting text
		    // ==============================================================================
		    
		    from.focus( function(){

			    if( $(this).hasClass("search_init") ){

				    $(this).removeClass("search_init");
				    this.value = "";
			    }
		    });
		    
		    to.focus( function(){

			    if( $(this).hasClass("search_init") ){

				    $(this).removeClass("search_init");
				    this.value = "";
			    }
		    });	
		    
		    // When a user clicks out of an empty field, add back the hinting text
		    // ==============================================================================
		    
		    from.blur( function(){

			    if(this.value == ""){

				    $(this).addClass("search_init");
				    this.value = parent._oConfig.aoColumns[args.index].oRangeFormat.from;
			    }
			    else {
				    $(this).removeClass("search_init");
			    }
		    });	
		    
		    to.blur( function(){

			    if(this.value == ""){

				    $(this).addClass("search_init");
				    this.value = parent._oConfig.aoColumns[args.index].oRangeFormat.to;
			    }
			    else {
				    $(this).removeClass("search_init");
			    }
		    });			    
		    
		    return true;

	    },

	    /**
	    * Creates a two-variable range input field for date data types and adds it to the target table
	    * 
	    *  @method _fnCreateDateRangeInput
	    *  
	    *  @param {{index: int, label: string, th: object}} args | Control args
	    *	=> VAL int index | the index of the field, starting at the LHS of the table
	    *	=> VAL string label | the input field's label
	    *	=> VAL object th | jQuery object containing the first object in the table header
	    *	
	    *  @returns void
	    */
	    "_fnCreateDateRangeInput": function(args){


		    var parent = this;
		    
		    var fromText = this._oConfig.aoColumns[args.index].oRangeFormat.from;
		    var toText = this._oConfig.aoColumns[args.index].oRangeFormat.to;
		    
		    // Create two text input fields with the correct filtering class, and
		    // add them to the table using jQuery's "raw HTML" mode.
		    // ==============================================================================		   

		    var sFromId = this._oConfig.sTableId + 'range_from_' + args.index;
		    var from = $('<input type="text" class="date_range_filter is_empty" id="' + sFromId + '" rel="' + args.index + '" value="' + fromText + '"/>');
		    			    
		    // Replace ALL of the HTML within the element with our first field, clearing the heading added
		    // by dataTables in the process
		    
		    args.th.html(from);

		    var sToId = this._oConfig.sTableId + 'range_to_' + args.index;
		    var to = $('<input type="text" class="date_range_filter is_empty" id="' + sToId + '" rel="' + args.index + '" value="' + toText + '"/>');

		    args.th.append(to);

		    args.th.wrapInner('<span class="filterColumn filter_date_range" />');		   
		    
		    
		    // Add the column's hinting text
		    this._asInitVals[args.index] = {
							from: fromText,
							to: toText
		    };						    

		    // Flag the column for processing
		    // ==============================================================================		    
		    
		    if(parent._oConfig.bTrace){ console.log({
							    method:'_fnCreateDateRangeInput',
							    action: 'Adding custom search key',
							    data: {
								    index: args.index
							    }
							});
		    }	
			    
		    this._aiCustomSearch_Indexes.push(args.index);		    
		    
			
		    // Replace the column's DOM filter function with a custom filter function that 
		    // can filter by date range
		    // ==============================================================================
		    
		    this._oDataTable.dataTableExt.afnFiltering.push( function(oSettings, aData, iDataIndex){

			    var dStartDate = from.datepicker("getDate");
			    var dEndDate = to.datepicker("getDate");
			    
			    var dCellDate = $.datepicker.parseDate($.datepicker.regional[""].dateFormat, aData[args.index]);
			    
			    var result = false;

			    if(dCellDate !== null){

				    if( (dStartDate == null) && (dEndDate == null) ){

					    result = true;
				    }
				    else if( (dStartDate == null) && (dCellDate < dEndDate) ){

					    result = true;
				    }
				    else if( (dStartDate < dCellDate) && (dEndDate == null) ){

					    result = true;
				    }
				    else if( (dStartDate < dCellDate) && (dCellDate < dEndDate) ){

					    result = true;
				    }			    
			    }

			    if(parent._oConfig.bTrace){ console.log({
								    method:'_fnCreateDateRangeInput',
								    action: 'Filter function',
								    data: {
									    dCellDate: dCellDate,
									    dStartDate: dStartDate,
									    dEndDate: dEndDate,
									    result: result
								    }
								});
			    }	
			    
			    return result;

		    });
		    
		    // Attach event listeners to both of the column's input fields that cause
		    // the table to redraw if the contents of a field changes
		    // ==============================================================================
		    
		    $('#' + sFromId + ',#' + sToId, args.th).change( function(){
			
			    if(parent._oConfig.bTrace){ console.log({
								    method:'_fnCreateDateRangeInput',
								    action: 'Change function',
								    data: {
									    sFromId: sFromId,
									    sToId: sToId
								    }
								});
			    }	

			    parent._fnQueueUpdate();
			    
		    });
		    			    		    		    		   
		    
		    if(from.datepicker == null){
			
			    // Trap jQueryUI datepicker() not being loaded ...because at least 90% of users
			    // are going to encounter this at some point.
		    
			    alert("The jQuery UI datepicker class isn't loaded");
			    return false;
		    }
		    
		    // Create the 'from' field jQueryUI datepicker instance
		    // ==============================================================================	
		    
		    from.datepicker({ 
			
			    beforeShowDay: function(date){ 

				// Prevent the user from setting the date in the 'from' field to a value larger
				// than the date in the 'to' field

				if( !$('#' + parent._oConfig.sTableId + 'range_to_' + args.index).hasClass("is_empty") ){

					var fromDate = date;

					var toVal = $('#' + parent._oConfig.sTableId + 'range_to_' + args.index).val();
					var toDate = $.datepicker.parseDate($.datepicker.regional[""].dateFormat, toVal);

					if(parent._oConfig.bTrace){ console.log({
								method:'beforeShowDay',
								action: '"from" datepicker beforeShowDay',
								data: {
									fromDate: fromDate,
									toDate: toDate
								}
							    });
					}					    

					if(fromDate >= toDate){

						return [false, 'disable_date', parent._oConfig.oLanguage.sWarningDateBefore];
					}
					else {					    
						return [true, ''];
					}

				}
				else {

					return [true, ''];
				}

			    },
			    
			    // Make the datePicker fire a 'blur' event when it closes, so that the correct
			    // CSS classes are applied to the field by our blur() handler later in the code
			    
			    onClose: function(){
				
				    $(this).trigger('blur');
			    }			
		
		    });	
		
		
		    // Create the 'to' field jQueryUI datepicker instance
		    // ==============================================================================
		    
		    to.datepicker({ 
			
			    beforeShowDay: function(date){ 

				// Prevent the user from setting the date in the 'to' field to a value smaller
				// than the date in the 'from' field

				if( !$('#' + parent._oConfig.sTableId + 'range_from_' + args.index).hasClass("is_empty") ){

					var toDate = date;

					var fromVal = $('#' + parent._oConfig.sTableId + 'range_from_' + args.index).val();
					var fromDate = $.datepicker.parseDate($.datepicker.regional[""].dateFormat, fromVal);

					if(parent._oConfig.bTrace){ console.log({
								method:'beforeShowDay',
								action: '"from" datepicker beforeShowDay',
								data: {
									fromDate: fromDate,
									toDate: toDate
								}
							    });
					}					    

					if(toDate <= fromDate){

						return [false, 'disable_date', parent._oConfig.oLanguage.sWarningDateAfter];
					}
					else {					    
						return [true, ''];
					}

				}
				else {

					return [true, ''];
				}

			    },
			    
			    // Make the datePicker fire a 'blur' event when it closes, so that the correct
			    // CSS classes are applied to the field by our blur() handler later in the code
			    
			    onClose: function(){
				
				    $(this).trigger('blur');
			    }			
		
		    });
		    
		    		    
		    // When a user clicks into an empty field, clear the hinting text
		    // ==============================================================================
		    
		    from.focus( function(){

			    var isEmpty = $(this).hasClass("is_empty");
			    		    			    
			    if(parent._oConfig.bTrace){ console.log({
								    method:'_fnCreateDateRangeInput',
								    action: 'From field focus',
								    data: {
									    isEmpty: isEmpty
								    }
								});
			    }	

			    if(isEmpty ){

				    $(this).removeClass("is_empty");
				    this.value = "";
			    }	

		    });
		    
		    to.focus( function(){

			    var isEmpty = $(this).hasClass("is_empty");

			    if(parent._oConfig.bTrace){ console.log({
								    method:'_fnCreateDateRangeInput',
								    action: 'To field focus',
								    data: {
									    isEmpty: isEmpty
								    }
								});
			    }
			    
			    if(isEmpty ){

				    $(this).removeClass("is_empty");
				    this.value = "";
			    }

		    });		    


		    // When a user clicks out of an empty field, add back the hinting text
		    // ==============================================================================		    
		    // @see http://stackoverflow.com/questions/1814292/jqueryui-datepicker-fires-inputs-blur-before-passing-date-avoid-workaround
		    
		    
		    from.blur( function(){  
			
			    // Prevent a race-condition with the jQuery UI datepicker widget. If we just attach to
			    // the 'blur' event, our code will read the contents of the field before the datepicker
			    // widget updates the field contents. Using setTimeout() is actually a good way to 
			    // do this due to JavaScript's single-threaded execution model
			    
			    var blurParent = this;
			    
			    setTimeout( function(){

				    var markedEmpty = $(blurParent).hasClass("is_empty");
				    var oldVal = blurParent.value;

				    if( (blurParent.value == "") || (blurParent.value == undefined) ){

					    if(!markedEmpty){
						    $(blurParent).addClass("is_empty");
					    }

					    blurParent.value = parent._oConfig.aoColumns[args.index].oRangeFormat.from;
				    }
				    else {
					    if(markedEmpty){
						    $(blurParent).removeClass("is_empty");
					    }
				    }
				    
				    if(parent._oConfig.bTrace){ console.log({
									    method:'_fnCreateDateRangeInput',
									    action: 'From field blur',
									    data: {
										    markedEmpty: markedEmpty,
										    oldVal: oldVal,
										    newVal: blurParent.value
									    }
									});
				    }				    
			    
			    }, 1);
			    
		    });	
		    
		    to.blur( function(){ 
			
			    // Prevent a race-condition with the jQuery UI datepicker widget. If we just attach to
			    // the 'blur' event, our code will read the contents of the field before the datepicker
			    // widget updates the field contents. Using setTimeout() is actually a good way to 
			    // do this due to JavaScript's single-threaded execution model
			    
			    var blurParent = this;
			    
			    setTimeout( function(){

				    var markedEmpty = $(blurParent).hasClass("is_empty");
				    var oldVal = blurParent.value;

				    if((blurParent.value == "") || (blurParent.value == undefined)){

					    if(!markedEmpty){
						    $(blurParent).addClass("is_empty");
					    }

					    blurParent.value = parent._oConfig.aoColumns[args.index].oRangeFormat.to;
				    }
				    else {
					    if(markedEmpty){
						   $(blurParent).removeClass("is_empty");
					    }
				    }
				    
				    if(parent._oConfig.bTrace){ console.log({
									    method:'_fnCreateDateRangeInput',
									    action: 'To field blur',
									    data: {
										    markedEmpty: markedEmpty,
										    oldVal: oldVal,
										    newVal: blurParent.value
									    }
									});
				    }				    
			    
			    }, 1);
		    });	


		    return true;

	    },

	    /**
	    * Creates a single-variable select field and adds it to the target table
	    * 
	    *  @method _fnCreateSelect
	    *  
	    *  @param {{index: int, label: string, th: object, aData: array}} args | Control args
	    *	=> VAL int index | the index of the field, starting at the LHS of the table
	    *	=> VAL string label | the input field's label
	    *	=> VAL object th | jQuery object containing the first object in the table header
	    *	=> VAL array aData | option values for the select box
	    *	   
	    *  @returns void
	    */
	    "_fnCreateSelect": function(args){
		
		
		    var parent = this;
		    
		    // Create a select field with the correct filtering class, and add it to the
		    // table using jQuery's "raw HTML" mode.
		    // ==============================================================================		    

		    var sId = this._oConfig.sTableId + 'select_' + args.index;		    
		    var r = '<select class="is_empty select_filter" id="' + sId + '"><option value="">' + args.label + '</option>';

		    for(var j=0; j < args.aData.length; j++){

			    r += '<option class="select_item" value="' + args.aData[j] + '">' + args.aData[j] + '</option>';
		    }

		    var select = $(r + '</select>');
		    
		    args.th.html(select);
		    args.th.wrapInner('<span class="filterColumn filter_select" />');
		    
		    // Flag the column for processing
		    // ==============================================================================		    
		    
		    if(parent._oConfig.bTrace){ console.log({
							    method:'_fnCreateSelect',
							    action: 'Adding custom search key',
							    data: {
								    index: args.index
							    }
							});
		    }	
		    
		    this._aiCustomSearch_Indexes.push(args.index);		    
		    
			
		    // Replace the column's DOM filter function with our own filter function
		    // ==============================================================================
		    
		    this._oDataTable.dataTableExt.afnFiltering.push( function(oSettings, aData, iDataIndex){

			    var val = document.getElementById(sId).value;
			    var cell = aData[args.index];			    
	
			    if(val == cell){
				    return true;
			    }
			    else {
				    return false;
			    }

		    });
		    		    
		    // Attach an event listener to the field that causes the table to update
		    // when the field's value is changed
		    // ==============================================================================		    

		    select.change( function(){
			
			    if(parent._oConfig.bTrace){ console.log({
								    method:'_fnCreateSelect',
								    action: 'Change function',
								    data: {
									    index: args.index
								    }
								});
			    }
		    
			    if(this.value != ""){
				
				    $(this).removeClass("is_empty");
			    } 
			    else {
				    $(this).addClass("is_empty");
			    }
			    
			    parent._fnQueueUpdate();
			    
		    });

	    },
	    
	    /**
	    * Processes a 'number' input field
	    * 
	    *  @method _fnProcessNumberField 
	    *  @param {int} index | index of the data field's column 
	    *  @returns string
	    */
	    "_fnProcessNumberField": function(index){

		    if( !$('#' + this._oConfig.sTableId + index).hasClass("search_init") ){

			    return $('#' + this._oConfig.sTableId + index).val();
		    }
		    else {
			    return "";
		    }
		    
	    },	
	    
	    /**
	    * Processes a 'text' input field
	    * 
	    *  @method _fnProcessTextField 
	    *  @param {int} index | index of the data field's column
	    *  @returns string
	    */
	    "_fnProcessTextField": function(index){
		
		    
		    if( !$('#' + this._oConfig.sTableId + index).hasClass("search_init") ){

			    return $('#' + this._oConfig.sTableId + index).val();
		    }
		    else {
			    return "";
		    }		    
	    },	 
	    
	    /**
	    * Processes a 'select' input field
	    * 
	    *  @method _fnProcessSelectField 
	    *  @param {int} index | index of the data field's column
	    *  @returns string
	    */
	    "_fnProcessSelectField": function(index){
		
		
		    if( !$('#' + this._oConfig.sTableId + 'select_' + index).hasClass("is_empty") ){

			    return $('#' + this._oConfig.sTableId + 'select_' + index).val();
		    }
		    else {
			    return "";
		    }			    		    
		    
	    },	 
	    
	    /**
	    * Processes a 'number-range' input field
	    * 
	    *  @method _fnProcessNumberRangeField 
	    *  @param {int} index | index of the data field's column
	    *  @returns string
	    */
	    "_fnProcessNumberRangeField": function(index){

		    // Scrape the form fields for this column from the DOM using jQuery

		    var fromVal = "";
		    var toVal = "";
		    
		    if( !$('#' + this._oConfig.sTableId + 'range_from_' + index).hasClass("search_init") ){

			    fromVal = $('#' + this._oConfig.sTableId + 'range_from_' + index).val();
		    }
		    
		    if( !$('#' + this._oConfig.sTableId + 'range_to_' + index).hasClass("search_init") ){

			    toVal = $('#' + this._oConfig.sTableId + 'range_to_' + index).val();
		    }
		    
		    var rangeSeperator = this._oConfig.sRangeSeparator;
		    var result = null;

		    if( !fromVal && !toVal ){

			    // TYPICAL OUTPUT: ""

			    result = "";								    
		    }
		    else if( fromVal && toVal ){

			    // TYPICAL OUTPUT: "15~27"

			    result =  fromVal + rangeSeperator + toVal;
		    }
		    else if(fromVal){

			    // TYPICAL OUTPUT: "15~"

			    result =  fromVal + rangeSeperator;								    
		    }
		    else {
			    // TYPICAL OUTPUT: "~27"

			    result =  rangeSeperator + toVal;							    
		    }	
		    
		    return result;
		    
	    },		    
	    
	    /**
	    * Processes a 'date-range' input field
	    * 
	    *  @method _fnProcessDateRangeField 
	    *  @param {int} index | index of the data field's column
	    *  @returns string
	    */
	    "_fnProcessDateRangeField": function(index){


		    // Scrape the form fields for this column from the DOM using jQuery

		    var fromVal = null;
		    var toVal = null;
		    
		    if( !$('#' + this._oConfig.sTableId + 'range_from_' + index).hasClass("is_empty") ){

			    fromVal = $('#' + this._oConfig.sTableId + 'range_from_' + index).val();
			    
			    if(this._oConfig.bTrace){ console.log({
								    method:'_fnProcessDateRangeField',
								    action: 'Processing "from" field',
								    data: {
									    fromVal: fromVal
								    }
								});
			    }				    
		    }
		    else {
			    if(this._oConfig.bTrace){ console.log({
								    method:'_fnProcessDateRangeField',
								    action: 'Processing "from" field'
								});
			    }							
		    }
		    
		    if( !$('#' + this._oConfig.sTableId + 'range_to_' + index).hasClass("is_empty") ){

			    toVal = $('#' + this._oConfig.sTableId + 'range_to_' + index).val();
			    
			    if(this._oConfig.bTrace){ console.log({
								    method:'_fnProcessDateRangeField',
								    action: 'Processing "to" field',
								    data: {
									    toVal: toVal
								    }
								});
			    }				    
		    }
		    else {
			    if(this._oConfig.bTrace){ console.log({
								    method:'_fnProcessDateRangeField',
								    action: 'Processing "to" field'
								});
			    }				
		    }
		    
		    var result = null;
		    var from = null;
		    var to = null;

		    var rangeSeperator = this._oConfig.sRangeSeparator;

		    // Based on which fields are filled, parse the date from local time into a JavaScript
		    // date object using datepicker.parseDate(), use getTime() to convert it to a UNIX
		    // timestamp, then divide by 1000 to convert it to 1-second precision

		    if( !fromVal && !toVal ){

			    // TYPICAL OUTPUT: ""

			    result = "";								    
		    }
		    else if( fromVal && toVal ){

			    // TYPICAL OUTPUT: "1360738800~1360920400"

			    from = $.datepicker.parseDate($.datepicker.regional[""].dateFormat, fromVal);
			    from = Math.round(from.getTime() / 1000) ;

			    to = $.datepicker.parseDate($.datepicker.regional[""].dateFormat, toVal);
			    to = Math.round(to.getTime() / 1000) ;

			    result =  from + rangeSeperator + to;
		    }
		    else if(fromVal){

			    // TYPICAL OUTPUT: "1360738800~"

			    from = $.datepicker.parseDate($.datepicker.regional[""].dateFormat, fromVal);
			    from = Math.round(from.getTime() / 1000) ;

			    result =  from + rangeSeperator;								    
		    }
		    else {
			    // TYPICAL OUTPUT: "~1360920400"

			    to = $.datepicker.parseDate($.datepicker.regional[""].dateFormat, toVal);
			    to = Math.round(to.getTime() / 1000) ;

			    result =  rangeSeperator + to;							    
		    }	
		    
		    return result;
		    
	    },
	    
	    /**
	    * Schedules an update from a remote data source
	    * 
	    *  @method _fnQueueUpdate 
	    *  @returns void
	    */
	    "_fnQueueUpdate": function(){

		    var parent = this;
		    
		    // If a timer is already running, clear it
		    
		    if(this._bUpdateQueued){
    
			    if(this._oConfig.bTrace){ console.log({
								    method:'_fnQueueUpdate',
								    action: 'Clearing existing timer',
								    data: {
									    timerId: this._iTimerID
								    }
								});
			    }
			    
			    window.clearTimeout(this._iTimerID);			    			
		    }	
		    
		    
		    // Create a new timer function
		    			    
		    this._iTimerID = window.setTimeout( function(){	
			
								    if(parent._oConfig.bTrace){ console.log({
													    method:'_fnQueueUpdate',
													    action: 'Timer function fired',
													    data: {
														    timerId: parent._iTimerID
													    }
													});
								    }		
								    
								    parent._bUpdateQueued = false;
								    window.clearTimeout(parent._iTimerID);
								    parent._iTimerID = null;

								    // Update the table
								    parent._oDataTable.fnDraw();
							}, 
							this._oConfig.iHysteresis
		    );	
			
		    if(this._oConfig.bTrace){ console.log({
							    method:'_fnQueueUpdate',
							    action: 'Created new timer function',
							    data: {
								    timerId: this._iTimerID
							    }
							});
		    }			

		    this._bUpdateQueued = true;
		    
	    },

	    /**
	    * Cancels a scheduled update from a remote data source
	    * 
	    *  @method _fnCancelUpdate 
	    *  @returns void
	    */
	    "_fnCancelUpdate": function(){
		
		    
		    if(this._bUpdateQueued){
    
			    if(this._oConfig.bTrace){ console.log({
								    method:'_fnCancelUpdate',
								    action: 'Clearing existing timer',
								    data: {
									    timerId: this._iTimerID
								    }
								});
			    }
			    
			    window.clearTimeout(this._iTimerID);
			    
			    this._bUpdateQueued = false;
			    this._iTimerID = null;			    
		    }	
		    
	    }

    };
    

    /**
    * Name of this class
    *  @type     String
    *  @default  ColumnFilter
    *  @static
    */
    ColumnFilter.prototype.CLASS = "ColumnFilter";

    /**
    * ColumnFilter version
    *  @type      String
    *  @default   See code
    *  @static
    */
    ColumnFilter.VERSION = "1.0";
    ColumnFilter.prototype.VERSION = ColumnFilter.VERSION;



})(jQuery);