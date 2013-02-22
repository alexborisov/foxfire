/**
 * @summary     Column Filter
 * @description Filters datatable columns by range or value
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
		
		
		    this._oDataTable = oDT;			    // DataTable object that this ColumnFilter instance is attached to
		    this._afnSearch = new Array();		    // Array of the functions that will override sSearch_ parameters
		    
		    this._aiCustomSearch_Indexes = new Array();	    // Array of column indexes that need to be intercepted and modified
								    // during the search string build process
								    
		    this._oFunctionTimeout = null;		    // Unknown	
		    this._asInitVals = new Array();		    // Unknown
		    this._label = null;				    // Unknown
		    this._th = null;				    // Unknown		    

		    this._oConfig = {	// Note the _

			    /** 
			    * Indicate if ColumnFilter should show show trace information on the console or not. This can be 
			    * useful when debugging, or if you're just curious as to what it's doing. Make sure this option  
			    * is switched off for production.
			    *  @type     bool
			    *  @default  false
			    *  @static
			    */
			    "bTrace": false,

			    /** 
			    * Sets whether to place the filtering fields in the header or the footer of the table.
			    *  @type     string (enumerated) ["head" | "foot"]
			    *  @default  foot
			    *  @static
			    */
			    "sPlaceHolder": "foot",

			    /** 
			    * Character used to separate the fields when rendering a pair of input fields
			    * for a column filtered by range 
			    *  @type     string
			    *  @default  "~"
			    *  @static
			    */
			    "sRangeSeparator": "~",

			    /** 
			    * Delay in milliseconds between the time a user changes a value in a filtering field,
			    * and when ColumnFilter updates the table based on the changed value. This setting
			    * prevents ColumnFilter from running unnecessary queries as a user types values into
			    * a field. In applications that require "search-as-you-type" functionality as the user
			    * enters data into a field, set this value to around 50 milliseconds. 
			    *  @type     int
			    *  @default  500
			    *  @static
			    */
			    "iFilteringDelay": 500,

			    /** 
			    * Array of the filter settings that will be applied on the columns
			    *  @type     array{object}
			    *  @default  null
			    *  @static
			    */
			    "aoColumns": null,

			    /** 
			    * The default format for columns that are filtered by range
			    *  @type     string
			    *  @default  "From {from} to {to}"
			    *  @static
			    */
			    "sRangeFormat": "From {from} to {to}",

			    /** 
			    * Unknown
			    *  @type     string
			    *  @default  "table"
			    *  @static
			    */
			    "sTableId": "table"

		    } 		    
		    
		    if(oConfig == null){    // Prevent the 'in' constructs from crashing
					    // on a null oConfig object			
			    oConfig = {};
		    }
		    
		    // Merge ColumnFilter's default config options with any config options that 
		    // are passed in during instantiation
		    // ===================================================================================
		    
		    if("bTrace" in oConfig){ this._oConfig.bTrace = oConfig.bTrace;}
		    if("sPlaceHolder" in oConfig){ this._oConfig.sPlaceHolder = oConfig.sPlaceHolder;}
		    if("sRangeSeparator" in oConfig){ this._oConfig.sRangeSeparator = oConfig.sRangeSeparator;}
		    if("iFilteringDelay" in oConfig){ this._oConfig.iFilteringDelay = oConfig.iFilteringDelay;}
		    if("aoColumns" in oConfig){ this._oConfig.aoColumns = oConfig.aoColumns;}
		    if("sRangeFormat" in oConfig){ this._oConfig.sRangeFormat = oConfig.sRangeFormat;}
		    if("sTableId" in oConfig){ this._oConfig.sTableId = oConfig.sTableId;}

		    this._fnRender();
		    
		    console.log(this._oDataTable.dataTableExt.afnFiltering);

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

		    // Select the target table row using jQuery, and iterate over each object inside the 
		    // object that jQuery returns. 
		    // ===================================================================================		
		    
		    $(sFilterRow + " th", this._oDataTable).each( function(index){


			    // If custom properties are passed in options.aoColumns, transcribe them
			    // to the column that we're currently iterating through. Otherwise use
			    // default values.

			    var aoColumn = {};

			    if(parent._oConfig.aoColumns == null){

				    aoColumn = { 
						    type: "text",
						    bRegex: false,
						    bSmart: true
				    };    
			    }
			    else {
				    // Note that because this as an anonymous function we have to use parent._X
				    // to access properties in the parent object instead of this._X
				    
				    if(parent._oConfig.aoColumns.length < index){

					    return;
				    }
				    else if (parent._oConfig.aoColumns[index] == null){

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

			    parent._label = $(this).text();
			    parent._th = $($(this)[0]);


			    // Add default range format

			    if(aoColumn.sRangeFormat != null){

				    parent._sRangeFormat = aoColumn.sRangeFormat;
			    }
			    else {			    
				    parent._sRangeFormat = parent._oConfig.sRangeFormat;
			    }


			    // Based on column type, run the correct generator function
			    // =====================================================================

			    switch(aoColumn.type){

				    case "number": {

					    parent._fnCreateInput(index, true, false, true);
					    break;
				    }

				    case "text": {

					    var bRegex = (aoColumn.bRegex == null ? false : aoColumn.bRegex);
					    var bSmart = (aoColumn.bSmart == null ? false : aoColumn.bSmart);
					    
					    parent._fnCreateInput(index, bRegex, bSmart, false);
					    break;
				    }

				    case "select": {

					    parent._fnCreateSelect(index, aoColumn.values);
					    break;
				    }

				    case "number-range": {

					    parent._fnCreateRangeInput(index);
					    break;
				    }

				    case "date-range": {

					    parent._fnCreateDateRangeInput(index);
					    break;
				    }

				    default: {

					    alert( "Warning: ColumnFilter passed unknown column type '" + aoColumn.type + "', index '" + index + "'");
					    break;
				    }


			    }   // ENDOF: switch(aoColumn.type){


		    });	// ENDOF: $(sFilterRow + " th", this._oDataTable).each( function(index)


		    for(j=0; j < this._aiCustomSearch_Indexes.length; j++){


			    var paramIndex = this._aiCustomSearch_Indexes[j];
			    var rangeSeperator = this._oConfig.sRangeSeparator; 

			    var fnSearch = function(){

				    console.log(paramIndex);

				    var fromVal = $("#tablerange_from_" + paramIndex).val();
				    var toVal = $("#tablerange_to_" + paramIndex).val();

				    console.log(fromVal); 
				    console.log(toVal);

				    if( !fromVal && !toVal ){

					    return "";
				    }
				    else if( fromVal && toVal ){

					    return fromVal + rangeSeperator + toVal;
				    }
				    else if(fromVal){

					    return fromVal + rangeSeperator;
				    }
				    else {
					    return rangeSeperator + toVal;
				    }				    
			    };
					    
 
			    this._afnSearch.push(fnSearch);			    
			    
		    }

		    // If the dataTables API is using server-side processing, override its fnServerData()
		    // function with our own version that includes column filtering.
		    // ===================================================================================			

		    if(this._oDataTable.fnSettings().oFeatures.bServerSide){


			    // Clone the original fnServerData function
			    var fnServerDataOriginal = this._oDataTable.fnSettings().fnServerData;
			    
			    // Then replace it with our own function
			    
			    this._oDataTable.fnSettings().fnServerData = function(sUrl, aoData, fnCallback, oSettings){

				    //console.log({n: "aoData-original", v: aoData});
				    
				    // Attach our search functions to the data object
				    for(j=0; j < parent._aiCustomSearch_Indexes.length; j++){

					    var index = parent._aiCustomSearch_Indexes[j];

					    for(k=0; k < aoData.length; k++){

						    if( aoData[k].name == ("sSearch_" + index) ){

							    console.log("overwriting " + j);
							    aoData[k].value = parent._afnSearch[j]();
						    }
					    }
				    }
				    
				    //console.log({n: "_aiCustomSearch_Indexes", v: parent._aiCustomSearch_Indexes});
				    //console.log({n: "_afnSearch", v: parent._afnSearch});

				    aoData.push({ "name": "sRangeSeparator", "value": parent._oConfig.sRangeSeparator });
				    
				    //console.log({n: "aoData-modified", v: aoData});

				    // If the dataTables fnServerData() function was already overridden, pass our modified
				    // parameters through to the existing function. Otherwise, run the standard getJSON() function.
				    
				    if(fnServerDataOriginal != null){
									
					    fnServerDataOriginal(sUrl, aoData, fnCallback, oSettings);
				    }
				    else {  // TODO: Is this necessary?
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
	    * Creates a single-variable input field
	    * 
	    *  @method _fnCreateInput
	    *  @param {int} index | the index of the field, starting at the LHS of the table
	    *  @param {string} regex | unknown
	    *  @param {string} smart | unknown
	    *  @param {bool} bIsNumber | unknown
	    *  @returns void
	    */
	    "_fnCreateInput": function(index, regex, smart, bIsNumber){
		
		    var parent = this;
		    var sCSSClass = "text_filter";

		    if(bIsNumber){

			    sCSSClass = "number_filter";
		    }

		    var input = $('<input type="text" class="search_init ' + sCSSClass + '" value="' + this._label + '"/>');

		    this._th.html(input);

		    if(bIsNumber){

			    this._th.wrapInner('<span class="filter_column filter_number" />');
		    }
		    else {
			    this._th.wrapInner('<span class="filter_column filter_text" />');
		    }

		    this._asInitVals[index] = this._label;


		    if(bIsNumber && !this._oDataTable.fnSettings().oFeatures.bServerSide){

			    // Filter on the column all numbers that starts with the entered value

			    input.keyup( function(){			    

				    parent._oDataTable.fnFilter('^' + this.value, index, true, false);
			    });
		    } 
		    else {

			    // Filter on the column (the index) of this element

			    input.keyup( function(){

				    parent._oDataTable.fnFilter(this.value, index, regex, smart);
			    });
		    }

		    input.focus( function(){

			    if( $(this).hasClass("search_init") ){

				    $(this).removeClass("search_init");
				    this.value = "";
			    }
		    });

		    input.blur( function(){

			    if(this.value == ""){

				    $(this).addClass("search_init");
				    this.value = parent._asInitVals[index];
			    }
		    });

	    },

	    /**
	    * Creates a two-variable range input field for numeric data types
	    * 
	    *  @method _fnCreateRangeInput
	    *  @param {int} index | the index of the field, starting at the LHS of the table
	    *  @returns void
	    */
	    "_fnCreateRangeInput": function(index){

		    var parent = this;
		    
		    this._th.html(_fnRangeLabelPart(0));

		    var sFromId = this._oConfig.sTableId + 'range_from_' + index;
		    var from = $('<input type="text" class="number_range_filter" id="' + sFromId + '" rel="' + index + '"/>');

		    this._th.append(from);
		    this._th.append(_fnRangeLabelPart(1));

		    var sToId = this._oConfig.sTableId + 'range_to_' + index;
		    var to = $('<input type="text" class="number_range_filter" id="' + sToId + '" rel="' + index + '"/>');

		    this._th.append(to);
		    this._th.append(_fnRangeLabelPart(2));
		    this._th.wrapInner('<span class="filterColumn filter_number_range" />');

		    this._aiCustomSearch_Indexes.push(index);

		    
		    this._oDataTable.dataTableExt.afnFiltering.push( function(oSettings, aData, iDataIndex){

			    var iMin = document.getElementById(sFromId).value * 1;
			    var iMax = document.getElementById(sToId).value * 1;

			    var iValue = aData[index] == "-" ? 0 : aData[index] * 1;

			    if( (iMin == "") && (iMax == "") ){

				    return true;
			    }
			    else if( (iMin == "") && (iValue < iMax) ){

				    return true;
			    }
			    else if( (iMin < iValue) && (iMax == "") ){

				    return true;
			    }
			    else if( (iMin < iValue) && (iValue < iMax) ){

				    return true;
			    }

			    return false;
				
		    });		    
		    
		    $('#' + sFromId + ',#' + sToId, this._th).keyup( function(){

			    var iMin = document.getElementById(sFromId).value * 1;
			    var iMax = document.getElementById(sToId).value * 1;
			    
			    if( (iMin != 0) && (iMax != 0) && (iMin > iMax) ){
				
				    return;
			    }

			    parent._oDataTable.fnDraw();
		    });


	    },

	    /**
	    * Creates a two-variable range input field for date data types
	    * 
	    *  @method _fnCreateDateRangeInput
	    *  @param {int} index | the index of the field, starting at the LHS of the table
	    *  @returns void
	    */
	    "_fnCreateDateRangeInput": function(index){

		    var parent = this;
		    this._th.html(this._fnRangeLabelPart(0));

		    var sFromId = this._oConfig.sTableId + 'range_from_' + index;
		    var from = $('<input type="text" class="date_range_filter" id="' + sFromId + '" rel="' + index + '"/>');

		    // Trap jQueryUI datepicker() not being loaded ...because at least 90% of users
		    // are going to screw this one up at one point or another.
		    
		    if(from.datepicker == null){
			    alert("the jQuery UI datepicker class isn't loaded");
			    return false;
		    }
		    
		    from.datepicker();
			    
		    this._th.append(from);
		    this._th.append(this._fnRangeLabelPart(1));

		    var sToId = this._oConfig.sTableId + 'range_to_' + index;
		    var to = $('<input type="text" class="date_range_filter" id="' + sToId + '" rel="' + index + '"/>');

		    this._th.append(to);
		    this._th.append(this._fnRangeLabelPart(2));
		    this._th.wrapInner('<span class="filterColumn filter_date_range" />');		   
		    
		    to.datepicker();

		    this._aiCustomSearch_Indexes.push(index);
		    
//		    // Add range search functions
//		    // ===================================================================================	
//
//		    if(this._aiCustomSearch_Indexes[index] != null){
//
//			    var paramIndex = this._aiCustomSearch_Indexes[index];
//			    var rangeSeperator = this._oConfig.sRangeSeparator; 
//			    
//			    var fnSearch = function(){
//
//				    console.log(paramIndex);
//				    
//				    var fromVal = $("#tablerange_from_" + paramIndex).val();
//				    var toVal = $("#tablerange_to_" + paramIndex).val();
//				    
//				    console.log(fromVal); 
//				    console.log(toVal);
//				    
//				    if( !fromVal && !toVal ){
//					
//					    return "";
//				    }
//				    else if( fromVal && toVal ){
//					
//					    return fromVal + rangeSeperator + toVal;
//				    }
//				    else if(fromVal){
//					
//					    return fromVal + rangeSeperator;
//				    }
//				    else {
//					    return rangeSeperator + toVal;
//				    }				    
//			    };
//
//			    this._afnSearch.push(fnSearch);
//		    }		    
		    
			
		    this._oDataTable.dataTableExt.afnFiltering.push( function(oSettings, aData, iDataIndex){

			    var dStartDate = from.datepicker("getDate");
			    var dEndDate = to.datepicker("getDate");
			    
			    var dCellDate = $.datepicker.parseDate($.datepicker.regional[""].dateFormat, aData[index]);

			    if(dCellDate == null){

				    return false;
			    }

			    if( (dStartDate == null) && (dEndDate == null) ){

				    return true;
			    }
			    else if( (dStartDate == null) && (dCellDate < dEndDate) ){

				    return true;
			    }
			    else if( (dStartDate < dCellDate) && (dEndDate == null) ){

				    return true;
			    }
			    else if( (dStartDate < dCellDate) && (dCellDate < dEndDate) ){

				    return true;
			    }

			    return false;

		    });
		    
		    
		    $('#' + sFromId + ',#' + sToId, this._th).change( function(){
			
			    parent._oDataTable.fnDraw();
		    });

		    return true;

	    },

	    /**
	    * Creates a single-variable select field
	    * 
	    *  @method _fnCreateSelect
	    *  @param {int} index | the index of the field, starting at the LHS of the table
	    *  @param {array} aData | unknown	    
	    *  @returns void
	    */
	    "_fnCreateSelect": function(index, aData){
		
		    var parent = this;

		    var r = '<select class="search_init select_filter"><option value="" class="search_init">' + this._label + '</option>';

		    for(var j=0; j < aData.length; j++){

			    r += '<option value="' + aData[j] + '">' + aData[j] + '</option>';
		    }

		    var select = $(r + '</select>');
		    
		    this._th.html(select);
		    this._th.wrapInner('<span class="filterColumn filter_select" />');

		    select.change( function(){
			
			    if( $(this).val() != "" ){
				
				    $(this).removeClass("search_init");
			    } 
			    else {
				    $(this).addClass("search_init");
			    }
			    
			    parent._oDataTable.fnFilter($(this).val(), index);
		    });

	    },

	    /**
	    * Creates a range-format selector string
	    * 
	    *  @method _fnRangeLabelPart
	    *  @param {int} iPlace | unknown   
	    *  @returns string
	    */
	    "_fnRangeLabelPart": function(iPlace){

		    var sRangeFormat = this._oConfig.sRangeFormat;
		    
		    switch(iPlace){
			
			    case 0: {
				    return sRangeFormat.substring(0, sRangeFormat.indexOf("{from}"));
			    }
			    case 1: {
				    return sRangeFormat.substring(sRangeFormat.indexOf("{from}") + 6, sRangeFormat.indexOf("{to}"));
			    }
			    default: {
				    return sRangeFormat.substring(sRangeFormat.indexOf("{to}") + 4);
			    }
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


//$.fn.dataTableExt.afnFiltering.push( function(oSettings, aData, iDataIndex){
		    

//if(fnServerDataOriginal != null){
//if(properties.iDelay != 0){
//if(oFunctionTimeout != null)
//window.clearTimeout(oFunctionTimeout);
//oFunctionTimeout = window.setTimeout(function (){
//fnServerDataOriginal(sSource, aoData, fnCallback);
//}, properties.iDelay);
//} else {
//fnServerDataOriginal(sSource, aoData, fnCallback);
//}
//}
//else
//$.getJSON(sSource, aoData, function (json){
//fnCallback(json)
//});