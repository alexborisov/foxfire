<?php

/**
 * BP-MEDIA DATABASE UTILITY CLASS
 * Provides utility functions for the main database classes
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package FoxFire
 * @subpackage Database
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/wiki/DOCS_FOX_db_top
 *
 * ========================================================================================================
 */

class FOX_dbUtil {


	var $db;			// Local copy of WP's database singleton


	// ============================================================================================================ //


	function FOX_dbUtil($args=null) {

		$this->__construct($args);
	}


	function __construct($args=null) {

		// Handle dependency-injection for unit tests
		if($args){

			$this->db = &$args['db'];
		}
		else {
			global $wpdb;
			$this->db = &$wpdb;
		}

	}


	/**
	 * Checks if a string is a SQL reserved word (illegal to use as a column name)
	 *
	 * @param string $key | String to check
	 * @return bool $result | True if reserved word. False if not.
	 */

	 public function isReservedWord($key)
	 {


		$check = strtoupper($key);

		// Reserved word list from: http://dev.mysql.com/doc/refman/5.6/en/reserved-words.html
		// ==============================================================================================================

		$reserved = array(

			"ACCESSIBLE","ADD","ALL","ALTER","ANALYZE","AND","AS","ASC","ASENSITIVE",
			"BEFORE","BETWEEN","BIGINT","BINARY","BLOB","BOTH","BY",
			"CALL","CASCADE","CASE","CHANGE","CHAR","CHARACTER","CHECK","COLLATE","COLUMN","CONDITION","CONSTRAINT","CONTINUE",
			"CONVERT","CREATE","CROSS","CURRENT_DATE","CURRENT_TIME","CURRENT_TIMESTAMP","CURRENT_USER","CURSOR",
			"DATABASE","DATABASES","DAY_HOUR","DAY_MICROSECOND","DAY_MINUTE","DAY_SECOND","DEC","DECIMAL","DECLARE","DEFAULT",
			"DELAYED","DELETE","DESC","DESCRIBE","DETERMINISTIC","DISTINCT","DISTINCTROW","DIV","DOUBLE","DROP","DUAL",
			"EACH","ELSE","ELSEIF","ENCLOSED","ESCAPED","EXISTS","EXIT","EXPLAIN",
			"FALSE","FETCH","FLOAT","FLOAT4","FLOAT8","FOR","FORCE","FOREIGN","FROM","FULLTEXT",
			"GENERAL","GRANT","GROUP",
			"HAVING","HIGH_PRIORITY","HOUR_MICROSECOND","HOUR_MINUTE","HOUR_SECOND",
			"IF","IGNORE","IGNORE_SERVER_IDS","IN","INDEX","INFILE","INNER","INOUT","INSENSITIVE","INSERT","INT","INT1","INT2","INT3","INT4","INT8",
			"INTEGER","INTERVAL","INTO","IS","ITERATE",
			"JOIN","KEY","KEYS","KILL",
			"LEADING","LEAVE","LEFT","LIKE","LIMIT","LINEAR","LINES","LOAD","LOCALTIME","LOCALTIMESTAMP","LOCK","LONG","LONGBLOB",
			"LONGTEXT","LOOP","LOW_PRIORITY",
			"MASTER_BIND","MASTER_HEARTBEAT_PERIOD","MASTER_SSL_VERIFY_SERVER_CERT","MATCH","MAXVALUE","MEDIUMBLOB","MEDIUMINT","MEDIUMTEXT",
			"MIDDLEINT","MINUTE_MICROSECOND","MINUTE_SECOND","MOD","MODIFIES",
			"NATURAL","NOT","NO_WRITE_TO_BINLOG","NULL","NUMERIC",
			"ON","ONE_SHOT","OPTIMIZE","OPTION","OPTIONALLY","OR","ORDER","OUT","OUTER","OUTFILE",
			"PARTITION","PRECISION","PRIMARY","PROCEDURE","PURGE",
			"RANGE","READ","READS","READ_WRITE","REAL","REFERENCES","REGEXP","RELEASE","RENAME","REPEAT","REPLACE","REQUIRE","RESIGNAL",
			"RESTRICT","RETURN","REVOKE","RIGHT","RLIKE",
			"SCHEMA","SCHEMAS","SECOND_MICROSECOND","SELECT","SENSITIVE","SEPARATOR","SET","SHOW","SIGNAL","SLOW","SMALLINT","SPATIAL",
			"SPECIFIC","SQL","SQLEXCEPTION","SQLSTATE","SQLWARNING","SQL_BIG_RESULT","SQL_CALC_FOUND_ROWS","SQL_SMALL_RESULT",
			"SSL","STARTING","STRAIGHT_JOIN",
			"TABLE","TERMINATED","THEN","TINYBLOB","TINYINT","TINYTEXT","TO","TRAILING","TRIGGER","TRUE",
			"UNDO","UNION","UNIQUE","UNLOCK","UNSIGNED","UPDATE","USAGE","USE","USING","UTC_DATE","UTC_TIME","UTC_TIMESTAMP",
			"VALUES","VARBINARY","VARCHAR","VARCHARACTER","VARYING",
			"WHEN","WHERE","WHILE","WITH","WRITE",
			"XOR",
			"YEAR_MONTH",
			"ZEROFILL"
		);

		if( array_search($check, $reserved) !== false){

			return true;
		}
		else {
			return false;
		}


	 }


	/**
         * Returns all major statistics for the current SQL server
         *
         * @version 0.1.9
         * @since 0.1.9
         * @return bool | Exception on failure. Data array on success
         */

	public function getServerStatus($data_group){


		$query = "SHOW STATUS";
		
		try {
			$db_result = $this->db->get_results($query);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error running SHOW STATUS query",
				'data'=>array("data_group"=>$data_group),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		$master = array();

		foreach($db_result as $obj){

			$master[$obj->Variable_name] = $obj->Value;
		}
		unset($obj);

		// NOTE: There are over 600 different values being tracked in
		// the $master array, so we have to provide a method of filtering them
		// ==============================================================================


		switch($data_group){

			case "dashboard" : {

				$group_keys = array(
							'Bytes_received','Bytes_sent', 'Compression','Connections','Created_tmp_files',
							'Innodb_data_pending_writes','Innodb_data_read','Innodb_data_reads','Innodb_data_writes',
							'Innodb_data_written','Threads_connected','Threads_created','Threads_running','Uptime'
				);

			} break;

			case "caching" : {

				$group_keys = array(
							'Binlog_cache_use','Created_tmp_disk_tables','Created_tmp_files','Created_tmp_tables','Delayed_insert_threads',
							'Delayed_writes','Flush_commands','Innodb_buffer_pool_pages_data','Innodb_buffer_pool_pages_dirty',
							'Innodb_buffer_pool_pages_flushed','Innodb_buffer_pool_pages_free','Innodb_buffer_pool_pages_misc',
							'Innodb_buffer_pool_pages_total','Innodb_buffer_pool_read_ahead_rnd','Innodb_buffer_pool_read_ahead_seq',
							'Innodb_buffer_pool_read_requests','Innodb_buffer_pool_reads','Innodb_buffer_pool_wait_free',
							'Innodb_buffer_pool_write_requests','Innodb_data_fsyncs','Innodb_row_lock_current_waits', 'Innodb_row_lock_time',
							'Innodb_row_lock_time_avg','Innodb_row_lock_time_max','Slow_queries'
				);

			} break;

			case "bandwidth" : {

				$group_keys = array(
							'Bytes_received','Bytes_sent', 'Compression','Connections', 'Innodb_data_written',
							'Innodb_os_log_written', 'Max_used_connections', 'Threads_connected','Threads_created','Threads_running'
				);

			} break;

			case "memory" : {

			} break;

			case "disk" : {

				$group_keys = array(
							'Created_tmp_files', 'Binlog_cache_disk_use', 'Created_tmp_disk_tables', 'Created_tmp_files',
							'Created_tmp_tables', 'Delayed_writes', 'Open_files', 'Open_streams', 'Open_table_definitions',
							'Open_tables', 'Opened_files'
				);

			} break;

			// Trap nonexistent data groups
			// =============================

			default : {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Not a recognized data group. \n",
					'data'=>array("data_group"=>$data_group),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}


		} //ENDOF: switch($data_group)

		
		$result = array();

		foreach($group_keys as $key){

			$result[$key] = $master[$key];
		}
		unset($key);


		return $result;

	}
	
	

} // End of class FOX_dbUtil

?>