<?php




use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once 'includes/phpmailer/src/Exception.php';
require_once 'includes/phpmailer/src/PHPMailer.php';
require_once 'includes/phpmailer/src/SMTP.php';
require_once 'class.sendgrid.php';


if(!class_exists('database'))           require_once('class.db.php');
if(!class_exists('Settings'))          	require_once('class.settings.php');
if(!class_exists('SSH_Settings'))       require_once('class.ssh_settings.php');

/**
 * class.marketing.php
 * 
 * Handles general marketing tool functions and processes
 * 
 * @package Marketing
 * @author  Jen Skibitsky <dev@kelleher-international.com>
 * 
 */
class Marketing
{

    public $test_data = array
    (
        '#FirstName'      => 'John',
        '#LastName'       => 'Doe',
        '#Salutation'     => 'Mr.',
        '#Email'          => 'jdoe@example.com',
        '#Company'        => 'Acme, Inc.',
        '#Password'       => 'Password',
        '#UserName'       => 'Username',
        '#OptOutLink'     => 'http://www.example.com/unsubscribe',
        '#OptInReason'    => 'You are receiving this e-mail because you provided your e-mail address to us via a web form or some other external means.',
        '#MailingAddress' => 'My Company, 123 Main Street, Placeholderville, NY 12345',
		'#pid'			  => '0'
    );
    
    public $optin_reason = 'You are receiving this e-mail because you provided your e-mail address to us via a web form or some other external means.';
	public $merge_fields;
    
    public $esp;
    public $list_file_path;
    public $phpmailer;
    public $optout_url;
    public $approve_url;
    
    private $ws;
    private $notify_address = 'rich@kelleher-international.com';
	private $ssh_obj;
    private $ssh_host;
    private $ssh_user;
    private $ssh_password;
	private $db;
    private $settings;
    private $settings_obj;
    private $sendgrid;

    /**
     * Marketing class constructor
     * 
     * 
     * @return void
     */
    public function __construct()
    {
		global $DB;
		if(!$DB) {
			$this->db = new database();
			$this->db->connect();
		} else {
			$this->db = $DB;
		}
		$this->settings_obj   = new Settings();
		$this->settings       = $this->settings_obj->setting;
        $this->esp            = (array_key_exists('ESP', $this->settings) && $this->settings['ESP'] != '') ? strtolower(trim($this->settings['ESP'])) : 'local';
		$this->merge_fields   = $this->settings['MERGE_FIELDS'];
		
        $this->list_file_path = $this->settings['SERVER_PATH'].'deploy';
        $this->optout_url     = $this->settings['BASE_URL']."unsubscribe.php";
        $this->approve_url    = $this->settings['BASE_URL']."approve.php";
		
		$this->ssh_obj   	  = new SSH_Settings();
		$this->ssh_host		  = $this->ssh_obj->get_host();
		$this->ssh_user		  = $this->ssh_obj->get_user();
		$this->ssh_password	  = $this->ssh_obj->get_password();

        $this->phpmailer = new PHPMailer(true);

        if($this->settings['SENDGRID_USERNAME'] != '' && $this->settings['SENDGRID_PASSWORD'] != '')
        {
            $this->sendgrid = new SendGrid(array('user' => $this->settings['SENDGRID_USERNAME'], 'key' => $this->settings['SENDGRID_PASSWORD']));
        }
     
    }
    
    /**
     * Send one-off e-mail
     * 
     * Sends a one-off email either via SendGrid (if enabled)
     * or through the normal local system mailer
     * 
     * @param   string  $subject
     * @param   array   $to_addresses
     * @param   array   $cc_addresses
     * @param   array   $bcc_addresses
     * @param   string  $from_name
     * @param   string  $from_address
     * @param   string  $reply_to
     * @param   string  $html_body
     * @param   string  $text_body
     * @param   int     $person_id
     * @param   int     $deploy_id
     * @param   string  $merge_data
     * @param   bool    $local_send         set to true to send via PHPMailer even if an external ESP is enabled for the system
     * @param   bool    $dont_log           set to true to prevent storing this e-mail in the comm history
     * @param   bool    $receipt            set to true to request a read reciept
     * @param   bool    $set_replyto        set to true to add the reply to setting to the reply-to header of a local email
     * @param   array   $attachments        an array of files (using absolute file paths) to attach to the email
     * @return  bool|int                    returns 'true' if email was successfully sent, 'false' if not
     */
    public function send_oneoff_email($subject, $to_addresses, $cc_addresses, $bcc_addresses, $from_name, $from_address, $reply_to, $html_body, $text_body, $person_id=0, $deploy_id=0, $merge_data='', $local_send=false, $dont_log=false, $receipt=false, $set_replyto=false, $attachments=array(), $unique_args=array(), $send_time='')
    {
		if($this->esp !== 'local' && !$local_send && $this->domain_whitelisted($from_address))
        {
            //Create an instance; passing `true` enables exceptions
            $mail = new PHPMailer(true);
            //Server settings  
            $mail->isSMTP();
            $mail->Host       = "smtp.sendgrid.net";                   //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $this->settings['SENDGRID_USERNAME'];                     //SMTP username
            $mail->Password   = $this->settings['SENDGRID_PASSWORD'];                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            //Recipients
            $mail->setFrom($from_address, $from_name);
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->msgHTML($html_body);
            $mail->AltBody = $text_body;

            //if($set_replyto)
            if (strlen($reply_to) > 0) {
                $mail->addReplyTo($reply_to);
            }

            foreach ($to_addresses as $addr) {
                $mail->addAddress($addr);
            }

            foreach ($cc_addresses as $email) {
                $mail->addCC($email);
            }

            foreach ($bcc_addresses as $email) {
                $mail->addBCC($email);
            }
           
            if (is_array($attachments)) {
                foreach ($attachments as $filepath) {
                    $mail->addAttachment($filepath);         //Add attachments
                }
            }
            
            if ($mail->Send()) {
                //record to comm history if a person id is specified
                if ($person_id > 0 && !$dont_log) {
                    $this->log_communication($person_id, $html_body, $subject, $from_address, $to_addresses[0], $deploy_id, $send_time, 'EMAIL', 'SENT', 'SES', $msg_id);
                }
                echo "Ok, Sent!\n";
                $result = true;
            } else {
                $msg = "ERROR MESSAGE: " . $e->errorMessage() . " \n\nClient: Kelleher International\nScript Name: " . $_SERVER['SCRIPT_NAME'] . "\nRequest URI: " . $_SERVER['REQUEST_URI'] . "\nFrom Email: " . $from_address . "\nSubject:" . $subject . "\nBody:\n" . $html_body;
                $this->send_error_msg('KIMS: Error Sending Email', $msg, 'rich@kelleher-international.com');
                error_log($msg);

                echo 'Mailer Error: ' . $mail->ErrorInfo;
                $result = false;
            }
        }
        else {
            //if the email is being sent through the local server/phpmailer only because the from domain isn't whitelisted, send an error notification
            if ($this->esp !== 'local' && !$local_send) {
                $msg = "A transactional (one-off) email was sent with a from address that is not whitelisted to send via the external ESP, and was sent from the local server instead.";
                $msg .= "\n\nClient: Kelleher International\nScript Name: " . $_SERVER['SCRIPT_NAME'] . "\nRequest URI: " . $_SERVER['REQUEST_URI'] . "\nFrom Email: " . $from_address . "\nSubject:" . $subject . "\nBody:\n" . $html_body;
                $this->send_error_msg('Email From Address Not Whitelisted', $msg, 'rich@kelleher-international.com');
            }

            $mail = new PHPMailer(true);
            //Server settings  
            $mail->isSMTP();
            $mail->Host       = "smtp.sendgrid.net";                   //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $this->settings['SENDGRID_USERNAME'];                     //SMTP username
            $mail->Password   = $this->settings['SENDGRID_PASSWORD'];                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            $mail->SMTPDebug  = true;
            $mail->Debugoutput = 'echo';  
            //Recipients
            $mail->setFrom($from_address, $from_name);

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->msgHTML($html_body);
            $mail->AltBody = $text_body;

            //if($set_replyto)
            if (strlen($reply_to) > 0) {
                $mail->addReplyTo($reply_to);
            }

            foreach ($to_addresses as $addr) {
                $mail->addAddress($addr);
            }

            foreach ($cc_addresses as $email) {
                $mail->addCC($email);
            }

            foreach ($bcc_addresses as $email) {
                $mail->addBCC($email);
            }

            if (is_array($attachments)) {
                foreach ($attachments as $filepath) {
                    $mail->addAttachment($filepath);         //Add attachments
                }
            }

           if ($mail->Send()) {
                if ($person_id > 0 && !$dont_log) {
                    $this->log_communication($person_id, $html_body, $subject, $from_address, $to_addresses[0], $deploy_id);
                }
                echo "Ok, Sent!\n";
                $result = true;
            } else {
                $msg = "ERROR MESSAGE: \n\nClient: Kelleher International\nScript Name: " . $_SERVER['SCRIPT_NAME'] . "\nRequest URI: " . $_SERVER['REQUEST_URI'] . "\nFrom Email: " . $from_address . "\nSubject:" . $subject . "\nBody:\n" . $html_body;
                $this->send_error_msg('KIMS: Error Sending Email', $msg, 'rich@kelleher-international.com');
                error_log($msg);
                $result = false;
            }
            
        }
        
        return $result;
    }
    
    /**
     * Start deployment
     * 
     * 
     * @param   int     $deploy_id
     * @param   int     $offset
     * @return  bool 
     */
    public function start_deployment($deploy_id, $offset=0)
    {
        $start_date = time();
        $result = true;
        $del_listfile = true;
        $json=[];

        //get deployment data
        $data = $this->get_deployment($deploy_id);
        if(empty($data)) return false;
        
        $sql = "UPDATE MarketingDeployments SET MarketingDeployments_status = 'In Progress' WHERE MarketingDeployments_id = '".$this->db->mysqli->escape_string($deploy_id)."' LIMIT 1";
       $update = $this->db->mysqli->query($sql);

            if($update) {
                $json['success'] = true;	
            } else {
                $json['success'] = false;
                $json['error'] = $this->db->mysqli->error;
                echo json_encode($json);
            }
    	
        //create a file of all email addresses to send to
		$create_list = $this->create_emails_file($deploy_id);

		if($create_list === false)
		{
			$this->db_reconnect();
			$this->reset_deployment($deploy_id);
             $json['success'] = false;
                $json['error'] ='create list error';
                echo json_encode($json);
            
			return false;
		}
		else
		{
			$filename = $create_list['filename'];
			$total_recipients = $create_list['count'];
			$this->db_reconnect();
			$sql   = "UPDATE MarketingDeployments SET MarketingDeployments_totalRecip = '".$this->db->mysqli->escape_string($total_recipients)."' WHERE MarketingDeployments_id = '".$this->db->mysqli->escape_string($deploy_id)."' LIMIT 1";
			$query = $this->db->mysqli->query($sql);
            if($query) {
                $json['success'] = true;	
            } else {
                $json['success'] = false;
                $json['error'] = $this->db->mysqli->error;
                echo json_encode($json);
            } 
		}
        
        switch($data['MarketingDeployments_ESP'])
        {
            //SendGrid deployment
            case 'sendgrid':
                //ensure that the from address is on the domain whitelist
                if(!$this->domain_whitelisted($data['MarketingDeployments_fromEmail']))
                {
                    $msg = "A mass e-mail deployment was halted and reset to Pending because the from address is not whitelisted to send via the external ESP";
                    $msg .= "\n\nClient: Kelleher International\nFrom Email: ".$data['MarketingDeployments_fromEmail']."\nSubject: ".$data['MarketingDeployments_subject']."\nDeployment Name: ".$data['MarketingDeployments_name']."\nDeployment ID: ".$deploy_id;
                    $this->send_error_msg('Email Not Whitelisted', $msg, 'rich@kelleher-international.com');
                    $this->db_reconnect();
                    $this->reset_deployment($deploy_id);
                    return false;
                }

                //insert Communication History records before sending
                if($offset === 0)
                {
                    $this->record_deployment_comms($data, $filename);
                }

                $html_body = $this->prepare_email_body($data['MarketingDeployments_bodyHTML'], $deploy_id, 'html', '%%', '%%', true);
                $text_body = $this->prepare_email_body($data['MarketingDeployments_bodyText'], $deploy_id, 'text', '%%', '%%', true);

                $result = $this->sendgrid->send_deployment(
                    $this->list_file_path.'/'.$filename, 
                    $data['MarketingDeployments_fromEmail'], 
                    $data['MarketingDeployments_fromName'], 
                    $data['MarketingDeployments_replyTo'], 
                    $data['MarketingDeployments_subject'], 
                    $html_body, 
                    $text_body, 
                    $this->get_substitution_fields('%%', '%%'), 
                    array('deployment_id' => $deploy_id), 
                    $data['MarketingDeployments_name'],
                    $offset, 
                    0
                );

               if($result !== false) {
                    //mark the successfully started deployment as "sent"
                    $this->db_reconnect();
                    $sql   = "UPDATE MarketingDeployments SET MarketingDeployments_status = 'Sent', MarketingDeployments_dateSent = '".time()."' WHERE MarketingDeployments_id = '".$this->db->mysqli->escape_string($deploy_id)."' LIMIT 1";
                    $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
               
                    if($query) {
                        $json['success'] = true;	
                    } else {
                        $json['success'] = false;
                        $json['error'] = $this->db->mysqli->error;
                        echo json_encode($json);
                    } 
               
                }
                else {
                    $json['success'] = false;
                    $json['error'] = 'Send Deployment';
                    echo json_encode($json);
                }
				break;
            //local deployment
            default:
                //open the created e-mail list file
                $fp = fopen($this->list_file_path.'/'.$filename, 'r');
                
                //loop through each line
                while(($line = fgetcsv($fp)) !== false)
                {
                    $mdata = $this->merge_fields;
                    $count = 0;
                    //get the merge data for the current e-mail address
                    foreach($mdata as $field => $db)
                    {
                        $mdata[$field] = $line[$count + 1];
                        $count++;
                    }
                     
                    //get the person ID from the line
                    $person_id = end($line);

                    $html_body = $this->prepare_email_body($data['MarketingDeployments_bodyHTML'], $deploy_id, 'html', '#', '', false);
                    $text_body = $this->prepare_email_body($data['MarketingDeployments_bodyText'], $deploy_id, 'text', '#', '', false);

                    $html_body = $this->replace_mergefields($deploy_id, $html_body, $person_id, false, false);
                    $text_body = $this->replace_mergefields($deploy_id, $text_body, $person_id, false, false);
                    //append opens tracking image to html body
                    $img_url = $this->settings['BASE_URL']."img.open.php?DID=".$deploy_id."&Email=".$line[0];
                    $html_body .= mb_convert_encoding('<img src="'.$img_url.'" width="1" height="1" />', "HTML-ENTITIES", 'UTF-8');
					$reply_to = $data['MarketingDeployments_replyTo'];
                    //send a "one-off" email via phpmailer
                    $send_result = $this->send_oneoff_email($data['MarketingDeployments_subject'], array($line[0]), array(), array(), $data['MarketingDeployments_fromName'], $data['MarketingDeployments_fromEmail'], $reply_to, $html_body, $text_body, $person_id, $deploy_id, serialize($mdata), true);
                }
                
                $this->db_reconnect();
                //deployment is done so mark as sent
                $sql   = "UPDATE MarketingDeployments SET MarketingDeployments_status = 'Sent' WHERE MarketingDeployments_id = '".$this->db->mysqli->escape_string($deploy_id)."'";
                $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
                break;
        }

        if($result === false)
        {
            $this->db_reconnect();
            $this->reset_deployment($deploy_id);
            return false;
        }
        
        //delete the e-mail list file
        if($del_listfile === true && strlen($filename) > 0 && file_exists($this->list_file_path.'/'.$filename))
        {
            @unlink($this->list_file_path.'/'.$filename);
        }
        
        return $result;
    }

    /**
     * Schedule deployment
     *
     * Schedules the deployment for the specified date via a server 'at' job
     *
     * @param   int     $deploy_id
     * @param   int     $sched_date
     * @return  bool
     */
    public function schedule_deployment($deploy_id, $sched_date)
    {
        $result = false;
        $data = $this->get_deployment($deploy_id);
		//echo "deploy id=$deploy_id<br>";
		//echo "deployment=".print_r($data);
		//echo "sched date=$sched_date<br>";
        if(empty($data)) return $result;
        if(!is_numeric($sched_date) || $sched_date < (time() + 3600)) return $result;
		
        $job_id = $this->schedule_job("curl -i -d 'deploy_id=".$deploy_id."' ".$this->settings['BASE_URL']."ajax/deployment.php", $sched_date);
		//echo "job id=$job_id<br>";
        $result = (!empty($job_id));
      
        if($result)
        {
            $sql = "
                UPDATE 
                    MarketingDeployments 
                SET 
                    MarketingDeployments_dateSched = '".$sched_date."',
                    MarketingDeployments_status = 'Scheduled', 
                    MarketingDeployments_jobID = '".$this->db->mysqli->escape_string($job_id)."'
                WHERE 
                    MarketingDeployments_id = '".$this->db->mysqli->escape_string($deploy_id)."'
                LIMIT 1";

            $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        }

        return $result;
    }
	
	/**
     * Cancel deployment
     * 
     * Cancels a deployment that has been scheduled
     * 
     * @param   int $deploy_id
     * @return  bool
     */
    public function cancel_deployment($deploy_id)
    {
        //get deployment data
        $data = $this->get_deployment($deploy_id);
        if(empty($data) || empty($data['MarketingDeployments_jobID'])) return false;
        
        $result = $this->remove_job($data['MarketingDeployments_jobID']);
        if($result) $this->reset_deployment($deploy_id);

        return $result;
    }

    /**
     * Get next send date
     *
     * Returns the date when the next chunk of emails should be delivered
     * for a Throttled Sending enabled deployment
     *
     * @param   int     $prev_date
     * @param   bool    $skip_weekends
     * @return  int
     */
    function get_next_send_date($prev_date, $skip_weekends) {
        $increment = 86400;
        //calculate the date based on the server's timezone
        $current_tz = date_default_timezone_get();
        date_default_timezone_set($this->settings['SERVER_TIMEZONE']);

        $timestamp = $prev_date + $increment;
        $day = strtolower(date('D', $timestamp));

        if($skip_weekends && $day == 'sat') {
            $timestamp = $prev_date + ($increment * 3);
        } else if($skip_weekends && $day == 'sun') {
            $timestamp = $prev_date + ($increment * 2);
        }

        date_default_timezone_set($current_tz);

        return $timestamp;
    }
    
    /**
     * Reset deployment
     * 
     * Resets a deployment back to "Pending" status and removes any associated MarketingDeploymentVerify records
     * 
     * @param   int $deploy_id
     * @return  void
     */
    public function reset_deployment($deploy_id)
    {
        //deployment failed so reset status to "Pending"
        $sql = "UPDATE MarketingDeployments SET MarketingDeployments_status = 'Pending', MarketingDeployments_dateSched = '0', MarketingDeployments_sentBy = '0' WHERE MarketingDeployments_id = '".$this->db->mysqli->escape_string($deploy_id)."'";
        $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        
        //remove any emails added to the MarketingDeploymentVerify table for this deployment
        $sql = "DELETE FROM MarketingDeploymentVerify WHERE Deployment_id = '".$this->db->mysqli->escape_string($deploy_id)."'";
        $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);

        //remove any PersonsCommHistory records for this deployment
        $sql = "DELETE FROM PersonsCommHistory WHERE Deployment_id = '".$this->db->mysqli->escape_string($deploy_id)."'";
        $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
    }
    
    /**
     * Delete deployment
     * 
     * 
     * @param   int     $deploy_id
     * @return  void
     */
    public function delete_deployment($deploy_id)
    {
        $data = $this->get_deployment($deploy_id);
		if($data['MarketingDeployments_status'] != 'Pending') {
			return 0;
		}
        
        //remove deployment from database
        $delete = $this->db->mysqli->query("DELETE FROM MarketingDeployments WHERE MarketingDeployments_id = '".$this->db->mysqli->escape_string($_POST['deploy_id'])."'") or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        
        //remove list association records
        $delete = $this->db->mysqli->query("DELETE FROM MarketingDeploymentLists WHERE Deployment_id = '".$this->db->mysqli->escape_string($_POST['deploy_id'])."'") or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        
        //remove reporting data records
        $delete = $this->db->mysqli->query("DELETE FROM MarketingDeploymentViews WHERE Deployment_id = '".$this->db->mysqli->escape_string($_POST['deploy_id'])."'") or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
    
        return 1;
    }
    
    /**
     * Copy deployment
     * 
     * 
     * @param   int     $deploy_id
	 * @param   str     $deploy_title
     * @return  int     the ID of the new deployment record
     */
    public function copy_deployment($deploy_id, $deploy_title)
    {
        //get deployment data
        $data  = $this->get_deployment($deploy_id);
        $lists = $this->get_deployment_lists($deploy_id, true);
        
        //insert a new deployment record
        $sql = "
            INSERT INTO
                MarketingDeployments
            (
               	MarketingDeployments_name,
                MarketingDeployments_subject,
                MarketingDeployments_fromEmail,
                MarketingDeployments_fromName,
                MarketingDeployments_replyTo,
                MarketingDeployments_bodyHTML,
                MarketingDeployments_bodyText,
                MarketingDeployments_status,
                MarketingDeployments_dateCreated,
				MarketingDeployments_createdBy,
                MarketingDeployments_ESP
            )
            VALUES
            (
                '".$this->db->mysqli->escape_string($deploy_title)."',
                '".$this->db->mysqli->escape_string(utf8_encode($data['MarketingDeployments_subject']))."',
                '".$this->db->mysqli->escape_string($data['MarketingDeployments_fromEmail'])."',
                '".$this->db->mysqli->escape_string($data['MarketingDeployments_fromName'])."',
                '".$this->db->mysqli->escape_string($data['MarketingDeployments_replyTo'])."',
                '".$this->db->mysqli->escape_string(utf8_encode($data['MarketingDeployments_bodyHTML']))."',
                '".$this->db->mysqli->escape_string(utf8_encode($data['MarketingDeployments_bodyText']))."',
                'Pending',
                '".time()."',
                '".$_SESSION['system_user_id']."',
                '".$this->db->mysqli->escape_string($this->esp)."'
            )";
        
        $insert = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        $new_id = $this->db->mysqli->insert_id;
        
        //add new list associations
        foreach($lists as $list)
        {
            $query = $this->db->mysqli->query("INSERT INTO MarketingDeploymentLists (Deployment_id, MarketingList_id) VALUES ('".$new_id."', '".$list."')") or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        }
        
        return $new_id;
    }
    
    /**
     * Send test e-mail
     * 
     * 
     * @param   int     $deploy_id
     * @param   string  $to_email
     * @return  bool 
     */
    public function send_test_email($deploy_id, $to_email)
    {
        //make sure email is valid
        if(!$this->valid_email($to_email))
        {
            return false;
        }
    
        //get deployment data
        $data = $this->get_deployment($deploy_id);
        
        $html_body = $data['MarketingDeployments_bodyHTML'];
        $text_body = $data['MarketingDeployments_bodyText'];
        $external_esp = true;
        $pid = 0;
		        
        //prepend message headers
        $html_body = $this->prepend_msg_header($html_body, $deploy_id, $pid);
        $text_body = $this->prepend_msg_header($text_body, $deploy_id, $pid, true);
        
        //append message footers
        $html_body = $this->append_msg_footer($html_body, $deploy_id, $pid, false, $external_esp);
        $text_body = $this->append_msg_footer($text_body, $deploy_id, $pid, true, $external_esp);
        
        //replace merge fields
        $html_body = $this->replace_mergefields($deploy_id, $html_body, $pid, false, true);
        $html_body = mb_convert_encoding($html_body, "HTML-ENTITIES", 'UTF-8');
					
        $text_body = $this->replace_mergefields($deploy_id, $text_body, $pid, false, true);

        //send the message
        $result = $this->send_oneoff_email($data['MarketingDeployments_subject'], array($to_email), array(), array(), $data['MarketingDeployments_fromName'], $data['MarketingDeployments_fromEmail'], $data['MarketingDeployments_replyTo'], $html_body, $text_body);
        
        return $result;
    }
	
	/**
     * Merge DB fields with body of the email
     * 
     * 
     * @param   string  $body
     * @param   string  $table
	 * @param	int		$person
     * @return  string 
     */
	public function db_field_merge($body, $table, $person) {
		// REPLACE DB FIELDS //
		$person_sql = "SELECT * FROM Persons WHERE PersonID='".$person."'";
		$person_data = $this->db->get_single_result($person_sql);
		
		$psql = "SELECT * FROM Questions WHERE MappedTable='".$table."' AND Status='1' ORDER BY QuestionOrder ASC";
		//echo $psql."\n";
		$pfields = $this->db->get_multi_result($psql);
		if($pfields['empty_result'] != 1) {
			foreach($pfields as $pfield):
				$fieldName = $pfield['MappedField'];
				$mergeString = '%%'.$fieldName.'%%';
				//echo $fieldName.'+'.$mergeString."\n";
				$body_temp = str_replace($mergeString, $person_data[$fieldName], $body);
				$body = $body_temp;			
			endforeach;				
		}	
		return $body;		
	}
    
    /**
     * Get mobile content
     * 
     * Returns the mobile friendly version of the deployment e-mail content,
     * with merge fields replaced, ect. along with the e-mail subject
     * 
     * @param   int     $deploy_id
     * @param   int     $person_id
     * @return  array   array('body'=>'', 'subject'=>'')
     */
    function get_mobile_content($deploy_id, $person_id)
    {
        $deploy_id = trim($deploy_id);
        $person_id = trim($person_id);
        $content = array();
        
        $data = $this->get_deployment($deploy_id);
        if(empty($data)) return false;
        
        $content['subject'] = $data['EmailSubject'];
        $content['body'] = $data['EmailBodyMobile'];
        
        $content['body'] = $this->append_msg_footer($content['body'], $deploy_id, $person_id, false, true);
        $content['body'] = $this->replace_mergefields($deploy_id, $content['body'], $person_id);
        
        return $content;
    }
    
    /**
     * Get web content
     * 
     * Returns the "view online" version of the deployment e-mail content,
     * with merge fields replaced, ect. along with the e-mail subject and additional css styles
     * 
     * @param   int     $deploy_id
     * @param   int     $person_id
     * @return  array   array('body'=>'', 'subject'=>'')
     */
    function get_web_content($deploy_id, $person_id, $ver='html')
    {
        $deploy_id = trim($deploy_id);
        $person_id = trim($person_id);
        $content = array();
        
        $data = $this->get_deployment($deploy_id);
        if(empty($data)) return false;
        
        $content['css'] = '';
        $content['subject'] = $data['MarketingDeployments_subject'];
		if($ver == 'html') {
			$content['body'] = $data['MarketingDeployments_bodyHTML'];
		} else {
			$content['body'] = $data['MarketingDeployments_bodyText'];
		}
        
		if(is_numeric($person_id)){
			$content['body'] = $this->append_msg_footer($content['body'], $deploy_id, $person_id, (($ver == 'html') ? false : true), true);
		}
		$content['body'] = $this->replace_mergefields($deploy_id, $content['body'], $person_id);
		
		if($ver == 'text') {
			$content['body'] = nl2br($content['body']);
		}
        
        return $content;
    }
    
    /**
     * Create emails file
     * 
     * 
     * @param   int     $deploy_id
     * @return  array|bool
     */
    private function create_emails_file($deploy_id)
    {
        $filename = 'deployment_list_'.$deploy_id.'.csv';
        $write_error = false;
        $count = 0;
        
        //attempt to create the file
        $fp = fopen($this->list_file_path.'/'.$filename, 'c');
        
        if($fp === false)
        {
            echo 'Error creating deployment e-mails file', 'URL:'.$this->settings['BASE_URL'].'<br>DEPLOYMENT ID: '.$deploy_id, 'dev@kelleher-international.com';
            $this->send_error_msg('Error creating deployment e-mails file', 'URL:'.$this->settings['BASE_URL'].'<br>DEPLOYMENT ID: '.$deploy_id, 'rich@kelleher-international.com');
            return false;
        }
    
        //get all associated CRM groups
        $groups = array();
        $lists  = $this->get_deployment_lists($deploy_id);
        
        foreach($lists as $list)
        {
            $g = $this->get_list_groups($list);
            $groups = array_merge($groups, $g); 
        }
        
        //remove any duplicate groups
        $groups = array_unique($groups);        
        //get deployment data
        $data = $this->get_deployment($deploy_id);
  
        //iterate over each group
        foreach($groups as $g)
        {
            //get the SQL used to retrieve the e-mails for the group
            $sql   = $this->build_group_emails_sql($g);
            $query = $this->db->mysqli->query($sql);
            
            if($query === false)
            {
                $this->send_error_msg('Marketing SQL Error - Marketing::create_emails_file()','SQL:' . $sql . 'URL:'.$this->settings['BASE_URL'].'<br>DEPLOYMENT ID: '.$deploy_id.'<br><pre>'.'Line: '.__LINE__ .' - '.$this->db->mysqli->error.'</pre>');
                return false;
            }
            
            while($row = $query->fetch_assoc())
            {
                //check if the e-mail has been globally unsubscribed
                if($this->is_unsubscribed($row['Email'])) continue;
				
                //check if the e-mail is marked as "bounced" in the system
                if($this->is_bounced($row['Email'])) continue;
				
                //attempt to add the email to the deployment "verify" table
                //failure means this e-mail already has been added for the deployment
                if(!$this->add_deployment_email($deploy_id, $row['Email'])) continue;
                
                //get all the related data for this field for merge field replacement
                $merge_data = $this->get_merge_data($deploy_id, $row['Person_id'], $event_id, $event_coupon_id, true);
                array_unshift($merge_data, $row['Email']);
                //add a new line to the file

                $result = fputcsv($fp, $merge_data, ',');
                $count++;
            }
        }
        
        $result = @fclose($fp);
              
        if($write_error)
        {
           
            $this->send_error_msg('Errors occurred writing to e-mails import file', 'URL:'.$this->settings['BASE_URL'].'<br>DEPLOYMENT ID: '.$deploy_id, 'dev@kelleher-international.com');
            return false;
        }
    
     
        return ($count > 0) ? array('filename' => $filename, 'count' => $count) : false;
    }
    
    /**
     * Send approval messages
     * 
     * 
     * @param   int     $deploy_id
     * @return  bool
     */
    public function send_approval_msgs($deploy_id)
    {
        //make sure the approval list CRM group is defined
        if(!defined('SEED_LIST_ID') || SEED_LIST_ID < 0)
        {
            return false;
        }
        
        //get all email addresses for this group
        $sql = $this->build_group_emails_sql(SEED_LIST_ID);
        
        //get the deployment data
        $data = $this->get_deployment($deploy_id);
         
        //send deployment e-mail as a one-off to each member of the approval group
        $list_query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        
        while($row = $list_query->fetch_assoc())
        {
             //construct the email body
            $html_body = $data['EmailBody'];
            $text_body = $data['EmailBodyText'];
            $external_esp = true;	//($data['DeploymentEsp'] !== 'local');
            
            //prepend message headers
            $html_body = $this->prepend_msg_header($html_body, $deploy_id, $row['PersonID']);
            $text_body = $this->prepend_msg_header($text_body, $deploy_id, $row['PersonID'], true);
    
            //append message footers
            $html_body = $this->append_msg_footer($html_body, $deploy_id, $row['PersonID'], false, $external_esp);
            $text_body = $this->append_msg_footer($text_body, $deploy_id, $row['PersonID'], true, $external_esp);
            
            //replace merge fields
            $html_body = $this->replace_mergefields($deploy_id, $html_body, $row['PersonID']);
            $text_body = $this->replace_mergefields($deploy_id, $text_body, $row['PersonID']);
        
            $approve_link_html = '<div style="margin-top:20px;"><a href ="'.$this->approve_url.'?id='.$deploy_id.'&pid='.$row['PersonID'].'">Click here</a> to approve/disapprove this deployment</div>';
            
            $approve_link_text = '

Go to the following link to approve/disapprove this deployment:
'.$this->approve_url.'?id='.$deploy_id.'&pid='.$row['PersonID'];

            $html_body .= $approve_link_html;
            $html_body = mb_convert_encoding($html_body, "HTML-ENTITIES", 'UTF-8');
            $text_body .= $approve_link_text;
            
            //send the one-off message
            $result = $this->send_oneoff_email($data['EmailSubject'], array($row['Email']), array(), array(), $data['EmailFromName'], $data['EmailFrom'], $data['EmailReplyTo'], $html_body, $text_body);

        }
        
        return true;
    }
    
    /**
     * Insert approval response
     * 
     * 
     * @param   int     $deploy_id
     * @param   int     $person_id
     * @param   string  $response
     * @param   string  $comment
     * @return  bool
     */
    public function insert_approval_response($deploy_id, $person_id, $response, $comment)
    {
        $sql = "
            INSERT INTO
                MarketingDeploymentSeeds
            (
                DeploymentID,
                PersonID,
                Approval_Date,
                Approval_Response,
                Approval_Comment
            )
            VALUES
            (
                '".$this->db->mysqli->escape_string($deploy_id)."',
                '".$this->db->mysqli->escape_string($person_id)."',
                '".time()."',
                '".$this->db->mysqli->escape_string($response)."',
                '".$this->db->mysqli->escape_string($comment)."'
            )";
            
        $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        
        return $query;
    }
        
    /**
     * Replace merge fields
     * 
     * 
     * @param   int     $deploy_id
     * @param   string  $body
     * @param   int     $person_id
     * @param   bool    $one_off
     * @param   bool    $test
     * @param   int     $event_id
     * @return  string
     */
    public function replace_mergefields($deploy_id, $body, $person_id, $one_off=false, $test=false, $event_id=0)
    {
        //get the deployment data
        $data = $this->get_deployment($deploy_id);    
        //replace the standard merge fields
        $body = $this->merge_data($body, $person_id, $data['SiteID'], $deploy_id, $test);
        //replace any events merge fields
        $body = $this->replace_event_mergefields($body, $person_id, $p_data['Email']);
        //merge event session data if applicable
        if($one_off && $event_id > 0 && $person_id > 0)
        {
            $body = $this->merge_event_session_body($body, $person_id, $event_id);
        }
        
        //convert relative paths for links and images to absolute URLs
        $body = $this->email_urls($body);
        return $body;
    }

    /**
     * Merge data
     *
     * 
     * @param   array   $content
     * @param   int     $person_id
     * @param   int     $site_id
     * @return  void
     */
    public function merge_data($content, $person_id, $site_id=1, $deploy_id=0, $test=false, $skip_person=false)
    {
		include_once("class.encryption.php");
		include_once("class.record.php");
		$no_person = false;
        $viewonline_url = ($deploy_id > 0) ? $this->settings['BASE_URL'].'view-email.php?id='.$deploy_id.'&pid='.$person_id : '';
        $mobile_url = ($deploy_id > 0) ? $this->settings['BASE_URL'].'view-email.php?id='.$deploy_id.'&pid='.$person_id : '';
        
		if(!$test && !$skip_person && !empty($person_id) && is_numeric($person_id)) {
			//get the person record
			$p_query = "SELECT * FROM Persons WHERE Person_id = '".$this->db->mysqli->escape_string($person_id)."' LIMIT 1";
			$p_send = $this->db->mysqli->query($p_query) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error.' '.$p_query);
			if($p_send->num_rows == 1) {
				$p_data = $p_send->fetch_assoc();
			} else {
				$no_person = true;
			}
		} else {
			$no_person = true;
		}        
        $merge_fields = $this->get_merge_fields();

        foreach($merge_fields as $field => $field_data)
        {
			$db_field = $field_data['db'];
            $m_data = '';
            
            if($field == '#OptOutLink')
            {
                $m_data = $this->optout_url;
            }
            elseif($field == '#ViewOnlineLink')
            {
                $m_data = $viewonline_url;
            }
            elseif($field == '#MobileLink')
            {
                $m_data = $mobile_url;
            }
            elseif($field == '#OptInReason')
            {
                $m_data = $this->optin_reason;
            }
            elseif($field == '#MailingAddress')
            {
                $addr_company = $this->settings['COMPANY_NAME'];
                $addr_street1 = $this->settings['MAILING_ADDRESS_LINE1'];
                $addr_street2 = $this->settings['MAILING_ADDRESS_LINE2'];
                $addr_city = $this->settings['MAILING_ADDRESS_CITY'];
                $addr_state = $this->settings['MAILING_ADDRESS_STATE'];
                $addr_postal = $this->settings['MAILING_ADDRESS_POSTAL'];

                $m_data  = $addr_company.', '.$addr_street1.', '.((strlen($addr_street2) > 0) ? $addr_street2.', ' : '');
                $m_data .= $addr_city.', '.$addr_state.' '.$addr_postal;
            }
            elseif($field == '#ExpirationDate_') {
                $expDays_offset = strpos($content, $field);
                $expDays = substr($content, ($expDays_offset + 16), 2);
                $fullReplaceString = '#ExpirationDate_'.$expDays;
                $field = $fullReplaceString;                
                $m_data = date("m/d/y", mktime(0, 0, 0, date("m"), date("d") + $expDays, date("Y")));
            }
			elseif($field == '#Password') {
				$ENC = new encryption();
				$m_data = $ENC->decrypt($p_data[$db_field]);
			}
			elseif($field == '#SenderName') {
				$RECORD = new Record($this->db);				
				$m_data = $RECORD->get_FulluserName($_SESSION['system_user_id']);
			}
			elseif($field == '#SenderEmail') {
				$RECORD = new Record($this->db);				
				$m_data = $RECORD->get_userEmail($_SESSION['system_user_id']);
			}
			elseif($field == '#SenderRoomURL') {
				$RECORD = new Record($this->db);				
				$m_data = $RECORD->get_userRoomURL($_SESSION['system_user_id']);
			}
			elseif($skip_person)
			{
				continue;
			}
            elseif($no_person)
            {
                $content = str_replace($field, $this->test_data[$field], $content);
                continue;
            }
            else
            {
               $m_data = $p_data[$db_field];     
            }
            $content = str_replace($field, $m_data, $content);
        }

        return $content;
    }

    /**
     * Prepare email body
     *
     *
     * @param   string  $body
     * @param   int     $deploy_id
     * @param   string  $type html|text
     * @param   string  $left_enclosure
     * @param   string  $right_enclosure
     * @param   bool    $external_esp
     * @return  string
     */
    public function prepare_email_body($body, $deploy_id=0, $type='html', $left_enclosure='%%', $right_enclosure='%%', $external_esp=true)
    {
        //handle ExpirationDate of coupon if present
        $expStringStart = strpos($body, '#ExpirationDate_');
        if ($expStringStart) {
            $fullStringToReplace = substr($body, $expStringStart, 18);
            $expDays = substr($fullStringToReplace, -2);
            $expDate = date("m/d/y", mktime(0, 0, 0, date("m"), date("d") + $expDays, date("Y")));
            $body = str_replace($fullStringToReplace, $expDate, $body);
        }

        //check if there are any event merge fields in the content        
        if(preg_match('/##cCode_([0-9]+)##/', $body)) {          
            $body = preg_replace('/##cCode_([0-9]+)##/', $left_enclosure.'cCode'.$right_enclosure, $body);
        }

        if(preg_match('/##ConfirmationNumber_([0-9]+)##/', $body)) {         
            $body = preg_replace('/##ConfirmationNumber_([0-9]+)##/', $left_enclosure.'confirmationNumber'.$right_enclosure, $body);
        }
        
        $body = str_replace('##pCode##', $left_enclosure.'pCode'.$right_enclosure, $body);  
        $body = $this->append_msg_footer($body, $deploy_id, $left_enclosure.'PersonID'.$right_enclosure, ($type == 'text'), $external_esp);
        $body = $this->prepend_msg_header($body, $deploy_id, $left_enclosure.'PersonID'.$right_enclosure, ($type == 'text'));
        
        foreach($this->merge_fields as $field => $db_field)
        {
            $body = str_replace($field, $left_enclosure.str_replace('#', '', $field).$right_enclosure, $body);
        }

        //ensure all image and hyperlinks are absolute paths
        if($type == 'html')
        {
            $body = $this->email_urls($body);
        }

        return $body;
    }

    /**
     * Prepend message header
     * 
     * @param   string  $body
     * @param   int     $deploy_id
     * @param   int     $person_id
     * @param   bool    $plain_text
     * @return  string
     */
    public function prepend_msg_header($body, $deploy_id, $person_id, $plain_text=false)
    {
        $html_header = '';
        $text_header = '';
        //get the deployment data
        $data = $this->get_deployment($deploy_id);
        $viewonline_url = $this->settings['BASE_URL'].'view-email.php?id='.$deploy_id.'&pid='.$person_id;
        $wrap_header = (stripos($body, '#ViewOnlineLink') === false);
    
        if(file_exists($this->settings['SERVER_PATH'].'editorstyle.css') && $this->get_stylesheet_override_flag($deploy_id) != 1)
        {
            $html_header .= '<style type="text/css">'.file_get_contents($this->settings['SERVER_PATH'].'editorstyle.css').'</style>';
        }
        
        if($wrap_header)
        {
            $html_header .= '<div style="text-align:center; margin-bottom:10px; font-family: Arial, sans-serif; font-size: 10px;">';
        }
        
        if(stripos($body, '#ViewOnlineLink') === false)
        {
            $text_header .= 'View this e-mail online: ';
            $text_header .= $viewonline_url.'
    
';
            $html_header .= '<a href="'.$viewonline_url.'">View this e-mail online</a>&nbsp;&nbsp;';
        }
        
        if($wrap_header)
        {
            $html_header .= '</div>';
        }
        
        return ($plain_text) ? $text_header.$body : $html_header.$body;
    }
    
    /**
     * Append message footer
     * 
     * 
     * @param   string  $body
     * @param   int     $deploy_id
     * @param   int     $person_id
     * @param   bool    $plain_text
     * @param   bool    $external_esp
     * @return  string
     */
    public function append_msg_footer($body, $deploy_id, $person_id, $plain_text=false, $external_esp=true)
    {
        $data = $this->get_deployment($deploy_id);
        $baseurl = $this->settings['BASE_URL'];
        $addr_company = $this->settings['COMPANY_NAME'];
        $addr_street1 = $this->settings['MAILING_ADDRESS_LINE1'];
        $addr_street2 = $this->settings['MAILING_ADDRESS_LINE2'];
        $addr_city = $this->settings['MAILING_ADDRESS_CITY'];
        $addr_state = $this->settings['MAILING_ADDRESS_STATE'];
        $addr_postal = $this->settings['MAILING_ADDRESS_POSTAL'];
        
        //update the opt-out url to use the deployment's website domain
        $this->optout_url = $baseurl."unsubscribe.php";
    
        $optin_reason      = $this->optin_reason;
        $optout_text_plain = 'If you wish to no longer receive e-mail from this source, click or copy and paste the following URL in your browser:
';
        $optout_text_html  = 'If you wish to no longer receive e-mail from this source, ';
        
    
        $html_footer = '<div style="text-align:left; margin-top:10px; font-family: Arial, sans-serif; font-size: 10px;">';
        $text_footer = '

';
        
        //only append opt-out link if it has not been added in the body via the merge field
        if(stripos($body, '#OptOutLink') === false)
        {
            $html_footer .= $optout_text_html.' <a href="'.$this->optout_url.'">click here</a>.<br>';
            $text_footer .= $optout_text_plain.$this->optout_url.'
';
        }
        
        if($external_esp)
        {
            /*if(stripos($body, '#OptInReason') === false)
            {
                //add mandatory footer text and postal address for jango deployments
                $html_footer .= '<br>'.$optin_reason.'<br><br>';
                
                $text_footer .= '
'.$optin_reason.'

';
            }*/
            
            if(stripos($body, '#MailingAddress') === false)
            {
                $html_footer .= $addr_company.', '.$addr_street1.', '.((strlen(trim($addr_street2)) > 0) ? $addr_street2.', ' : '');
                $html_footer .= $addr_city.', '.$addr_state.' '.$addr_postal;
                
    
                $text_footer .= $addr_company.', '.$addr_street1.', '.((strlen(trim($addr_street2)) > 0) ? $addr_street2.', ' : '');
                $text_footer .= $addr_city.', '.$addr_state.' '.$addr_postal;
            }    
        }
        
        $html_footer .= '</div>';
        
        return ($plain_text) ? $body.$text_footer : $body.$html_footer;
    }
    
    /**
     * Log action
     * 
     * Logs all opt-in/opt-out actions to a database table
     * 
     * @param   string  $type
     * @param   string  $email
     * @return  bool
     */
    public function log_action($type, $email)
    {
        $sql = "
            INSERT INTO 
                MarketingLog 
            (
                MarketingLog_action, 
                MarketingLog_email, 
                MarketingLog_date
            )
            VALUES
            (
                '".$this->db->mysqli->escape_string($type)."',
                '".$this->db->mysqli->escape_string($email)."',
                '".time()."'
            )";
            
        $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        
        return $query;
    }
	
	 /**
     * Email search
     * 
     * Checks if a person record exists in the system with the given email address.
     * 
     * @param   string  $email
     * @return  int 	person id
     */
	public function email_search($email) {
		$result = $this->db->get_single_result("SELECT Person_id FROM Persons WHERE Email = '".$this->db->mysqli->escape_string($email)."' AND PersonsTypes_id NOT IN (1,2,9) LIMIT 1");
		if(array_key_exists('Person_id', $result)) {
			return $result['Person_id'];
		} else {
			return 0;
		}
	}
	
	/**
	 * Clean HTML
	 *
	 * Cleans a string of HTML, removing all potentially harmful tags so that is safe to display within the system
	 *
	 * @param	string	$html
	 * @return	string
	 */
	public function clean_html($html) {
		$html = preg_replace('@(<\!--\[if.+?\[endif\]-->)@is', '', $html);
		$html = preg_replace('@</?html(.*?)>@is', '', $html);
		$html = preg_replace('@</?head(.*?)>@is', '', $html);
		$html = preg_replace('@</?body(.*?)>@is', '', $html);
		$html = preg_replace('@</?meta(.*?)>@is', '', $html);
		$html = preg_replace('@</?base(.*?)>@is', '', $html);
		$html = preg_replace('@<head(.*?)>(.*?)</head>@is', '', $html);
		$html = preg_replace('@<title(.*?)>(.*?)</title>@is', '', $html);
		$html = preg_replace('@<script(.*?)>(.*?)</script>@is', '', $html);
		$html = preg_replace('@<style(.*?)>(.*?)</style>@is', '', $html);
		$html = preg_replace('@<link(.*?)>@is', '', $html);
		$html = str_replace('href="www', 'href="http://www', $html);

		return $html;
	}
    
    /**
     * Log communication
     * 
     * Adds a sent deployment or one-off e-mail to the person's communications history
     * 
     * @param   int     $person_id
     * @param   string  $message
     * @param   string  $subject
     * @param   string  $from_email
     * @param   int     $deploy_id
     * @param   string  $merge_data
     * @return  bool
     */
    public function log_communication($person_id, $message, $subject, $from_email, $to_email, $deploy_id=0, $send_time='', $message_type='EMAIL', $message_status='SENT', $message_service='LOCAL', $message_id='', $merge_data=false)
    {
		$current_time = time();
		$dup = 0;
		
		if($message_id != '') {
			$chk_sql = "SELECT Messages_id,PersonsCommHistory_id FROM Messages 
						JOIN PersonsCommHistory ON Messages_commId = PersonsCommHistory_id AND Person_id = '".$this->db->mysqli->escape_string($person_id)."'
						WHERE Messages_messageId = '".$this->db->mysqli->escape_string($message_id)."'
						AND Messages_service = '".$this->db->mysqli->escape_string($message_service)."'
						LIMIT 1";
			$chk_query = $this->db->get_single_result($chk_sql);
			if(array_key_exists('Messages_id', $chk_query)) {
				$dup = $chk_query['PersonsCommHistory_id'];
			}
		}
		if($dup == 0) {
			$sql = "
				INSERT INTO PersonsCommHistory (
					Person_id,
					MessageType, 
					MessageSentDate,
					MessageSender, 
					MessageSubject, 
					MessageBody,
					MessageStatus,
					MessageID,
					MessageSource,
					MessageService, 
					MessageFrom,
					MessageTo,
					MessageInsertDate,
					Deployment_id "
				. " ) VALUES ( "
				. " '".$this->db->mysqli->escape_string($person_id)."' "
				. " ,'".$this->db->mysqli->escape_string($message_type)."' "
				. " ,'".( $send_time != '' ? $send_time : $current_time )."'"
				. " ,'".( isset($_SESSION['system_user_id'])  ? $this->db->mysqli->escape_string($_SESSION['system_user_id']) : '1')."' "
				. " ,'".$this->db->mysqli->escape_string($subject)."' "
				. " ,'".$this->db->mysqli->escape_string($message)."' "
				. " ,'".$this->db->mysqli->escape_string($message_status)."' "
				. " ,'".$this->db->mysqli->escape_string($message_id)."' "
				. " ,'SYSTEM' "
				. " ,'".$this->db->mysqli->escape_string($message_service)."' "
				. " ,'".$this->db->mysqli->escape_string($from_email)."' "
				. " ,'".$this->db->mysqli->escape_string($to_email)."' "
				. " ,'".$current_time."' "
				. " ,'".$this->db->mysqli->escape_string($deploy_id)."' "
				.")";
			
			$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
			$comm_id = $this->db->mysqli->insert_id;
			
			if($message_id != '') {
				$sql_2 = "INSERT INTO Messages (
							Messages_commId,
							Messages_messageId,
							Messages_service
						) VALUES (
							'".$this->db->mysqli->escape_string($comm_id)."',
							'".$this->db->mysqli->escape_string($message_id)."',
							'".$this->db->mysqli->escape_string($message_service)."'
						)";
				$query_2 = $this->db->mysqli->query($sql_2) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
			}
			
			if($deploy_id != 0 && $merge_data !== false) {
				$sql_3 = "INSERT INTO MarketingDeploymentMerge (
							MarketingDeploymentMerge_commId,
							MarketingDeploymentMerge_data
						) VALUES (
							'".$this->db->mysqli->escape_string($comm_id)."',
							'".$this->db->mysqli->escape_string($merge_data)."'
						)";
				$query_3 = $this->db->mysqli->query($sql_3) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
			}
			
			$last_note = json_encode(array(
							'hType' => $message_type,
							'hHeader' => ( $message_type == 'EMAIL' ? $subject : $from_email ),
							'hFrom' => ( $message_type == 'EMAIL' ? $from_email : '' ),
							'hDate' => ( $send_time != '' ? $send_time : $current_time ),
							'hID' => $comm_id, 
							'node' => ( $message_type == 'EMAIL' ? 'EMAIL_LINK' : 'SMS_LINK' )
						));
			$sql_3 = "UPDATE Persons 
					SET LastNoteAction = '".$this->db->mysqli->escape_string($last_note)."',
					LastNoteActionBody = '".$this->db->mysqli->escape_string($message)."'
					WHERE Person_id = '".$this->db->mysqli->escape_string($person_id)."'";
			$query_3 = $this->db->mysqli->query($sql_3);
			
		} else {
            $sql = "
            UPDATE PersonsCommHistory set "
            . " MessageSubject = '".$this->db->mysqli->escape_string($subject)."' "
            . " , MessageBody='".$this->db->mysqli->escape_string($message)."' "
            . " WHERE PersonsCommHistory_id = '".$dup."' ";
            $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
            $comm_id = $this->db->mysqli->insert_id;
		}
        
        return $dup;
    }

    /**
     * Record deployment communications
     *
     *
     * @param   array   $data
     * @param   string  $filename
     * @return  bool
     */
    public function record_deployment_comms($data, $filename)
    {
        //open the created e-mail list file
        $fp = fopen($this->list_file_path.'/'.$filename, 'r');
        if($fp === false) return false;
        
        $this->db_reconnect();
        //loop through each line
        while(($line = fgetcsv($fp)) !== false)
        {
            $mdata = $this->merge_fields;
            $count = 0;
            //get the merge data for the current e-mail address
            foreach($mdata as $field => $db)
            {
                $mdata[$field] = $line[$count + 1];
                $count++;
            }
             
            //get the person ID from the line
            $person_id = end($line);
            //record the message to the person comm history
            $this->log_communication($person_id, '', $data['MarketingDeployments_subject'], $data['MarketingDeployments_fromEmail'], $mdata['#Email'], $data['MarketingDeployments_id'], '', 'EMAIL', 'SENT', strtoupper($this->esp), '', serialize($mdata));
        }

        return true;
    }
	
	/**
     * Update email status
     *
     * Updates the status of the email address to an integer representing one of the following: ok, bounced or unsubscribed 
     * 
     * @param   string  $email
	 * @param   int     $status
     * @return  void
     */
	public function update_email_status($email, $status) {
		$sql = "UPDATE Persons SET EmailStatus = '".$status."' WHERE Email = '".$this->db->mysqli->escape_string($email)."'";
		$query = $this->db->mysqli->query($sql);
	}
    
    /**
     * Process unsubscribe
     *
     * Unsubscribes an e-mail address from ALL future deployments 
     * 
     * @param   string  $email
     * @return  void
     */
    public function process_unsubscribe($email)
    {
        //add email address to local opt-outs table
        $sql   = "REPLACE INTO MarketingOptOuts (Optouts_email, Optouts_date) VALUES ('".$this->db->mysqli->escape_string($email)."', '".time()."')";
        $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
		
        //log the opt-out action
        $this->log_action('opt-out', $email);
		$this->update_email_status($email, 3);
        
        return;
    }
	
	 /**
     * Remove unsubscribe
     *
     * Removes an e-mail address from the Marketing OptOut list
     * 
     * @param   string  $email
     * @return  void
     */
    public function remove_unsubscribe($email)
    {
        //add email address to local opt-outs table
        $sql   = "DELETE FROM MarketingOptOuts WHERE Optouts_email = '".$this->db->mysqli->escape_string($email)."'";
        $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        
        //log the opt-out action
        $this->log_action('removed from opt-out', $email);
		$this->update_email_status($email, 1);
        
        return;
    }
    
    /**
     * Is unsubscribed
     *
     * Checks if the specified e-mail is globally opted-out
     *
     * @param   string  $email
     * @return  bool
     */
    public function is_unsubscribed($email)
    {
        $sql   = "SELECT Optouts_email FROM MarketingOptOuts WHERE Optouts_email = '".$this->db->mysqli->escape_string($email)."' LIMIT 1";
        $query = $this->db->mysqli->query($sql);
        
        if($query === false)
        {
            $this->send_error_msg('Marketing SQL Error - Marketing::is_unsubscribed()','URL:'.$this->settings['BASE_URL'].'<br>DEPLOYMENT ID: '.$deploy_id.'<br><pre>'.'Line: '.__LINE__ .' - '.$this->db->mysqli->error.'</pre><br>'.$sql);
            exit;
        }

        return ($query->num_rows > 0) ? true : false;
    }
    
    /**
     * Is bounced
     * 
     * Checks if the specified e-mail is classified as "invalid" (has a total number of non-overidden bounces that equal or exceed the bounce threshold [defaults to 1])
     * 
     * @param   string  $email
     * @return  bool
     */
    public function is_bounced($email)
    {
        $threshold = 1;
        $sql   = "SELECT Deployment_id FROM MarketingDeploymentVerify WHERE EmailAddress='".$this->db->mysqli->escape_string($email)."' AND FailureOverride != '1' AND Failure='1' LIMIT ".$threshold;
        $query = $this->db->mysqli->query($sql);
        
        if($query === false)
        {
            $this->send_error_msg('Marketing SQL Error - Marketing::is_bounced()','URL:'.$this->settings['BASE_URL'].'<br>DEPLOYMENT ID: '.$deploy_id.'<br><pre>'.'Line: '.__LINE__ .' - '.$this->db->mysqli->error.'</pre><br>'.$sql);
            exit;
        }
        
        return ($query->num_rows == $threshold) ? true : false;
    }
    
    /**
     * Is suppressed
     *
     * Checks if the specified e-mail is suppressed for a deployment
     *
     * @param   int     $deploy_id
     * @param   string  $email
     * @return  bool
     */
    public function is_suppressed($deploy_id, $email)
    {
        $sql   = "SELECT DeploymentID FROM MarketingDeploymentSupress WHERE DeploymentID = '".$this->db->mysqli->escape_string($deploy_id)."' AND Email = '".$this->db->mysqli->escape_string($email)."' LIMIT 1";
        $query = $this->db->mysqli->query($sql);
        
        if($query === false)
        {
            $this->send_error_msg('Marketing SQL Error - Marketing::is_suppressed()','URL:'.$this->settings['BASE_URL'].'<br>DEPLOYMENT ID: '.$deploy_id.'<br><pre>'.'Line: '.__LINE__ .' - '.$this->db->mysqli->error.'</pre><br>'.$sql);
            exit;
        }

        return ($query->num_rows > 0) ? true : false;
    }
    
    /**
     * Add deployment email
     * 
     * Attempts to add an e-mail address to MarketingDeploymentVerify for the specified deployment
     * Returns false if the e-mail already exists for the deployment causing the insert to fail
     * 
     * @param   int     $deploy_id
     * @param   string  $email 
     * @return 
     */
    public function add_deployment_email($deploy_id, $email)
    {
		$chk_sql   = "SELECT COUNT(*) AS Sent FROM MarketingDeploymentVerify WHERE Deployment_id = '".$this->db->mysqli->escape_string($deploy_id)."' AND EmailAddress = '".$this->db->mysqli->escape_string($email)."'";
		$chk_query = $this->db->get_single_result($chk_sql);
		if(array_key_exists('Sent', $chk_query)) {
			if($chk_query['Sent'] > 0) {
				return false;
			}
		} else {
			$this->send_error_msg('Marketing SQL Error - Marketing::add_deployment_email()','URL:'.$this->settings['BASE_URL'].'<br>DEPLOYMENT ID: '.$deploy_id.'<br><pre>'.'Line: '.__LINE__ .' - '.$this->db->mysqli->error.'</pre><br>'.$sql);
            exit;
		}
		
        $sql    = "INSERT INTO MarketingDeploymentVerify (Deployment_id, EmailAddress, MailDate) VALUES ('".$this->db->mysqli->escape_string($deploy_id)."', '".$this->db->mysqli->escape_string($email)."', '".time()."')";
        $result = $this->db->mysqli->query($sql);
        
        return $result;
    }
	
	public function get_automated_email_stats($automated_email_id, $start_epoch, $end_epoch)
	{
		$sql = "SELECT COUNT(*) AS Stat FROM MarketingAutomatedDeploymentsVerify 
				WHERE AutoDeployID = '".$automated_email_id."' AND DateSent >= '".$start_epoch."' AND DateSent < '".$end_epoch."'";
		$result = $this->db->mysqli->query($sql);
		$row = $result->fetch_assoc();
		return $row['Stat'];
	}
    
    
    /**
     * Get deployment statistics
     * 
     * Returns an array of reporting statistics for the specified deployment
     * 
     * @param   int         $deploy_id
     * @return  array
     */
    public function get_deployment_stats($deploy_id)
    {
        $stats = array('total'=>0, 'opens'=>0, 'opens_unique'=>0, 'bounces'=>0, 'clicks'=>0);
        $data = $this->get_deployment($deploy_id);
        if(empty($data)) return $stats;
        
        switch($data['MarketingDeployments_ESP'])
        {
            case 'sendgrid':
				$sql   = "SELECT COUNT(*) AS bounces 
							FROM MarketingDeploymentEvents 
							WHERE MarketingDeploymentEvents_deploymentId = '".$this->db->mysqli->escape_string($deploy_id)."' 
							AND MarketingDeploymentEvents_eventType IN (2, 6)";
                $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
                $data  = $query->fetch_assoc();
                $stats['bounces'] = $data['bounces'];
                
                $sql   = "SELECT COUNT(*) AS clicks 
							FROM MarketingDeploymentEvents 
							WHERE MarketingDeploymentEvents_deploymentId = '".$this->db->mysqli->escape_string($deploy_id)."' 
							AND MarketingDeploymentEvents_eventType = 4";
                $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
                $data  = $query->fetch_assoc();
                $stats['clicks'] = $data['clicks'];
                break;
            default:
                $sql   = "SELECT COUNT(*) AS bounces FROM MarketingDeploymentVerify WHERE Deployment_id = '".$this->db->mysqli->escape_string($deploy_id)."' AND Failure = 1";
                $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
                $data  = $query->fetch_assoc();
                $stats['bounces'] = $data['bounces'];
                break;
        }
		
		$sql   = "SELECT COUNT(*) AS total FROM MarketingDeploymentVerify WHERE Deployment_id = '".$this->db->mysqli->escape_string($deploy_id)."'";
        $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        $data  = $query->fetch_assoc();
        $stats['total'] = $data['total'];
		
		$sql   = "SELECT COUNT(*) AS opens FROM MarketingDeploymentViews WHERE Deployment_id = '".$this->db->mysqli->escape_string($deploy_id)."'";
		$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
		$data  = $query->fetch_assoc();
		$stats['opens'] = $data['opens'];
		
		$sql   = "SELECT COUNT(DISTINCT Email) AS opens_unique FROM MarketingDeploymentViews WHERE Deployment_id = '".$this->db->mysqli->escape_string($deploy_id)."'";
		$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
		$data  = $query->fetch_assoc();
		$stats['opens_unique'] = $data['opens_unique'];
        
        return $stats;
    }
	
	public function get_autoemail_reporting_data($autoemail_id, $type, $start_epoch, $end_epoch)
	{
		 $results = array('total'=>0, 'rows'=>array(), 'has_date'=>false, 'has_url'=>false);
		 
		 switch($type) {
			case 'recipients':
				$results['has_date'] = true;
				$sql = "SELECT
							MarketingAutomatedDeploymentsVerify.PersonID,
							MarketingAutomatedDeploymentsVerify.EmailAddress,
							MarketingAutomatedDeploymentsVerify.DateSent
						FROM 
							MarketingAutomatedDeploymentsVerify 
						WHERE
							MarketingAutomatedDeploymentsVerify.AutoDeployID = '".$this->db->mysqli->escape_string($autoemail_id)."'
							AND MarketingAutomatedDeploymentsVerify.DateSent >= '".$start_epoch."'
							AND MarketingAutomatedDeploymentsVerify.DateSent < '".$end_epoch."'";
				$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error); 
				$results['total'] = $query->num_rows;
				
				while($row = $query->fetch_assoc()) {
					$results['rows'][] = array('person_id'=>$row['PersonID'], 'email'=>$row['EmailAddress'], 'date'=>$row['DateSent']);
				}
			break;   
            case 'opens':
				$results['has_date'] = true;
				$sql = "SELECT
							MarketingAutomatedDeploymentsVerify.PersonID,
							MarketingAutomatedDeploymentsVerify.EmailAddress,
							MarketingDeploymentEvents.MarketingDeploymentEvents_date
						FROM
							MarketingAutomatedDeploymentsVerify
						JOIN
							MarketingDeploymentEvents ON MarketingAutomatedDeploymentsVerify.MADV_ID = MarketingDeploymentEvents.MarketingDeploymentEvents_autoDeployId
						WHERE
							MarketingAutomatedDeploymentsVerify.AutoDeployID = '".$this->db->mysqli->escape_string($autoemail_id)."'
							AND MarketingDeploymentEvents.MarketingDeploymentEvents_eventType = 3
							AND MarketingDeploymentEvents.MarketingDeploymentEvents_date >= '".$start_epoch."'
							AND MarketingDeploymentEvents.MarketingDeploymentEvents_date < '".$end_epoch."'";
				$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error); 
				$results['total'] = $query->num_rows;
				while($row = $query->fetch_assoc()) {
					$results['rows'][] = array('person_id'=>$row['PersonID'], 'email'=>$row['EmailAddress'], 'date'=>$row['MarketingDeploymentEvents_date']);
				}
			break;
            case 'clicks':
				$results['has_url']  = true;
                $results['has_date'] = true;
				$sql = "SELECT
							MarketingAutomatedDeploymentsVerify.PersonID,
							MarketingAutomatedDeploymentsVerify.EmailAddress,
							MarketingDeploymentEvents.MarketingDeploymentEvents_date,
							MarketingDeploymentEvents.MarketingDeploymentEvents_clickedUrl
						FROM
							MarketingAutomatedDeploymentsVerify
						JOIN
							MarketingDeploymentEvents ON MarketingAutomatedDeploymentsVerify.MADV_ID = MarketingDeploymentEvents.MarketingDeploymentEvents_autoDeployId
						WHERE
							MarketingAutomatedDeploymentsVerify.AutoDeployID = '".$this->db->mysqli->escape_string($autoemail_id)."'
							AND MarketingDeploymentEvents.MarketingDeploymentEvents_eventType = 4
							AND MarketingDeploymentEvents.MarketingDeploymentEvents_date >= '".$start_epoch."'
							AND MarketingDeploymentEvents.MarketingDeploymentEvents_date < '".$end_epoch."'";
				$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error); 
				$results['total'] = $query->num_rows;
				while($row = $query->fetch_assoc()) {
					$results['rows'][] = array('person_id'=>$row['PersonID'], 'email'=>$row['EmailAddress'], 'date'=>$row['MarketingDeploymentEvents_date'], 'url'=>$row['MarketingDeploymentEvents_clickedUrl']);
				}
			break; 
            case 'bounces':
				$results['has_date'] = true;
				$sql = "SELECT
							MarketingAutomatedDeploymentsVerify.PersonID,
							MarketingAutomatedDeploymentsVerify.EmailAddress,
							MarketingDeploymentEvents.MarketingDeploymentEvents_date
						FROM
							MarketingAutomatedDeploymentsVerify
						JOIN
							MarketingDeploymentEvents ON MarketingAutomatedDeploymentsVerify.MADV_ID = MarketingDeploymentEvents.MarketingDeploymentEvents_autoDeployId
						WHERE
							MarketingAutomatedDeploymentsVerify.AutoDeployID = '".$this->db->mysqli->escape_string($autoemail_id)."'
							AND MarketingDeploymentEvents.MarketingDeploymentEvents_eventType IN (2, 6)
							AND MarketingDeploymentEvents.MarketingDeploymentEvents_date >= '".$start_epoch."'
							AND MarketingDeploymentEvents.MarketingDeploymentEvents_date < '".$end_epoch."'";
				$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error); 
				$results['total'] = $query->num_rows;
				while($row = $query->fetch_assoc()) {
					$results['rows'][] = array('person_id'=>$row['PersonID'], 'email'=>$row['EmailAddress'], 'date'=>$row['MarketingDeploymentEvents_date']);
				}
			break;
		 }
		 return $results;
	}
    
    /**
     * Get reporting data
     * 
     * 
     * @param   int     $deploy_id
     * @param   string  $type
     * @param   int     $offset
     * @param   int     $limit
	 * @param   int     $trigger
     * @return  array
     */
    public function get_reporting_data($deploy_id, $type, $offset=0, $limit=50, $trigger_id=0)
    {
        $results = array('total'=>0, 'rows'=>array(), 'has_date'=>false, 'has_url'=>false);
        $data = $this->get_deployment($deploy_id);

        if(empty($data)) return $results;
        
		switch(strtolower($type))
		{
			case 'recipients':
				$sql = "
					SELECT
						ifNull(Persons.Person_id, 0) AS PersonID,
						MarketingDeploymentVerify.EmailAddress,
						MarketingDeploymentVerify.MailDate
					FROM 
						MarketingDeploymentVerify 
					LEFT JOIN 
						Persons 
						ON MarketingDeploymentVerify.EmailAddress = Persons.Email
					WHERE
						MarketingDeploymentVerify.Deployment_id = '".$this->db->mysqli->escape_string($deploy_id)."'
					GROUP BY MarketingDeploymentVerify.EmailAddress";
				
				$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error); 
				$results['total'] = $query->num_rows;
				
				while($row = $query->fetch_assoc())
				{
					$results['rows'][] = array('person_id'=>$row['PersonID'], 'email'=>$row['EmailAddress'], 'date'=>$row['MailDate']);
				}
				
				//$results['rows'] = array_slice($results['rows'], $offset, $limit);
				$results['has_date'] = true;
				break;
			case 'opens':
				$sql = "
					SELECT
						MarketingDeploymentViews.ReadDate,
						Persons.Person_id AS PersonID,
						MarketingDeploymentViews.Email
					FROM
						MarketingDeploymentViews
					LEFT JOIN
						Persons
						ON MarketingDeploymentViews.Email = Persons.Email
					WHERE
						MarketingDeploymentViews.Deployment_id = '".$this->db->mysqli->escape_string($deploy_id)."'";
						
				$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error); 
				$results['total'] = $query->num_rows;
				
				while($row = $query->fetch_assoc())
				{
					$results['rows'][] = array('person_id'=>$row['PersonID'], 'email'=>$row['Email'], 'date'=>$row['ReadDate']);
				}
				
				//$results['rows'] = array_slice($results['rows'], $offset, $limit);
				$results['has_date'] = true;
				break;
			case 'opens_unique':
				 $sql = "
					SELECT                            
						Persons.Person_id AS PersonID,
						MDV.Email,
						( SELECT MDV2.ReadDate 
						 FROM MarketingDeploymentViews MDV2 
						 WHERE MDV2.Deployment_id = '".$this->db->mysqli->escape_string($deploy_id)."' 
						 AND MDV2.Email = MDV.Email
						 ORDER BY MDV2.ReadDate ASC LIMIT 1 ) 
						 AS DateFirstOpen, 
					   ( SELECT MDV3.ReadDate 
						 FROM MarketingDeploymentViews MDV3
						 WHERE MDV3.Deployment_id = '".$this->db->mysqli->escape_string($deploy_id)."'  
						 AND MDV3.Email = MDV.Email
						 ORDER BY MDV3.ReadDate DESC LIMIT 1 ) 
						AS DateLastOpen
					FROM
						MarketingDeploymentViews MDV
					LEFT JOIN
						Persons
						ON MDV.Email = Persons.Email
					WHERE
						MDV.Deployment_id = '".$this->db->mysqli->escape_string($deploy_id)."' 
					GROUP BY MDV.Email";
						
				$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error); 
				$results['total'] = $query->num_rows;
				
				while($row = $query->fetch_assoc())
				{
					$results['rows'][] = array('person_id'=>$row['PersonID'], 'email'=>$row['Email'], 'date_first'=>$row['DateFirstOpen'], 'date_last'=>$row['DateLastOpen']);
				}
				break;
			 case 'clicks':
				$sql = "
					SELECT
						MarketingDeploymentEvents.MarketingDeploymentEvents_date,
						MarketingDeploymentEvents.MarketingDeploymentEvents_clickedUrl,
						ifNull(Persons.Person_id, 0) AS PersonID,
						MarketingDeploymentEvents.MarketingDeploymentEvents_emailAddress AS Email
					FROM
						MarketingDeploymentEvents
					LEFT JOIN
						Persons
						ON MarketingDeploymentEvents.MarketingDeploymentEvents_emailAddress = Persons.Email
					WHERE
						MarketingDeploymentEvents.MarketingDeploymentEvents_eventType = 4
						AND MarketingDeploymentEvents.MarketingDeploymentEvents_deploymentId = '".$this->db->mysqli->escape_string($deploy_id)."'
					";
						
				$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error); 
				$results['total'] = $query->num_rows;

				while($row = $query->fetch_assoc())
				{
					$results['rows'][] = array('person_id'=>$row['PersonID'], 'email'=>$row['Email'], 'date'=>$row['MarketingDeploymentEvents_date'], 'url'=>$row['MarketingDeploymentEvents_clickedUrl']);
				}
				
				//$results['rows'] = array_slice($results['rows'], $offset, $limit);
				$results['has_url']  = true;
				$results['has_date'] = true;
				
				break; 
			case 'bounces':
				$sql = "
					SELECT
						MarketingDeploymentEvents.MarketingDeploymentEvents_date,
						ifNull(Persons.Person_id, 0) AS PersonID,
						MarketingDeploymentEvents.MarketingDeploymentEvents_emailAddress AS Email
					FROM
						MarketingDeploymentEvents
					LEFT JOIN
						Persons
						ON MarketingDeploymentEvents.MarketingDeploymentEvents_emailAddress = Persons.Email
					WHERE
						MarketingDeploymentEvents.MarketingDeploymentEvents_eventType IN (2, 6)
						AND MarketingDeploymentEvents.MarketingDeploymentEvents_deploymentId = '".$this->db->mysqli->escape_string($deploy_id)."'
					GROUP BY MarketingDeploymentEvents.MarketingDeploymentEvents_emailAddress";
						
				$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error); 
				$results['total'] = $query->num_rows;
				
				while($row = $query->fetch_assoc())
				{
					$results['rows'][] = array('person_id'=>$row['PersonID'], 'email'=>$row['Email'], 'date'=>$row['MarketingDeploymentEvents_date']);
				}
				
				//$results['rows'] = array_slice($results['rows'], $offset, $limit);
				$results['has_date'] = true;
				break;
		}
 
        return $results;
    }
	
	function render_reporting_grid($type, $data) {
		if($data['total'] > 0) {
			$html = '
			<div class="row">
				<div class="col-md-4">
					<div class="m-input-icon m-input-icon--left">
						<input type="text" class="form-control m-input m-input--solid" placeholder="Search..." id="search_'.$type.'">
						<span class="m-input-icon__icon m-input-icon__icon--left">
							<span>
								<i class="la la-search"></i>
							</span>
						</span>
					</div>
				</div>
			</div>
			<br />';
			if($type != 'opens_unique') {
				$html .= '
				<table id="'.$type.'_tbl" class="m-datatable deploy_report_tbl" width="100%">
					<thead>
						<tr>
							<th>Date</th>
							<th>E-Mail Address</th>
							'.( $data['has_url'] ? '<th>Link Clicked</th>' : '' ).'
						</tr>
					</thead>
					<tbody>';
				foreach($data['rows'] as $recipient) {
					$html .= '
					<tr>
						<td>'.( $recipient['date'] > 0 ? date('Y-m-d H:i', $recipient['date']) : '' ).'</td>
						<td>'.$recipient['email'].( !empty($recipient['person_id']) ? ' <a href="/profile/'.$recipient['person_id'].'" target="_blank" class="m-link" style="color:#7b7e8a;" title="Click to view the associated person record"><i class="la la-external-link-square"></i></a>' : '' ).'</td>
						'.( $data['has_url'] ? '<td>'.$recipient['url'].'</td>' : '' ).'
					</tr>';
				}
			} else {
				$html .= '
				<table id="'.$type.'_tbl" class="m-datatable deploy_report_tbl" width="100%">
					<thead>
						<tr>
							<th>E-Mail Address</th>
							<th>Date First Opened</th>
							<th>Date Last Opened</th>
						</tr>
					</thead>
					<tbody>';
				foreach($data['rows'] as $recipient) {
					$html .= '
					<tr>
						<td>'.$recipient['email'].( !empty($recipient['person_id']) ? ' <a href="/profile/'.$recipient['person_id'].'" target="_blank" class="m-link" style="color:#7b7e8a;" title="Click to view the associated person record"><i class="la la-external-link-square"></i></a>' : '' ).'</td>
						<td>'.( $recipient['date_first'] > 0 ? date('Y-m-d H:i', $recipient['date_first']) : '' ).'</td>
						<td>'.( $recipient['date_last'] > 0 ? date('Y-m-d H:i', $recipient['date_last']) : '' ).'</td>
					</tr>';
				}
			}
			$html .= '
					</tbody>
				</table>';
		} else {
			$html = '<div id="'.$type.'_tbl" class="alert alert-info">There is no data for this report.</div>';
		}
		return $html;
	}
    
    /**
     * Generate deployment preview
     * 
     * 
     * @param   int     $deploy_id
     * @param   string  $type       "text"|"html"
     * @return  string
     */
    public function generate_deployment_preview($deploy_id, $type='html')
    {
        $data = $this->get_deployment($deploy_id);
        $external_esp = true;	//($data['DeploymentEsp'] != '' && $data['DeploymentEsp'] != 'local');
        
        switch($type)
        {
            case 'html':
                $field = 'EmailBody';
                $body = $data[$field];
                $body = $this->prepend_msg_header($body, $deploy_id, 0);
                $body = $this->append_msg_footer($body, $deploy_id, 0, false, $external_esp);
                $body = $this->replace_mergefields($deploy_id, $body, 0, false, true);
                break;
                
            case 'text':
                $field = 'EmailBodyText';
                $body = $data[$field];
                $body = $this->prepend_msg_header($body, $deploy_id, 0, true);
                $body = $this->append_msg_footer($body, $deploy_id, 0, true, $external_esp);
                $body = $this->replace_mergefields($deploy_id, $body, 0, false, true);
                $body = '<pre>'.$body.'</pre>';
                break;
                
            case 'mobile':
                $field = 'EmailBodyMobile';
                $body = $data[$field];
                $body = $this->append_msg_footer($body, $deploy_id, 0, false, $external_esp);
                $body = $this->replace_mergefields($deploy_id, $body, 0, false, true);
                break;
        }

        return $body;
    }
    
    /**
     * Get merge data
     * 
     * Returns a associative array of all available merge fields and 
     * their corresponding data for the specified person record
     * 
     * @param   int     $deploy_id
     * @param   int     $person_id
     * @param	int		$event_id
     * @package	int		$event_coupon_id
     * @param   bool    $include_pid
     * @param   int     $site_id
     * @return  array
     */
    function get_merge_data($deploy_id, $person_id, $event_id,$event_coupon_id, $include_pid=false, $site_id=1)
    {
        $merge_array = $this->get_merge_fields();
        
        //get the deployment data
        $data = ($deploy_id == 0) ? false :$this->get_deployment($deploy_id);
        
        $viewonline_url = ($deploy_id == 0) ? '' : $this->settings['BASE_URL'].'view-email.php?id='.$deploy_id.'&pid='.$person_id;
        $mobile_url = ($deploy_id == 0) ? '' : $this->settings['BASE_URL'].'mobile_view.php?id='.$deploy_id.'&pid='.$person_id;
        
            
        //get the person record
        $p_query = "SELECT * FROM Persons WHERE Person_id = '".$this->db->mysqli->escape_string($person_id)."' LIMIT 1";
        $p_send = $this->db->mysqli->query($p_query);
        
        if($p_send === false)
        {
            $this->send_error_msg('Marketing SQL Error - Marketing::get_merge_data()','URL:'.$this->settings['BASE_URL'].'<br><pre>'.'Line: '.__LINE__ .' - '.$this->db->mysqli->error.'</pre><br>'.$p_query);
            exit;
        }
        
        $p_data = $p_send->fetch_assoc();
        
        foreach($this->merge_fields as $field => $field_data)
        {
			$db_field = $field_data['db'];
            $m_data = '';
           
			if($field == '#OptOutLink')
			{
				$m_data = $this->optout_url;
			}
			elseif($field == '#ViewOnlineLink')
			{
				$m_data = $viewonline_url;
			}
			elseif($field == '#MobileLink')
			{
				$m_data = $mobile_url;
			}
			else if($field == '#OptInReason')
			{
				$m_data = $this->optin_reason;
			}
			else if($field == '#MailingAddress')
			{
				$addr_company = $this->settings['COMPANY_NAME'];
				$addr_street1 = $this->settings['MAILING_ADDRESS_LINE1'];
				$addr_street2 = $this->settings['MAILING_ADDRESS_LINE2'];
				$addr_city = $this->settings['MAILING_ADDRESS_CITY'];
				$addr_state = $this->settings['MAILING_ADDRESS_STATE'];
				$addr_postal = $this->settings['MAILING_ADDRESS_POSTAL'];

				$m_data  = $addr_company.', '.$addr_street1.', '.((strlen($addr_street2) > 0) ? $addr_street2.', ' : '');
				$m_data .= $addr_city.', '.$addr_state.' '.$addr_postal;
			}
			else
			{
				//all other data is pulled in as-is from Persons
				$m_data = $p_data[$db_field];
			}
                
            $merge_array[$field] = $m_data;
        }
        
        if($include_pid)
        {
            $merge_array['#PersonID'] = $person_id;
        }

        return $merge_array;
    }
    
    /**
     * Get deployment lists
     * 
     * Returns an array of all marketing lists associated with the specified deployment
     * 
     * @param   int     $deploy_id
     * @param   bool    $include_inactive   Set to TRUE to include inactive lists in the return array 
     * @return  array
     */
    public function get_deployment_lists($deploy_id, $include_inactive=false)
    {
        $lists = array();
        
        $sql = "
            SELECT 
               MarketingList_id
            FROM 
                MarketingDeploymentLists
            INNER JOIN
                MarketingLists
                ON MarketingLists_id = MarketingList_id
            WHERE 
                Deployment_id = '".$this->db->mysqli->escape_string($deploy_id)."' ";
                
        if(!$include_inactive)
        {
            $sql .= " AND MarketingLists_active = 1 ";
        }
        
        $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        
        while($l = $query->fetch_assoc())
        {
            $lists[] = $l['MarketingList_id'];
        }
        
        return $lists;
    }
    
    
    /**
     * Get list groups
     * 
     * Returns an array of all CRM groups associated with the specified Marketing List
     * 
     * @param   int     $list_id
     * @return  array       
     */
    public function get_list_groups($list_id)
    {
        $sql   = "SELECT MarketingLists_groups FROM MarketingLists WHERE MarketingLists_id = '".$this->db->mysqli->escape_string($list_id)."' LIMIT 1";
        $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        $data  = $query->fetch_assoc();
        
        if(strlen($data['MarketingLists_groups']) < 1)
        {
            return array();
        }
        else
        {
            $groups = explode('|', $data['MarketingLists_groups']);
        
            foreach($groups as $key => $g)
            {
                if($g < 1 || !is_numeric($g))
                {
                    unset($groups[$key]);
                }
                else
                {
                    //check if the group is still active
                    $sql   = "SELECT `Groups_id` FROM `Groups` WHERE `Groups_id` = '".$g."' AND `Groups_active`='1' LIMIT 1";
                    $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
                    
                    if($query->num_rows < 1)
                    {
                        unset($groups[$key]);
                    }
                }
            }
        
            return array_merge($groups);
        }
    }
    
    /**
     * Build group e-mails SQL
     * 
     * Creates the SQL query statement used to select all e-mails from the specified CRM group
     * 
     * @param   int     $group_id
     * @return  string
     */
    public function build_group_emails_sql($group_id)
    {
		require_once("assets/vendors/modules/PHP-SQL-Parser/src/PHPSQLParser.php");
		$parser = new PHPSQLParser($sql);
        $g_query = "SELECT * FROM `Groups` WHERE Groups_id = '".$this->db->mysqli->escape_string($group_id)."' LIMIT 1";
        $g_send  = $this->db->mysqli->query($g_query);
        $g_data  = $g_send->fetch_assoc();
        $sql 	 = stripslashes($g_data['Groups_baseQuery']);
		if(!isset($parser->parsed['HAVING'])) {
			$sql .= " GROUP BY Persons.Person_id";
		}
        return $sql;
    }
    
    
    /**
     * Get list counts
     * 
     * Retrieves the total records, total emails, and total faxes counts for the specified CRM groups
     * 
     * @param   array   $groups     An array of CRM group IDs
     * @return  array               Structure: array('records'=>(integer), 'emails'=>(integer), 'faxes'=>(integer))
     */
    public function get_group_counts($groups)
    {
        $rec_count   = 0;
        $email_count = 0;
        $fax_count   = 0;			//Fax count always returns 0 because we do not currently have a fax feature in the system
		$person_ids  = array();
		
		foreach($groups as $group_id) {
			$group_count = 0;
			$group_result = $this->db->get_single_result("SELECT * FROM `Groups` WHERE `Groups_id` = ".$group_id."");
           if(!array_key_exists('error', $group_result) && !array_key_exists('empty_result', $group_result) && $group_result['Groups_parentTable'] == 'Persons') {
				if($group_result['Groups_baseQuery'] != '') {
					$group_data = $this->db->get_multi_result(stripslashes($group_result['Groups_baseQuery']));
					if(!array_key_exists('error', $group_data) && !array_key_exists('empty_result', $group_data)) {
						foreach($group_data as $group_row) {
							$person_ids[$group_row['Person_id']] = 1;
						}
					}
				}
				
			}
		}
		
		$rec_count = count($person_ids);
		if(count($person_ids) > 0) {
			$email_sql = "
			SELECT COUNT(*) AS EmailCount
			FROM Persons
			WHERE Person_id IN (".implode(',', array_keys($person_ids)).")
			AND Email != ''
			AND Email NOT IN (SELECT DISTINCT Optouts_email FROM MarketingOptOuts)
			AND Email NOT IN (SELECT DISTINCT EmailAddress FROM MarketingDeploymentVerify WHERE FailureOverride != '1' AND Failure='1')
			";
           $email_result = $this->db->get_single_result($email_sql);
			$email_count = $email_result['EmailCount'];
		}
        
        return array('records'=>$rec_count, 'emails'=>$email_count, 'faxes'=>$fax_count);
    }
    
    /**
     * Add to group
     * 
     * Adds an array of person IDs to the specified CRM group
     * 
     * @param   array   $person_ids
     * @param   int     $group_id 
     * @return  bool
     */
    public function add_to_group($person_ids, $group_id)
    {
        if(empty($person_ids))
        {
            return false;
        }
        
        $person_ids = array_unique($person_ids);
        
        $pt_query   = $this->db->mysqli->query("SELECT * FROM ParentTables WHERE Table_DB_Name = 'Persons' LIMIT 1") or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        $data       = $pt_query->fetch_assoc();
        $grp_table  = $data['GroupTable'];
        $key_field  = $data['KeyField'];
        
        foreach($person_ids as $id)
        {
            $sc_com = "SELECT * FROM CompanyPersons WHERE PersonID = '".$this->db->mysqli->escape_string($id)."' LIMIT 1";
            $sc_com = $this->db->mysqli->query($sc_com);
            $sc_num = $sc_com->num_rows;
            
            if($sc_num > 0)
            {
                $com_data   = $sc_com->fetch_assoc();
                $company_id = $com_data['CompanyID'];
            }
            else
            {
                $company_id = 0;
            }
            
            $search_sql = "SELECT * FROM ". $grp_table. " WHERE ".$key_field." = '".$id."' AND CompanyID = '".$company_id."' AND GroupID = '".$group_id."' "; 

            $search = $this->db->mysqli->query($search_sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
            $row    = $search->num_rows;

            if($row == 0)
            {
                $insert_sql = "
                    INSERT INTO ".$grp_table." (".$key_field." , CompanyID, GroupID, AddGroupDate)" 
                    ."VALUES ('".$id."' "
                    .",'".$company_id."' "
                    .",'".$group_id."' "
                    .",'".time()."' "
                    ." ) ";
    
                $insert = $this->db->mysqli->query($insert_sql);
                
                $data_str['Action']     = "Add to Group";
                $data_str['GroupTable'] = $grp_table;
                $data_str[$key_field]   = $id;
                $data_str['GroupID']    = $group_id;
                $data_str_line          = serialize($data_str);
                
                $insert_log = "INSERT INTO UserLog (UserID, LogDate, BaseTable, LogDataStored, RecordID) VALUES ('".$_SESSION['system_user_id']."','".time()."', 'Persons', '".$this->db->mysqli->escape_string($data_str_line)."','".$id."' )";             
                $insert_log = $this->db->mysqli->query($insert_log);
                
                unset($data_str_line);
            }
        }
        
        return true;
    }
    
    
    /**
     * Get deployment
     *
     * Get the deployment record data
     * 
     * 
     * @param   int     $deploy_id   
     * @return  array
     */
    public function get_deployment($deploy_id)
    {
        $sql   = "SELECT * FROM MarketingDeployments WHERE MarketingDeployments_id = '".$this->db->mysqli->escape_string($deploy_id)."' LIMIT 1";
        $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        $data  = $query->fetch_assoc();
        
        return $data;
    }
	
	    
    /**
     * Get marketing event
     *
     * Get data for the marketing deployment event
     * 
     * 
     * @param   int     $deploy_id   
     * @return  array
     */
    public function get_marketing_event($event_id)
    {
        $sql   = "SELECT * FROM MarketingDeploymentEvents WHERE MarketingDeploymentEvents_id = '".$this->db->mysqli->escape_string($event_id)."' LIMIT 1";
        $query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        $data  = $query->fetch_assoc();
        
        return $data;
    }
    
    /**
     * Get merge fields
     *
     * @return  array   structure: array('#MergeField'=>'DatabaseField', ...)
     */
     public function get_merge_fields()
     {
        return $this->merge_fields;
     }
     
     /**
      * Get substitution fields
      *
      * @param  string  $left_enclosure
      * @param  string  $right_enclosure
      * @return array
      */
     public function get_substitution_fields($left_enclosure='', $right_enclosure='')
     {
        $fields = array();

		$fields[] = $left_enclosure.'EmailAddress'.$right_enclosure;
        
        foreach($this->merge_fields as $field => $db)
        {
            $fields[] = $left_enclosure.str_replace('#', '', $field).$right_enclosure;
        }
        
        /*foreach($this->event_merge_fields as $field)
        {
            $fields[] = $left_enclosure.$field.$right_enclosure;
        }*/

        $fields[] = $left_enclosure.'PersonID'.$right_enclosure; 

        return $fields;
     }

     /**
      * Get password
      * 
      * Helper function for replace_event_mergfields()
      * 
      * @param   string  $person_id
      * @return  string
      */
     function get_password($person_id)
     {
        include_once("class.encryption.php");
		$sql = "SELECT Password FROM Persons WHERE PersonID = '".$this->db->mysqli->escape_string($person_id)."' ";
        $query = $this->db->mysqli->query($sql);        
		$ENC = new encryption();
        if ($query->num_rows == 0)
        {
            $data = $query->fetch_assoc();            
            if(trim($ENC->decrypt($data['Password'])) != '')
            {
                return $ENC->decrypt($data['Password']);
            }
            else
            {
                $generatePassword = true;           
            }       
        }   
        else
        {
            $generatePassword = true;
        }
        
        if($generatePassword)
        {
            //generate a new password
            $chars = "ABCDXYZabcdefghijkmnopqrstuvwxyz023456789";
            
            srand((double)microtime()*1000000);
            
            $i = 0;
            
            $newPassword = '';
            
            while ($i <= 8)
            {
                $num = rand() % 33;
                $tmp = substr($chars, $num, 1);
                $newPassword .= $tmp;
                $i++;
            }           
            
            $sql = "UPDATE Persons SET Password = '".$this->db->mysqli->escape_string($ENC->encrypt($newPassword))."' WHERE PersonID = '".$this->db->mysqli->escape_string($person_id)."' ";
            $query = $this->db->mysqli->query($sql);
            
            return $newPassword;
        }
    }
    
    /**
     * Get username
     * 
     * Helper function for replace_event_mergfields()
     * 
     * @param   string  $person_id
     * @param   string  $email
     * @return  string
     */
    function get_username($person_id, $email)
    {
        $sql = "SELECT WebuserID FROM Persons WHERE PersonID = '".$this->db->mysqli->escape_string($person_id)."' ";
        $query = $this->db->mysqli->query($sql);
        
        if ($query->num_rows == 0)
        {
            $data = $query->fetch_assoc(); 
             
            if(trim($data['WebuserID']) != '')
            {
                return $data['WebuserID'];
            }
            else
            {
                $sql = "UPDATE Persons SET WebuserID = '".$this->db->mysqli->escape_string($email)."' WHERE PersonID = '".$this->db->mysqli->escape_string($person_id)."' ";
                $query = $this->db->mysqli->query($sql);
                
                return $email;       
            }
        }       
        else
        {
            $sql = "UPDATE Persons SET WebuserID = '".$this->db->mysqli->escape_string($email)."' WHERE PersonID = '".$this->db->mysqli->escape_string($person_id)."' ";
            $query = $this->db->mysqli->query($sql);
            
            return $email;
        }   
    }
    
    /**
     * Replace event merge fields
     * 
     * 
     * @param   string  $email_body
     * @param   string  $person_id
     * @param   string  $email
     * @return 
     */
    
    function replace_event_mergefields($email_body, $person_id, $email)
    {
        global $tempPersonId;
        global $tempEmail;
        $tempPersonId = $person_id;
        $tempEmail = $email;
        $hashSalt1 = 'cls';
        $hashSalt2 = '361';
        //set the unique person identifier for this personId
        $uniquePersonId = '&pid='.$person_id.'_'.substr(sha1($hashSalt1.$person_id.$hashSalt2),0,10);
        $email_body = str_replace('##pCode##',$uniquePersonId, $email_body);
        
        //search for any couponlinks
        $email_body = preg_replace_callback('/##cCode_([0-9]+)##/',
            function($matches) {
               global $tempPersonId;
               return "&cid=".$tempPersonId."_".$matches[1]."_".substr(sha1("cls".$tempPersonId.$matches[1]."361"),0,10);        
            },
            $email_body);
            
        $email_body = preg_replace_callback('/#Password/',
            function($matches) {
            // single quotes are essential here,
                // or alternative escape all $ as \$            
               global $tempPersonId;
               return $this->get_password($tempPersonId);
            },
            $email_body);
            
        $email_body = preg_replace_callback('/#UserName/',
            function($matches) {
                global $tempEmail;
                global $tempPersonId;
                return $this->get_username($tempPersonId,$tempEmail);
            },
            $email_body);
            
        //search for any confirmationNumbers
        $email_body = preg_replace_callback('/##ConfirmationNumber_([0-9]+)##/',
            function($matches){
                global $tempPersonId;return $matches[1]."-".$tempPersonId."-".substr(md5($matches[1]."-".$tempPersonId."fghj4ts"),0,15);
            },
            $email_body);
        
        return $email_body;
    }
    
    /**
     * Merge event session body
     * 
     * 
     * @param   string  $body
     * @param   int     $person_id
     * @param   int     $event_id
     * @return  string
     */
    public function merge_event_session_body($body, $person_id, $event_id)
    {
        //first get all active time slots for this event
        $timeslot_sql = "SELECT * FROM EventTimeSlots
                    WHERE EventID = '".$event_id."'
                    AND Active = 1
                    ORDER BY StartTime
                    ";
                    
        $timeslot_query = $this->db->mysqli->query($timeslot_sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
        
        while ($timeslot_data = $timeslot_query->fetch_assoc())
        {
            $timeslot_html .= "<b>".date("l, F j: g:ia", $timeslot_data['StartTime'])." - ".date("g:ia", $timeslot_data['EndTime'])."</b><br />"; 
            //get session that person has signed up for in each time slot
            $timeslotID = $timeslot_data['TimeSlotID'];  
            $session_sql = "SELECT ES.* FROM EventSessions ES
                            LEFT JOIN EventSessionPersons ESP ON ES.SessionID = ESP.SessionID
                            WHERE ESP.PersonID = '".$this->db->mysqli->escape_string($person_id)."'
                            AND ES.TimeSlotID = '".$this->db->mysqli->escape_string($timeslotID)."'
                            AND ES.Active = 1
                            ";
                            
            $session_query = $this->db->mysqli->query($session_sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
            
            if ($session_query->num_rows != 0)
            {
                $session_data = $session_query->fetch_assoc();
                $timeslot_html .= $session_data['SessionTitle']."<br /><br />";
            }
            else
            {
                $session_sql = "SELECT ES.* FROM EventSessions ES
                            WHERE ES.TimeSlotID = '".$this->db->mysqli->escape_string($timeslotID)."'
                            AND ES.Active = 1
                            ";
                            
                $session_query = $this->db->mysqli->query($session_sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
                
                if ($session_query->num_rows > 1)
                {
                    $timeslot_html .= "<i>You have not yet picked a session for this time slot. Available sessions are:</i><br /><ul>";
                    while ($session_data = $session_query->fetch_assoc())
                    {
                        $timeslot_html .= "<li>".$session_data['SessionTitle']."</li>";
                    }
                    
                    $timeslot_html .= "</ul>";
                }
                else
                {
                    if ($session_query->num_rows == 1)
                    {
                        $session_data = $session_query->fetch_assoc();
                        $timeslot_html .= $session_data['SessionTitle']."<br /><br />";
                    }
                    else
                    {
                        $timeslot_html .= "<i>Sessions have not yet been created for this time slot.</i><br /><br />";
                    }
                }
            }
        }
        
        $body = str_replace('#SessionInfo', $timeslot_html, $body);
        
        return $body;
    }
    
    /**
     * Email URLs
     *
     * Adds website base URL (BASEURL) to relative links before sending email
     * 
     * @param   string  $content
     * @uses    BASEURL
     * @return  string
     */
    public function email_urls($content)
    {
        $links  = array();
        $images = array();
                          
        //regular expression to find href values        
        $regex_pattern = '/<\s*a [^\>]*href\s*=\s*[\""\']?([^\""\'\s>]*)/i'; 
        preg_match_all($regex_pattern, $content, $linkURLs);
        
        //removes duplicate values to avoid the url being formatted multiple times
        $links = $linkURLs[1];
        $links = array_unique($links); 
    
        //regular expression to find src values
        $regex_pattern_img = '/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'\s>]*)/i';  
        preg_match_all($regex_pattern_img, $content, $imageURLs);
        
        //removes duplicate values to avoid the url being formatted multiple times
        $images = $imageURLs[1];
        $images = array_unique($images); 
                
         //loop through href values
        foreach ($links AS $link)
        {
            if(substr($link,0,11) == '/userfiles/' && stripos($link, $this->settings['BASE_URL']) === false)
            { 
                //add BASEURL to href value
                $content = str_replace('href="'.$link, 'href="'.$this->settings['BASE_URL'].substr($link,1),$content);
                $content = str_replace("href='".$link, "href='".$this->settings['BASE_URL'].substr($link,1),$content);
            }
        }
        
        //loop through src values
        foreach ($images AS $image)
        { 
            if(substr($image,0,11) == '/userfiles/' && stripos($image, $this->settings['BASE_URL']) === false)
            {
                //add BASEURL to src value
                $content = str_replace('src="'.$image, 'src="'.$this->settings['BASE_URL'].substr($image,1),$content); 
                $content = str_replace("src='".$image, "src='".$this->settings['BASE_URL'].substr($image,1),$content);
            }
        }
        
        return $content;
    }
    
    /**
     * Valid email
     * 
     * Checks if a string is a well-formed e-mail address
     * 
     * @param   string  $email
     * @return  bool
     */
	function valid_email($address) {
		return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? false : true;
	}

    /**
     * From domain whitelisted
     *
     * Returns true if the passed email address' domain is on the FROM domain whitelist, or if the setting does not have a value
     *
     * @param   string  $email
     * @return  bool
     */
    public function domain_whitelisted($email)
    {
        if(strlen($this->settings['DOMAIN_WHITELIST']) < 1) return true;
        
        $domains = array();
        $parts = explode(',', $this->settings['DOMAIN_WHITELIST']);
        if(empty($parts)) return true;

        foreach($parts as $index => $part) {
            $part = trim(strtolower($part));
            if(strlen($part) > 0) $domains[] = $part;
        }

        if(empty($domains)) return true;

        $emailparts = explode('@', $email);
        $domain = (!isset($emailparts[1])) ? '' : trim(strtolower($emailparts[1]));

        if(empty($domain)) return false;

        return in_array($domain, $domains);
    }

    /**
     * Schedule job
     *
     * Schedules a one-off task for the specified via the linux 'at' command
     * Note: The time is relative to the system's default timezone
     * 
     *
     * @param   string  $comd
     * @param   int     $timestamp
     * @return  bool|string if successful, returns the scheduled job's internal ID
     */
    public function schedule_job($cmd, $timestamp) {
		set_include_path(dirname(__FILE__).'/phpsec');
        require_once('Net/SSH2.php');
        //create an ssh2 connection and login
        $ssh = new Net_SSH2($this->ssh_host);
        if(!$ssh->login($this->ssh_user, $this->ssh_password)) return false;

        //build the at command string and execute it
        $current_tz = date_default_timezone_get();
        date_default_timezone_set($this->settings['SERVER_TIMEZONE']);
        $cmd = 'echo "'.$cmd.'" | at '.date('H:i m/d/Y', $timestamp);
        date_default_timezone_set($current_tz);
		//echo "cmd=$cmd<br>";
        $output = $ssh->exec($cmd);
		//echo "cmd output=$output<br>";
        if(empty($output)) return false;

        //parse the output to determine if the job was scheduled successfully, and to get the job ID
        $find = preg_match('/^job (.+) will/i', $output, $matches);
        return (empty($find) || empty($matches[1])) ? false : $matches[1];
    }

    /**
     * Remove job
     *
     * Removes a pending job scheduled via schedule_job()
     *
     * @param   string  $job_id
     * @return  bool
     */
    public function remove_job($job_id) {
        set_include_path(dirname(__FILE__).'/phpsec');
        require_once('Net/SSH2.php');
        //create an ssh2 connection and login
        $ssh = new Net_SSH2($this->ssh_host);
        if(!$ssh->login($this->ssh_user, $this->ssh_password)) return false;

        //run atrm with the specified job id
        $cmd = "atrm ".$job_id;
        $output = $ssh->exec($cmd);

        return ($output !== false);
    }

    /**
     * Database reconnect
     *
     * Attempts to re-establish the database connection if it was closed or timed out
     *
     * @param 
     * @return void
     */
    private function db_reconnect()
    {
        $this->db->mysqli->close();
        $this->db->connect();
    }
        
    /**
     * Send error message
     * 
     * 
     * @param   string  $subj
     * @param   string  $msg
     * @param   string  $to
     * @return  void
     */
    private function send_error_msg($subj, $msg, $to='')
    {
        echo $msg;
        if(empty($to)) $to = $this->notify_address;
        mail($to, $subj, $msg, 'From: system@kelleher-international.com');
    }
	
    /**
     * Get stylesheet override flag
     *
     *
     * @param   int     $deploy_id
     * @return  int
     */
	public function get_stylesheet_override_flag($deploy_id)
	{
		$setting = 0;
		$sql = "SELECT OverrideStylesheet FROM MarketingDeployments WHERE DeploymentID = '".$deploy_id."'";
		$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
		if($query->num_rows > 0) {
			$data = $query->fetch_assoc();
			$setting = $data['OverrideStylesheet'];
		}	
		return $setting;
	}
	
	/**
	 * Delete marketing events
	 *
	 * Removes all records of marketing events for this email address
	 * and event type and bounce type
	 *
	 * @param	string	$email
	 * @param	string	$event_type
	 * @param	string	$bounce_type
	 * @return	bool
	*/
	public function delete_marketing_events($email, $event_type, $bounce_type) {
		$sql = "DELETE FROM MarketingDeploymentEvents
				WHERE MarketingDeploymentEvents_emailAddress = '".$this->db->mysqli->escape_string($email)."'
				AND MarketingDeploymentEvents_eventType = '".$this->db->mysqli->escape_string($event_type)."'
				AND MarketingDeploymentEvents_bounceType = '".$this->db->mysqli->escape_string($bounce_type)."'
		";
		$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
		return $query;
	}
	
	/**
	 * Set failure override
	 *
	 * Sets failure override flag to true for all records
	 * in email deployment verify table that are marked as failed
	 *
	 * @param	string	$email
	 * @return	bool
	*/
	public function set_failure_override($email) {
		$sql = "UPDATE MarketingDeploymentVerify
				SET FailureOverride = '1'
				WHERE EmailAddress = '".$this->db->mysqli->escape_string($email)."'
				AND Failure = '1'
		";
		$query = $this->db->mysqli->query($sql) or die('Line: '.__LINE__ .' - '.$this->db->mysqli->error);
		return $query;
	}
	
	/**
	 * Delete bounces
	 *
	 * Removes the bounces associated with the given email address from the local database
	 * as well as from all connected ESP bounce lists, and clears the email's invalid flag
	 *
	 * @param	string	$email
	 * @return	bool
	*/
	public function delete_bounces($email) {
		//send request to SendGrid API to delete bounces
		if($this->esp == 'sendgrid' && is_object($this->sendgrid)) {
			$response = $this->sendgrid->send_request('bounces.delete', array('email' => $email));
		}

		//override all existing failures of the associated Email record
		$update = $this->set_failure_override($email);
		//delete the marketing event records
		$delete = $this->delete_marketing_events($email, 6, 'bounce');
		$delete = $this->delete_marketing_events($email, 2, 'bounce');
		$delete = $this->delete_marketing_events($email, 2, '');
		if($delete) {
			$this->update_email_status($email, 1);
		}

		return ($update && $delete);
	}
	
	/**
	 * Delete blocks
	 *
	 * Removes the blocks associated with the given email address from the local database
	 * as well as from all connected ESP block lists
	 *
	 * @param	string	$email
	 * @return	bool
	 */
	function delete_blocks($email) {
		//send request to SendGrid API to delete blocks
		if($this->esp == 'sendgrid' && is_object($this->sendgrid)) {
			$response = $this->sendgrid->send_request('blocks.delete', array('email' => $email));
		}

		//delete the marketing event records
		$delete = $this->delete_marketing_events($email, 6, 'blocked');
		$delete = $this->delete_marketing_events($email, 2, 'blocked');
		if($delete) {
			$this->update_email_status($email, 1);
		}

		return $delete;
	}
	
	/**
	 * Get product menu specials
	 *
	 * Gets the html and plain text versions of the products on special
	 * for the current day. Used in deployments and automated e-mails.
	 *
	 * @param	int	$site_id
	 * @return	array
	 */
	function get_product_menu_specials($site_id) {
		$return_arr = array('result' => 0, 'html' => '', 'plain' => '');
		$weekday = date('D');
		$sql = "SELECT * FROM smpl_products_items WHERE site_id='".$site_id."' AND product_special_days LIKE '%".$weekday."%'";
		$result = $this->db->get_multi_result($sql);
		if(!array_key_exists('error', $result) && !array_key_exists('empty_result', $result)) {
			$return_arr['result'] = 1;
			$count = 1;
			foreach($result as $product) {
				$price = ($product['product_price_special'] == '') ? $product['product_price'] : $product['product_price_special'];
				$return_arr['html'] .= '<p><strong>'.$count.'. '.$product['product_name'].'</strong>  $'.$price.'<br />'.$product['product_description'].'</p>';
				$return_arr['plain'] .= $count.". ".$product['product_name']."  $".$price;
				if($product['product_description'] != '') {
					$return_arr['plain'] .= "
".$product['product_description'];
				}
				$return_arr['plain'] .= "
			
";
				$count++;
			}
		}
		return $return_arr;
	}

}
