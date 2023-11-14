<?php
ini_set('memory_limit', '512M');
error_reporting(E_ALL); ini_set('display_errors', '0'); 
//error_reporting(E_ALL);

/*! \class database class.db.php "class.db.php"
 *  \brief This class should be used for all database calls.
 */
class database {
	
	function __construct() {
		$this->host    =    '';        /** $this->host    = host database host url or domain */
        $this->username    =    '';            /** $this->username    = username username for the database */
        $this->password    =    '';        /** $this->password    = password password fir read-write database access */
        $this->database    =    '';            /** $this->database    = database namke of the database connecting */
	}
	
	
	/*! \fn obj connect()
		\brief connect to the database and set the UTF.
		\return obj
	*/
	function connect() {
		$connection = $this->mysqli = new mysqli($this->host, $this->username, $this->password, $this->database);
		$connection->query('SET NAMES utf8');		
		$connection->set_charset('utf8mb4');
		/* check connection */
		if (mysqli_connect_errno()) {
			print_r($this);
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
		$this->connection = $connection;
		return $connection;
	}
	
	/*! \fn array get_multi_result(string $sql, bool $count_only)
		\brief execute a query and return multiple results
		\param	$sql	query to be executed
		\param	$count_only	boolean if the results should just hand back a count (defaults to false)	
		\return array
	*/
	function get_multi_result($sql, $count_only=false) {
		$result = $this->mysqli->query($sql);
		$row_cnt = $result->num_rows;
		if($count_only) {
			return $row_cnt;
		};
			if ($row_cnt == 0) {
				$data['empty_result'] = 1;
			} elseif ($row_cnt > 0) {				
				while ($row = $result->fetch_assoc()) {
					$data[] = $row;
				}    		
				$result->free();
			}  else {
				$data['error'] = 'Query Error';
				$data['success'] = false;
				$data['sql'] = $sql;
			}
		return $data;	
	}
	
	/*! \fn array get_single_result($sql)
		\brief execute a query and return a single result
		\param	$sql	query to be executed
		\return array
	*/
	function get_single_result($sql) {
		if ($result = $this->mysqli->query($sql)) {
			$row_cnt = $result->num_rows;
			if ($row_cnt == 0) {
				$data['empty_result'] = 1;
			} else {
				$row = $result->fetch_assoc();
				$data = $row;
			}
    		$result->free();			
		} else {
			$data['error'] = 'Query Error';
			$data['success'] = false;
			$data['sql'] = $sql;
		}
		return $data;	
	}
	
	/*! \fn keep_conn_alive()
		\brief execute a simple query to keep the database connection alive
	*/
	function keep_conn_alive() {
		$result = $this->mysqli->query('select now()');
	}
	
	/*! \fn bool check_if_logged_in()
		\brief check if the user is currently logged into the system
		\return bool
	*/
	function check_if_logged_in($exit=true) {
		if(empty($_SESSION['system_user_id'])) {
			if($exit) {
				echo 'ERROR: User is not logged in.';
				exit;
			} else {
				return false;
			}
		} elseif(!$exit) {
			return true;
		}
	}
	
	/*! \fn bool check_origin()
		\brief checks the origin of a ajax call if invalid returns an "Access Denied"
		\return true or string
	*/
	function check_origin() {
		$source_origin = '';
		if(!empty($_SERVER['HTTP_ORIGIN'])) {
			$source_origin = $_SERVER['HTTP_ORIGIN'];
		} elseif(!empty($_SERVER['HTTP_REFERER'])) {
			$source_origin = $_SERVER['HTTP_REFERER'];
		}
		$source_origin = str_replace('http://', '', str_replace('https://', '', $source_origin));
		if(strpos($source_origin, '/') !== false) {
			$source_origin = strstr($source_origin, '/', true);
		}
		//echo 'source='.$source_origin.'<br>target='.$_SERVER['SERVER_NAME'].'<br>';
		if($source_origin != $_SERVER['SERVER_NAME']) {
			echo 'ERROR: Access denied.';
			exit;
		}
	}
	
	/*!	\fn null setTimeZone()
		\brief returns and sets the timezone for the page when translating epoch times
		\return null
	*/
	function setTimeZone() {
		$usql = "SELECT userTimezone FROM Users WHERE user_id='".$_SESSION['system_user_id']."'";
		//echo $usql;
		$usnd = $this->get_single_result($usql);
		if(isset($usnd['empty_result'])) {
			$tz = 'America/Los_Angeles';
		} else {
			if($usnd['userTimezone'] == '') {
				$tz = 'America/Los_Angeles';	
			} else {
				$tz = $usnd['userTimezone'];
			}
		}
		date_default_timezone_set ($tz);		
	}
	
	function log_user_action($action, $record_id=0, $record_type='', $user_id=0, $action_time=0, $ip_address='') {
		if(empty($user_id)) {
			$user_id = $_SESSION['system_user_id'];
		}
		if(empty($action_time)) {
			$action_time = time();
		}
		if(empty($ip_address)) {
			$ip_address = $_SERVER['REMOTE_ADDR'];
		}
		$ul_sql = "INSERT INTO UserLog ( 
					UserLog_userId,
					UserLog_ipAddress,
					UserLog_date, 
					UserLog_desc, 
					UserLog_recordId, 
					UserLog_recordType
				) VALUES (
					'".$user_id."',
					'".$this->mysqli->escape_string($ip_address)."',
					'".$action_time."',
					'".$this->mysqli->escape_string($action)."',
					'".$record_id."',
					'".$this->mysqli->escape_string($record_type)."'
				)";
		$ul_query = $this->mysqli->query($ul_sql);
	}

}
?>
