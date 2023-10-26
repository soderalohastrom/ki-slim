<?php
/*! \class Settings class.settings.php "class.settings.php"
 *  \brief array holding all of the system and company settings
 */
class Settings {
	public $setting = array();
	/*! \fn obj __constructor($DB)
		\brief settings class constructor.
		\param	$DB db class object
		\return null
	*/

	function __construct() {
		$settings_prod = array(
			'ENV' 						=> 'prod',
			'ESP' 						=> 'sendgrid',
			'SENDGRID_USERNAME'         => 'apikey',
            'SENDGRID_PASSWORD'         => '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',
			'SES_SMTP_USERNAME' 		=> 'AKIA2O3TXCJPWWLLZZWM',
			'SES_SMTP_PASSWORD' 		=> 'BBpR5N7dxCUICEeDuEkuc2Vy0rfO7Z/MgvlGWhCm76qq',
			'SES_SMTP_HOST' 			=> 'email-smtp.us-west-1.amazonaws.com',
			'DOMAIN_WHITELIST'			=> 'kelleher-international.com',
			'COMPANY_NAME'				=> 'Kelleher International',
			'MAILING_ADDRESS_LINE1' 	=> '145 Corte Madera Town Center #422',
			'MAILING_ADDRESS_LINE2' 	=> '',
			'MAILING_ADDRESS_CITY'  	=> 'Corte Madera',
			'MAILING_ADDRESS_STATE' 	=> 'CA',
			'MAILING_ADDRESS_POSTAL'	=> '94925',
			'RINGCENTRAL_APP_KEY'		=> 'a8HrNNMqSF2F-XEJNbKwRQ',							
			'RINGCENTRAL_APP_SECRET'	=> 'j_nH-8GbTp6jIzANqZONKQL39xpV-JTg2PJe5eBxbl1g',
			'RINGCENTRAL_APP2_KEY'		=> 'oDskRDgKRGGdaGoto70KaA',
			'RINGCENTRAL_APP2_SECRET'	=> '6zr5nvBVRMq6-EX4Nl4YwgC5NCB37fTMiDlK0IlihESw',
			'RINGCENTRAL_SERVER'		=> 'https://platform.ringcentral.com',
			'RINGCENTRAL_SANDBOX'		=> false,
			'RINGCENTRAL_MASTER_USER'	=> 186862,
		);
	
		$settings_other = array(
			'DEFAULT_TIMEZONE'			=> 'America/Los_Angeles',
			'SERVER_TIMEZONE'	    	=> 'America/New_York',
			'SERVER_PATH' 				=> $_SERVER['DOCUMENT_ROOT'].'/',
			'BASE_URL' 					=> ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://' ).$_SERVER['SERVER_NAME'].'/',
			'MERGE_FIELDS' 				=> array(
											'#FirstName'      => array('db' => 'FirstName', 'display' => 'First Name'),
											'#LastName'       => array('db' => 'LastName', 'display' => 'Last Name'),
											'#Email'          => array('db' => 'Email', 'display' => 'E-Mail Address'),
											'#Password'       => array('db' => 'Persons_password', 'display' => 'Password'),
											'#pid'			  => array('db' => 'Person_id', 'display' => 'Person ID'),
											'#ViewOnlineLink' => array('db' => '', 'display' => 'View Online Link'),
											'#OptOutLink'     => array('db' => '', 'display' => 'Opt-Out Link'),
											'#OptInReason'    => array('db' => '', 'display' => 'Opt-In Reason'),
											'#MailingAddress' => array('db' => '', 'display' => 'Mailing Address'),
											'#SenderName' 	  => array('db' => '', 'display' => 'Sender Name'),
											'#SenderEmail' 	  => array('db' => '', 'display' => 'Sender Email'),
											'#SenderRoomURL'  => array('db' => '', 'display' => 'Sender Meeting Room URL'),
										),
			'NYLAS_CALLBACK_URL'		=> '',
			'NYLAS_API_ID' 				=> '3gu8dm97zm7y9d5wpcj6yts3s',
			'NYLAS_API_SECRET' 			=> 'ax3tjlzne5k2q7y4mqj6c98rp',
			'RINGCENTRAL_CALLBACK_URL'	=> '',
			'APPOINTMENT_LENGTH'		=> 60,											//Appointment length in minutes
			'APPOINTMENT_DATE_RANGE'	=> array('START' => 1, 'END' => 15),			//For client-facing Appointment Date dropdown: Show START days to END days past today
			'APPOINTMENT_TIMES'			=> array(										//Hour range of available appointment times for each day of the week
											'Sun' => array('FIRST' => 0, 'LAST' => 0), 
											'Mon' => array('FIRST' => 9, 'LAST' => 16), 
											'Tue' => array('FIRST' => 9, 'LAST' => 16), 
											'Wed' => array('FIRST' => 9, 'LAST' => 16), 
											'Thu' => array('FIRST' => 9, 'LAST' => 16), 
											'Fri' => array('FIRST' => 9, 'LAST' => 16),
											'Sat' => array('FIRST' => 0, 'LAST' => 0),
										),
		);
		$settings_other['NYLAS_CALLBACK_URL'] = $settings_other['BASE_URL'].'apis/callback/nylas.php';
		$settings_other['RINGCENTRAL_CALLBACK_URL'] = $settings_other['BASE_URL'].'apis/callback/ringcentral.php';
		$this->setting = array_merge($settings_other, $settings_prod);
		
	}
}
?>
