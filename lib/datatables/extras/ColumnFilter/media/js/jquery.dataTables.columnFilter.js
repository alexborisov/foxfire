/**
 * @summary     Column Filter
 * @description Filters datatable columns by range or value
 * @file        dataTables.columnFilter.js
 * @version     1.0
 * @author      Carl Roett, based on code by Jovan Popovic and Allan Jardine
 * @license     GPL v2 or BSD 3 point style
 * @contact     https://github.com/foxly
 */

// Global scope for ColumnFilter 
var ColumnFilter;


(function($){

    /**
    * ColumnFilter default settings for initialisation
    *  @namespace
    *  @static
    */
    ColumnFilter.oDefaults = {

	    /** 
	    * Indicate if ColumnFilter should show show trace information on the console or not. This can be 
	    * useful when debugging if you're just curious as to what it is doing. It should be turned off for 
	    * production.
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

    };

    ColumnFilter = function(oDT, oConfig)
    {

	    // Sanity check that we are a new instance 

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
	    *  @returns {object} AutoFill settings object
	    */
	    this.fnSettings = function (){
		    return this._oConfig;
	    };


	    /* Constructor logic */
	    this._fnInit( oDT, oConfig );

	    return this;

    };


    ColumnFilter.prototype = {


	    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	    * Private methods (they are of course public in JS, but should be treated as private)
	    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	    /**
	    * Initialisation
	    *  @method _fnInit
	    *  @param {object} oDT DataTables settings object
	    *  @param {object} oConfig Configuration object for ColumnFilter
	    *  @returns void
	    */
	    "_fnInit": function(oDT, oConfig)
	    {	    
		
		    this._oDataTable = oDT;			    // DataTable instance this object is attached to

		    this._oConfig = {};				    // Merged config parameters
		    this._afnSearch = new Array();		    // Array of the functions that will override sSearch_ parameters
		    this._aiCustomSearch_Indexes = new Array();	    // Unknown
		    this._oFunctionTimeout = null;		    // Unknown
		    this._asInitVals = new Array();		    // Unknown
		    this._label = null;				    // Unknown
		    this._th = null;				    // Unknown

		    // Merge ColumnFilter's default config options with any config options that 
		    // are passed in during instantiation, building the private _oConfig object

		    this._oConfig.bTrace = ("bTrace" in oConfig) ? oConfig.bTrace : this.oDefaults.bTrace;
		    this._oConfig.sPlaceHolder = ("sPlaceHolder" in oConfig) ? oConfig.sPlaceHolder : this.oDefaults.sPlaceHolder;
		    this._oConfig.sRangeSeparator = ("sRangeSeparator" in oConfig) ? oConfig.sRangeSeparator : this.oDefaults.sRangeSeparator;
		    this._oConfig.iFilteringDelay = ("iFilteringDelay" in oConfig) ? oConfig.iFilteringDelay : this.oDefaults.iFilteringDelay;
		    this._oConfig.aoColumns = ("aoColumns" in oConfig) ? oConfig.aoColumns : this.oDefaults.aoColumns;
		    this._oConfig.sRangeFormat = ("sRangeFormat" in oConfig) ? oConfig.sRangeFormat : this.oDefaults.sRangeFormat;
		    this._oConfig.sTableId = ("sTableId" in oConfig) ? oConfig.sTableId : this.oDefaults.sTableId;

		    var me = this;
		    this._fnProcess(me);

	    },

	    "_fnProcess": function(parent)
	    {	  
		
		    var sFilterRow = "tfoot tr";
		    var j, k = 0;

		    // Determine whether we're attaching to the top or the bottom of the table, and
		    // build a jQuery selector string
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
		    // object that jQuery returns. Note that because this as an anonymous function we
		    // have to use parent._X to access properties in the parent object instead of this._X
		    // ===================================================================================		

		    $(sFilterRow + " th", this._oDataTable).each( function(index){


			    // If custom properties are passed in options.aoColumns, transcribe them
			    // to the column that we're currently iterating through. Otherwise use
			    // default values.
			    // =====================================================================

			    var aoColumn = {};

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
			    // =====================================================================			

			    parent._label = $(this).text();
			    parent._th = $($(this)[0]);


			    // Add default range format

			    if(aoColumn.sRangeFormat != null){

				    parent._sRangeFormat = aoColumn.sRangeFormat;
			    }
			    else {			    
				    parent._sRangeFormat = parent._oConfig.sRangeFormat;
			    }


			    // Based on column type, run the correct modifier function
			    // =====================================================================

			    switch(aoColumn.type){

				    case "number": {

					    fnCreateInput(true, false, true);
					    break;
				    }

				    case "text": {

					    bRegex = (aoColumn.bRegex == null ? false : aoColumn.bRegex);
					    bSmart = (aoColumn.bSmart == null ? false : aoColumn.bSmart);
					    this._fnCreateInput(bRegex, bSmart, false);
					    break;
				    }

				    case "select": {

					    this._fnCreateSelect(aoColumn.values);
					    break;
				    }

				    case "number-range": {

					    this._fnCreateRangeInput();
					    break;
				    }

				    case "date-range": {

					    this._fnCreateDateRangeInput();
					    break;
				    }

				    default: {

					    alert( "Warning: ColumnFilter passed unknown column type '" + aoColumn.type + "'");
					    break;
				    }


			    }   // ENDOF: switch(aoColumn.type){


		    });	// ENDOF: $(sFilterRow + " th", this._oDataTable).each( function(index)



		    // Build range-search boxes
		    // ===================================================================================	

		    for(j=0; j < this._aiCustomSearch_Indexes.length; j++){

			    var index = this._aiCustomSearch_Indexes[j];
			    var rangeSeperator = this._oConfig.sRangeSeparator;

			    var fnSearch = function(){

				    return $("#range_from_" + index).val() + rangeSeperator + $("#range_to_" + index).val()
			    };

			    this._afnSearch.push(fnSearch);

		    }


		    // Attach to the dataTables API and check it we're using server-side processing
		    // ===================================================================================			

		    if(this._oDataTable.fnSettings().oFeatures.bServerSide){


			    var fnServerDataOriginal = this._oDataTable.fnSettings().fnServerData;


			    this._oDataTable.fnSettings().fnServerData = function (sSource, aoData, fnCallback){


				    for(j=0; j < this._aiCustomSearch_Indexes.length; j++){

					    var index = this._aiCustomSearch_Indexes[j];

					    for(k=0; k < aoData.length; k++){

						    if(aoData[k].name == "sSearch_" + index){

							    aoData[k].value = this._afnSearch[j]();
						    }
					    }
				    }

				    aoData.push({ "name": "sRangeSeparator", "value": this.oConfig.sRangeSeparator });


			//                    if(fnServerDataOriginal != null){
			//                        fnServerDataOriginal(sSource, aoData, fnCallback);
			//                    }
			//                    else {
			//                        $.getJSON(sSource, aoData, function (json){
			//                            fnCallback(json)
			//                        });
			//                    }

					$.getJSON(sSource, aoData, function (json){
					    fnCallback(json)
					});


				    /*
				    if(fnServerDataOriginal != null){
				    if(properties.iDelay != 0){
				    if(oFunctionTimeout != null)
				    window.clearTimeout(oFunctionTimeout);
				    oFunctionTimeout = window.setTimeout(function (){
				    fnServerDataOriginal(sSource, aoData, fnCallback);
				    }, properties.iDelay);
				    } else {
				    fnServerDataOriginal(sSource, aoData, fnCallback);
				    }
				    }
				    else
				    $.getJSON(sSource, aoData, function (json){
				    fnCallback(json)
				    });
				    */
			    };

		    }

	    },

	    "_fnCreateInput": function(regex, smart, bIsNumber){

		var sCSSClass = "text_filter";

		if(bIsNumber)
		    sCSSClass = "number_filter";

		var input = $('<input type="text" class="search_init ' + sCSSClass + '" value="' + this._label + '"/>');

		this._th.html(input);

		if(bIsNumber)
		    this._th.wrapInner('<span class="filter_column filter_number" />');
		else
		    this._th.wrapInner('<span class="filter_column filter_text" />');

		this._asInitVals[i] = this._label;
		var index = i;

		if(bIsNumber && !this._oDataTable.fnSettings().oFeatures.bServerSide){

		    input.keyup(function (){
			/* Filter on the column all numbers that starts with the entered value */
			this._oDataTable.fnFilter('^' + this.value, index, true, false);
		    });

		} 
		else {

		    input.keyup(function (){
			/* Filter on the column (the index) of this element */
			this._oDataTable.fnFilter(this.value, index, regex, smart);
		    });

		}

		input.focus(function (){

		    if($(this).hasClass("search_init")){
			$(this).removeClass("search_init");
			this.value = "";
		    }

		});

		input.blur(function (){
		    if(this.value == ""){
			$(this).addClass("search_init");
			this.value = this._asInitVals[index];
		    }
		});

	    },

	    "_fnCreateRangeInput": function (){

		this._th.html(_fnRangeLabelPart(0));

		var sFromId = this._oConfig.sTableId + 'range_from_' + i;
		var from = $('<input type="text" class="number_range_filter" id="' + sFromId + '" rel="' + i + '"/>');

		this._th.append(from);
		this._th.append(_fnRangeLabelPart(1));

		var sToId = this._oConfig.sTableId + 'range_to_' + i;
		var to = $('<input type="text" class="number_range_filter" id="' + sToId + '" rel="' + i + '"/>');

		this._th.append(to);
		this._th.append(_fnRangeLabelPart(2));
		this._th.wrapInner('<span class="filterColumn filter_number_range" />');

		var index = i;

		aiCustomSearch_Indexes.push(i);


		//------------start range filtering function


		/* 	Custom filtering function which will filter data in column four between two values
		*	Author: 	Allan Jardine, Modified by Jovan Popovic
		*/

	    // WTF? Above comment is not correct

		$.fn.dataTableExt.afnFiltering.push(

			function (oSettings, aData, iDataIndex){

			    var iMin = document.getElementById(sFromId).value * 1;
			    var iMax = document.getElementById(sToId).value * 1;
			    var iValue = aData[index] == "-" ? 0 : aData[index] * 1;

			    if(iMin == "" && iMax == ""){
				return true;
			    }
			    else if(iMin == "" && iValue < iMax){
				return true;
			    }
			    else if(iMin < iValue && "" == iMax){
				return true;
			    }
			    else if(iMin < iValue && iValue < iMax){
				return true;
			    }

			    return false;

			}
		);
		//------------end range filtering function



		$('#' + sFromId + ',#' + sToId, this._th).keyup(function (){

		    var iMin = document.getElementById(sFromId).value * 1;
		    var iMax = document.getElementById(sToId).value * 1;
		    if(iMin != 0 && iMax != 0 && iMin > iMax)
			return;

		    this._oDataTable.fnDraw();

		});


	    },

	    "_fnCreateDateRangeInput": function(){

		this._th.html(_fnRangeLabelPart(0));

		var sFromId = this._oConfig.sTableId + 'range_from_' + i;
		var from = $('<input type="text" class="date_range_filter" id="' + sFromId + '" rel="' + i + '"/>');

		from.datepicker();
		this._th.append(from);
		this._th.append(_fnRangeLabelPart(1));

		var sToId = this._oConfig.sTableId + 'range_to_' + i;
		var to = $('<input type="text" class="date_range_filter" id="' + sToId + '" rel="' + i + '"/>');

		this._th.append(to);
		this._th.append(_fnRangeLabelPart(2));
		this._th.wrapInner('<span class="filterColumn filter_date_range" />');
		this._th.datepicker();

		var index = i;

		aiCustomSearch_Indexes.push(i);


		//------------start date range filtering function

		$.fn.dataTableExt.afnFiltering.push(

			function (oSettings, aData, iDataIndex){

			    var dStartDate = from.datepicker("getDate");

			    var dEndDate = to.datepicker("getDate");

			    var dCellDate = $.datepicker.parseDate($.datepicker.regional[""].dateFormat, aData[index]);

			    if(dCellDate == null)
				return false;

			    if(dStartDate == null && dEndDate == null){
				return true;
			    }
			    else if(dStartDate == null && dCellDate < dEndDate){
				return true;
			    }
			    else if(dStartDate < dCellDate && dEndDate == null){
				return true;
			    }
			    else if(dStartDate < dCellDate && dCellDate < dEndDate){
				return true;
			    }

			    return false;

			}
		);
		//------------end date range filtering function

		$('#' + sFromId + ',#' + sToId, this._th).change(function (){
		    this._oDataTable.fnDraw();
		});


	    },

	    "_fnCreateSelect": function(aData){

		var index = i;
		var r = '<select class="search_init select_filter"><option value="" class="search_init">' + this._label + '</option>', j, iLen = aData.length;

		for (j = 0; j < iLen; j++){
		    r += '<option value="' + aData[j] + '">' + aData[j] + '</option>';
		}

		var select = $(r + '</select>');
		this._th.html(select);
		this._th.wrapInner('<span class="filterColumn filter_select" />');

		select.change(function (){
		    //var val = $(this).val();
		    if($(this).val() != ""){
			$(this).removeClass("search_init");
		    } else {
			$(this).addClass("search_init");
		    }
		    this._oDataTable.fnFilter($(this).val(), index);
		});

	    },

	    "_fnRangeLabelPart": function(iPlace){

		switch(iPlace){
		case 0:
		    return sRangeFormat.substring(0, sRangeFormat.indexOf("{from}"));
		case 1:
		    return sRangeFormat.substring(sRangeFormat.indexOf("{from}") + 6, sRangeFormat.indexOf("{to}"));
		default:
		    return sRangeFormat.substring(sRangeFormat.indexOf("{to}") + 4);
		}

	    }    

    };


    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    * Constants
    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

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



///**
//		 * Get the settings for a particular table for external manipulation
//		 *  @returns {object} DataTables settings object. See 
//		 *    {@link DataTable.models.oSettings}
//		 *  @dtopt API
//		 *
//		 *  @example
//		 *    $(document).ready(function(){
//		 *      var oTable = $('#example').dataTable();
//		 *      var oSettings = oTable.fnSettings();
//		 *      
//		 *      // Show an example parameter from the settings
//		 *      alert( oSettings._iDisplayStart );
//		 *    } );
//		 */
//		this.fnSettings = function()
//		{
//			return _fnSettingsFromNode( this[DataTable.ext.iApiIndex] );
//		};
//		


//		/**
//		 * This parameter allows you to override the default function which obtains
//		 * the data from the server ($.getJSON) so something more suitable for your
//		 * application. For example you could use POST data, or pull information from
//		 * a Gears or AIR database.
//		 *  @type function
//		 *  @member
//		 *  @param {string} sSource HTTP source to obtain the data from (sAjaxSource)
//		 *  @param {array} aoData A key/value pair object containing the data to send
//		 *    to the server
//		 *  @param {function} fnCallback to be called on completion of the data get
//		 *    process that will draw the data on the page.
//		 *  @param {object} oSettings DataTables settings object
//		 *  @dtopt Callbacks
//		 *  @dtopt Server-side
//		 * 
//		 *  @example
//		 *    // POST data to server
//		 *    $(document).ready( function(){
//		 *      $('#example').dataTable( {
//		 *        "bProcessing": true,
//		 *        "bServerSide": true,
//		 *        "sAjaxSource": "xhr.php",
//		 *        "fnServerData": function ( sSource, aoData, fnCallback, oSettings ){
//		 *          oSettings.jqXHR = $.ajax( {
//		 *            "dataType": 'json', 
//		 *            "type": "POST", 
//		 *            "url": sSource, 
//		 *            "data": aoData, 
//		 *            "success": fnCallback
//		 *          } );
//		 *        }
//		 *      } );
//		 *    } );
//		 */
//		"fnServerData": function ( sUrl, aoData, fnCallback, oSettings ){
//			oSettings.jqXHR = $.ajax( {
//				"url":  sUrl,
//				"data": aoData,
//				"success": function (json){
//					if( json.sError ){
//						oSettings.oApi._fnLog( oSettings, 0, json.sError );
//					}
//					
//					$(oSettings.oInstance).trigger('xhr', [oSettings, json]);
//					fnCallback( json );
//				},
//				"dataType": "json",
//				"cache": false,
//				"type": oSettings.sServerMethod,
//				"error": function (xhr, error, thrown){
//					if( error == "parsererror" ){
//						oSettings.oApi._fnLog( oSettings, 0, "DataTables warning: JSON data from "+
//							"server could not be parsed. This is caused by a JSON formatting error." );
//					}
//				}
//			} );
//		},