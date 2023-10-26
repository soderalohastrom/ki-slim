<?php
// // Import the Composer Autoloader to make the SDK classes accessible:
require_once __DIR__.'/vendor/autoload.php';

ini_set('max_execution_time', 300);

/**
 * Encryption
 *
 * Encryption class
 * 
 * @package System
 * @author  Brian Benton <bbenton@coollifesystems.com>
 */
class encryption
{
    
	private $crypt_key;
    /**
     * Construct
     * 
     * 
     * @return void
     */
    public function __construct() {
		$this->crypt_key = 'kims2018';
	}

	/**
	 * base64Encrypt
	 *
	 * Encrypts a string based on whatever $key is. 
	 *
	 * @param  string	$string		the string to crypt/decrypts
	 * @param  string	$key		the key
	 * @return string
	 */
	function base64Encrypt($string) {
		$result = '';

		for($i=0; $i<strlen($string); $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($this->crypt_key, ($i % strlen($this->crypt_key))-1, 1);
			$char = chr(ord($char)+ord($keychar));
			$result.=$char;
	  }

	  return base64_encode($result);
	}

	/**
	 * base64Decrypt
	 *
	 * Decrypts a string based on whatever $key is. 
	 *
	 * @param  string	$string		the string to crypt/decrypts
	 * @param  string	$key		the key
	 * @return string
	 */
	function base64Decrypt($string) {
		$result = '';

		$string = base64_decode($string);

		for($i=0; $i<strlen($string); $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($this->crypt_key, ($i % strlen($this->crypt_key))-1, 1);
			$char = chr(ord($char)-ord($keychar));
			$result.=$char;
	  }

	  return $result;
	}

	/** Encryption Procedure
     *
     *  @param mixed msg message/data
     *  @param string k encryption key
     *  @param boolean base64 base64 encode result
     *
     *  @return string iv+ciphertext+mac or
     * 	boolean false on error
    */
    public function encrypt( $msg, $base64 = true ) {
        # open cipher module (do not change cipher/mode)
        if ( ! $td = mcrypt_module_open('rijndael-256', '', 'ctr', '') )
            return false;
 
        $msg = serialize($msg);                         # serialize
        $iv = mcrypt_create_iv(32, MCRYPT_RAND);        # create iv
 
        if ( mcrypt_generic_init($td, $this->crypt_key, $iv) !== 0 )  # initialize buffers
            return false;
 
        $msg = mcrypt_generic($td, $msg);               # encrypt
        $msg = $iv . $msg;                              # prepend iv
        $mac = $this->pbkdf2($msg, $this->crypt_key, 1000, 32);       # create mac
        $msg .= $mac;                                   # append mac
 
        mcrypt_generic_deinit($td);                     # clear buffers
        mcrypt_module_close($td);                       # close cipher module
 
        if ( $base64 ) $msg = base64_encode($msg);      # base64 encode?
 
        return $msg;                                    # return iv+ciphertext+mac
    }
 
    /** Decryption Procedure
     *
     *  @param string msg output from encrypt()
     *  @param string k encryption key
     *  @param boolean base64 base64 decode msg
     *
     *  @return string original message/data or
     * boolean false on error
    */
    public function decrypt( $msg, $base64 = true ) {

        if ( $base64 ) $msg = base64_decode($msg);          # base64 decode?
 
        # open cipher module (do not change cipher/mode)
        if ( ! $td = mcrypt_module_open('rijndael-256', '', 'ctr', '') )
            return false;
 
        $iv = substr($msg, 0, 32);                          # extract iv
        $mo = strlen($msg) - 32;                            # mac offset
        $em = substr($msg, $mo);                            # extract mac
        $msg = substr($msg, 32, strlen($msg)-64);           # extract ciphertext
        $mac = $this->pbkdf2($iv . $msg, $this->crypt_key, 1000, 32);     # create mac
 
        if ( $em !== $mac )                                 # authenticate mac
            return false;
 
        if ( mcrypt_generic_init($td, $this->crypt_key, $iv) !== 0 )      # initialize buffers
            return false;
 
        $msg = mdecrypt_generic($td, $msg);                 # decrypt
        $msg = unserialize($msg);                           # unserialize
 
        mcrypt_generic_deinit($td);                         # clear buffers
        mcrypt_module_close($td);                           # close cipher module
 
        return $msg;                                        # return original msg
    }
 
    /** PBKDF2 Implementation (as described in RFC 2898);
     *
     *  @param string p password
     *  @param string s salt
     *  @param int c iteration count (use 1000 or higher)
     *  @param int kl derived key length
     *  @param string a hash algorithm
     *
     *  @return string derived key
    */
    public function pbkdf2( $p, $s, $c, $kl, $a = 'sha256' ) {
        $hl = strlen(hash($a, '', true)); # Hash length
        $kb = ceil($kl / $hl);              # Key blocks to compute
        $dk = '';                           # Derived key
 
        # Create key
        for ( $block = 1; $block <= $kb; $block ++ ) {
 
            # Initial hash for this block
            $ib = $b = hash_hmac($a, $s . pack('N', $block), $p, true);
 
            # Perform block iterations
            for ( $i = 1; $i < $c; $i ++ )
 
                # XOR each iterate
                $ib ^= ($b = hash_hmac($a, $b, $p, true));
 
            $dk .= $ib; # Append iterated block
        }
 
        # Return derived key of correct length
        return substr($dk, 0, $kl);
    }
	
	public function get_tiny_url($url)  {  
		$ch = curl_init();  
		$timeout = 5;  
		curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url);  
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);  
		$data = curl_exec($ch);  
		curl_close($ch);  
		return $data;  
	}
}