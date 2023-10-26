<?php
require_once('class.email.php');

/**
 * SendGrid
 * 
 * 
 * 
 * @package Marketing
 * @author  Jen Skibitsky <dev@kelleher-international.com>
 */
class SendGrid {
	public $debug_msg;
	
	private $api_endpoint;
	private $auth_user;
	private $auth_key;
	private $last_error;
	private $_debug;
	private $_curl_ssl_verify;

	protected $from         = '';               //displayed From email address
	protected $from_name    = '';               //displayed From name,
	protected $sender       = '';               //return path email address
	protected $subject      = '';               //email subject
	protected $body         = '';               //email body content
	protected $text_body    = '';               //a text only version of the body content
	protected $content_type = 'text/plain';     //the email message content type
	protected $to           = array();          //receipient email addresses
	protected $cc           = array();          //carbon copy email addresses
	protected $bcc          = array();          //blind carbon copy email addresses
	protected $reply_to     = array();          //reply to addresses
	protected $attachments  = array();          //filepath for each message attachment

	/**
	 * Timeout in seconds for an API call to respond
	 * @var integer
	 */
	const TIMEOUT = 20;

	/**
	 * User Agent
	 * 
	 * @var string
	 */
	const USER_AGENT = 'Sendgrid PHP API';

	/**
	 * SendGrid API Endpoint
	 * 
	 * @var string
	 */
	const SG_ENDPOINT = 'https://sendgrid.com/api';

	/**
	 * SendGrid SMTP Host
	 * 
	 * @var string
	 */
	const SG_SMTP_HOST = 'smtp.sendgrid.net';

	/**
	 * Creates a new SendGrid Newsletter API object to make calls with
	 *
	 * Your API key needs to be generated using SendGrid Management
	 * Authentication is done automatically when making the first API call
	 * using this object.
	 *
	 * @param	array	$params
	 * 			$params array keys:
	 * 			string 	user 				The username of the account to use
	 * 			string 	key 				The API key to use
	 * 			boolean debug 				Set to true to get debug information (development)
	 * 			boolean curl_ssl_verify 	Set false to disable CURL ssl cert verification
	 */
	function __construct($params) {
		$this->emailer = new Email();
		$this->auth_user = $params['user'];
		$this->auth_key = $params['key'];
		$this->api_endpoint = self::SG_ENDPOINT;
		$this->_debug = ($params['debug'] === true);
		$this->_curl_ssl_verify = ($params['curl_ssl_verify'] === true);
	}

	/**
	 * Send Deployment
	 *
	 *
	 * @param	string	$listfile
	 * @param	string	$from_addr
	 * @param	string	$from_name
	 * @param	string	$reply_addr
	 * @param	string	$subject
	 * @param	string	$html_body
	 * @param	string	$text_body
	 * @param	array	$merge_fields
	 * @param	array	$unique_args
	 * @param	string	$category
	 * @param	int		$offset
	 * @param	int		$limit
	 * @return	bool
	 */
	function send_deployment($listfile, $from_addr, $from_name, $reply_addr, $subject, $html_body, $text_body, $merge_fields, $unique_args, $category, $offset=0, $limit=0) {
		$result = false;
		$failures = array();
		$args = array('x-smtpapi' => array());
		//build the base x-smtpapi header value array: unique_args, category, substitution values
		if(!empty($unique_args)) $args['x-smtpapi']['unique_args'] = $unique_args;
		if(!empty($category)) $args['x-smtpapi']['category'] = $category;

		$args['x-smtpapi']['to'] = array();
		$args['x-smtpapi']['sub'] = array();
		foreach($merge_fields as $fld) $args['x-smtpapi']['sub'][$fld] = array();

		$this->clear();
		$this->set_contenttype('text/html');
		$this->set_from($from_addr, $from_name);
		$this->add_replyto($reply_addr);
		$this->set_subject($subject);
		$this->set_body($html_body);
		$this->set_text_body($text_body);
		//the e-mail's actual To address is discarded by SendGrid when using the X-SMTPAPI to field
		$this->add_to('nobody@kelleher-international.com');

		//open the list file and read it line by line
		$fp = fopen($listfile, 'r');
		if($fp === false) return $result;
		
		//loop through each line
		$count = 0;
		$lines_read = 0;
		while(($line = fgetcsv($fp)) !== false) {
			$lines_read++;
			if($offset > 0 && $lines_read <= $offset) continue;
			if($limit > 0 && $lines_read > ($offset + $limit)) break;

			//add email addresses and subsitution values to the x-smtpapi arrays until the 'to' array contains 500 addresses max
			$args['x-smtpapi']['to'][] = $line[0];

			foreach($merge_fields as $index => $fld) {
				$args['x-smtpapi']['sub'][$fld][] = $line[$index];
			}

			$count++;
			if($count >= 500) {
				$send = $this->send($args);
				if(!$send) $failures[] = $args;
				$result = ($result || $send);

				//send the email via sendgrid, and reset the 'to' and 'subs' arrays, continuing until all list members have been sent to
				$args['x-smtpapi']['to'] = array();
				$args['x-smtpapi']['sub'] = array();
				foreach($merge_fields as $fld) $args['x-smtpapi']['sub'][$fld] = array();
				$count = 0;
			}
		}

		fclose($fp);

		//if the end of the file was reached and there are stil recipients to be sent to, do it now
		if(!empty($args['x-smtpapi']['to'])) {
			$send = $this->send($args);
			if(!$send) $failures[] = $args;
			$result = ($result || $send);
		}

		//if only some of the sends failed, retry them
		if($result === true && !empty($failures)) {
			$tmp = $failures;
			$failures = array();
			foreach($tmp as $fail_args) {
				$send = $this->send($fail_args);
				if(!$send) $failures[] = $fail_args;
			}
		}

		//if there are still some failed sends, send an error notification
		if(!empty($failures)) {
			mail('rich@kelleher-international.com', 
				'SMPL/SendGrid :: Send failed for some deployment recipients', 
				print_r(array('Method' => 'Send_grid::send_deployment', 'Send Args' => '<pre>'.print_r($failures, true).'</pre>'), true), 
				'From: system@kelleher-international.com'
			);
		}

		return $result;
	}

	/**
	 * Send
	 *
	 * Sends an email message 
	 * 
	 * @param   array   $args   optional send operation configuration settings
	 * @return  bool
	 */
	public function send($args=array()) {
		$result = false;
	
		//make sure there is at least one to address
		if(count($this->to) < 1) return $result;

		$this->emailer->initialize(array(
			'protocol' => 'smtp',
			'smtp_port' => 587,
			'smtp_host' => $this->get_smtp_host(),
			'smtp_user' => $this->get_api_user(),
			'smtp_pass' => $this->get_api_password(),
			'crlf' => "\r\n",
			'newline' => "\r\n"
		));

		$this->emailer->set_mailtype($this->content_type == 'text/plain' ? 'text' : 'html');
		$this->emailer->from($this->from, $this->from_name);
		$this->emailer->to($this->to);
		$this->emailer->cc($this->cc);
		$this->emailer->bcc($this->bcc);
		$this->emailer->subject($this->subject);
		$this->emailer->message($this->body);
		$this->emailer->set_alt_message($this->text_body);

		if(!empty($this->reply_to)) {
			$this->emailer->reply_to($this->reply_to[0], $this->from_name);
		}

		if(!empty($this->attachments)) {
			foreach($this->attachments as $filepath) {
				$this->emailer->attach($filepath);
			}
		}

		//if an x-smtpapi arg was passed, format it and add it to the message headers
		if(is_array($args['x-smtpapi']) && !empty($args['x-smtpapi'])) {
			$xsmtp = $this->format_xsmtp_header($args['x-smtpapi']);
			$this->emailer->add_custom_header('X-SMTPAPI', $xsmtp);
		}

		$result = $this->emailer->send();
		$this->debug_msg = $this->emailer->print_debugger();

		return $result;
	}

	/**
	 * Makes a request to the SendGrid API
	 *
	 * @param 	string 	$url The relative URL to call (example: "server")
	 * @param 	array 	$postData (optional) The JSON string to send
	 * @param 	string 	$method (optional) The HTTP method to use
	 * @return 	array 	The parsed response, or NULL if there was an error
	 */
	public function send_request($url, $postData=array(), $method='POST') {
		if(!is_array($postData)) $postData = array();
		$postData['api_user'] = $this->auth_user;
		$postData['api_key']  = $this->auth_key;

		$this->debug('DEBUG - Post Data: ' , $postData);
		$url .= ".json";
		$json_url = $this->api_endpoint.'/'.$url;
		
		// Generate curl request
		$session = curl_init($json_url);
		$this->debug('DEBUG - Curl Session: ' , $session);

		//Set to FALSE to stop cURL from verifying the peer's certificate (needed for local hosts development mostly)
		if(!$this->_curl_ssl_verify) curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
		
		// Tell curl to use HTTP POST
		curl_setopt($session, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		// Tell curl that this is the body of the POST
		curl_setopt($session, CURLOPT_POSTFIELDS, $postData);
		// Tell curl not to return headers, but do return the response
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_USERAGENT, self::USER_AGENT);
		curl_setopt($session, CURLOPT_ENCODING, 'gzip,deflate');
		curl_setopt($session, CURLOPT_TIMEOUT, self::TIMEOUT);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

		// obtain response
		$response = curl_exec($session);
		curl_close($session);
		$this->debug('DEBUG - Json Response: ' , $response);
		$results = json_decode($response, TRUE);
		$this->debug('DEBUG - Results: ', $results);

		$this->last_error = isset($results['error']) ? $results['error'] : NULL;

		return $this->last_error ? false : $results;
	}

	/**
	 * Makes a print out of every step of send_request for DEBUGGING
	 *
	 * @param 	string 			$text The text to show before the actual debug information EX: DEBUG - Results: 
	 * @param 	string|array 	$data the actual debug data to show
	 */
	private function debug($text = 'DEBUG : ' , $data){
		if(!$this->_debug) return;
		$newLine = isset($_SERVER['HTTP_USER_AGENT']) ? "<br/>" : "\n";

		echo $newLine . $text;
		if(is_array($data)) {
			foreach($data as $name => $value) {
				if($name == 'api_user' || $name == 'api_key') continue;
				echo $newLine.$name.' => '.$value;
			}

			echo $newLine;
		}else {
			echo $data.$newLine;
		}
	}

	/**
	 * Format X-SMTPAPI Header
	 *
	 *
	 * @param  	array	$data
	 * @param	bool	$wrap_lines
	 * @return 	string
	 */
	public function format_xsmtp_header($data, $wrap_lines=true) {
		$json = json_encode($data);
		if($wrap_lines) {
			// Add spaces so that the field can be wrapped
			$json = preg_replace('/(["\]}])([,:])(["\[{])/', '$1$2 $3', $json);
			$json = wordwrap($json, 72, "\n   ");
		}

		return $json;
	}
	
	/**
	 * Clear
	 * 
	 * Clears all the email variables, ect to allow the sending of another email
	 * 
	 * @param 
	 * @return 
	 */
	public function clear() {
		$this->from         = '';
		$this->from_name    = '';
		$this->cc           = array();
		$this->bcc          = array();
		$this->reply_to     = array();
		$this->to           = array();
		$this->attachments  = array();
		$this->set_subject('');
		$this->set_sender('');
		$this->set_body('');
		$this->set_text_body('');
		$this->emailer->_attach_name = array();
		$this->emailer->_attach_type = array();
		$this->emailer->_attach_disp = array();
	}
	
	/**
	 * Add To
	 * 
	 * Adds a receipient for the message
	 * Ensures that it is a valid email address and is not already added
	 * 
	 * @param   string  $address
	 * @return  bool
	 */
	public function add_to($address) {
		$address = trim($address);
	
		if(in_array($address, $this->to) || !$this->valid_email($address, false)) {
			return false;
		} else {
			$this->to[] = $address;
		}
	}
	
	/**
	 * Add CC
	 * 
	 * Adds a CC receipient for the message
	 * Ensures that it is a valid email address and is not already added
	 * 
	 * @param   string  $address
	 * @return  bool
	 */
	public function add_cc($address) {
		$address = trim($address);
	
		if(in_array($address, $this->cc) || !$this->valid_email($address, false)) {
			return false;
		} else {
			$this->cc[] = $address;
		}
	}
	
	/**
	 * Add BCC
	 * 
	 * Adds a BCC receipient for the message
	 * Ensures that it is a valid email address and is not already added
	 * 
	 * @param   string  $address
	 * @return  bool
	 */
	public function add_bcc($address) {
		$address = trim($address);
	
		if(in_array($address, $this->bcc) || !$this->valid_email($address, false)) {
			return false;
		} else {
			$this->bcc[] = $address;
		}
	}
	
	/**
	 * Add Reply To
	 * 
	 * Adds a Reply To address for the message
	 * Ensures that it is a valid email address and is not already added
	 * 
	 * @param   string  $address
	 * @return  bool
	 */
	public function add_replyto($address) {
		$address = trim($address);
	
		if(in_array($address, $this->reply_to) || !$this->valid_email($address, false)) {
			return false;
		} else {
			$this->reply_to[] = $address;
		}
	}
	
	/**
	 * Add Attachment
	 * 
	 * Adds a file attachment to the message
	 * 
	 * @param   string  $file_path  the absolute server path to the file
	 * @return  bool
	 */
	public function add_attachment($file_path) {
		$this->attachments[] = $file_path;
	}

	/**
	 * Set from
	 * 
	 * Sets the from address and name for the email
	 * 
	 * @param   string  $address
	 * @param   string  $name
	 * @return  void
	 */
	public function set_from($address, $name) {
		$this->from      = $address;
		$this->from_name = $name;
	}
	
	/**
	 * Set Sender
	 * 
	 * Sets the sender address (return path) for the email
	 * 
	 * @param   string  $address
	 * @return  void
	 */
	public function set_sender($address) {
		$this->sender = $address;
	}
	
	/**
	 * Set subject
	 * 
	 * Sets the subject of the email
	 * 
	 * @param   string  $subject
	 * @return  void
	 */
	public function set_subject($subject) {
		$this->subject = $subject;
	}
	
	/**
	 * Set Body
	 * 
	 * Sets the body content of the email
	 * 
	 * @param   string  $body
	 * @return  void
	 */
	public function set_body($body) {
		$this->body = $body;
	}
	
	/**
	 * Set Text Body
	 * 
	 * Sets the text-only version of the body content
	 * 
	 * @param   string  $body
	 * @return  void
	 */
	public function set_text_body($text_body) {
		$this->text_body = $text_body;
	}
	
	/**
	 * Set content type
	 * 
	 * Set the content type of the email
	 * 
	 * @param   string  $type
	 * @return  void
	 */
	public function set_contenttype($type) {
		$this->content_type = $type;
	} 

	/**
	 * Valid email
	 *
	 * Email address validator
	 *
	 * @param   string  $email
	 * @param   bool    $check_dns  set to true to check if the address's domain exists in dns
	 * @return  bool
	 */ 
	public function valid_email($email, $check_dns=false) {
		$isValid = true;
		$atIndex = strrpos($email, "@");
		
		if(is_bool($atIndex) && !$atIndex)  {
			$isValid = false;
		} else {
			$domain = substr($email, $atIndex+1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			
			if ($localLen < 1 || $localLen > 64) {
				// local part length exceeded
				$isValid = false;
			} else if($domainLen < 1 || $domainLen > 255) {
				// domain part length exceeded
				$isValid = false;
			} else if($local[0] == '.' || $local[$localLen-1] == '.') {
				// local part starts or ends with '.'
				$isValid = false;
			} else if(preg_match('/\\.\\./', $local)) {
				// local part has two consecutive dots
				$isValid = false;
			} else if(!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
				// character not valid in domain part
				$isValid = false;
			} else if(preg_match('/\\.\\./', $domain)) {
				// domain part has two consecutive dots
				$isValid = false;
			} else if(!preg_match('/\./', $domain) || preg_match('/^\./', $domain)) {
				// domain must have at least one dot, but not at the beginning
				$isValid = false;
			} else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
				// character not valid in local part unless 
				// local part is quoted
				if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
					$isValid = false;
				}
			}
			
			if($check_dns) {
				if($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
					// domain not found in DNS
					$isValid = false;
				}
			}
		}
		
		return $isValid;
	}

	/**
	 * Get last error
	 *
	 *
	 * @return string
	 */
	function get_last_error() {
		return $this->last_error;
	}

	/**
	 * Get API username
	 *
	 *
	 * @return string
	 */
	function get_api_user() {
		return $this->auth_user;
	}

	/**
	 * Get API password
	 *
	 *
	 * @return string
	 */
	function get_api_password() {
		return $this->auth_key;
	}

	/**
	 * Get SMTP Host
	 *
	 *
	 * @return string
	 */
	function get_smtp_host() {
		return self::SG_SMTP_HOST;
	}
}