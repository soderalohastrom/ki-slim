<?php
/*! \class Settings class.ssh_settings.php "class.ssh_settings.php"
 *  \brief class that holds server login settings for SSH
 */
class SSH_Settings {
	private $host;
    private $user;
    private $password;
	
	/*! \fn obj __constructor($DB)
		\brief SSH_Settings class constructor.
		\return null
	*/
	public function __construct() {
		$this->host = 'yourserver.pair.com';
		$this->user = 'yourusername';
		$this->password = 'yourpassword';
	}
	
	public function get_host() {
		return $this->host;
	}
	
	public function get_user() {
		return $this->user;
	}
	
	public function get_password() {
		return $this->password;
	}
}
?>