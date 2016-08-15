<?php
class MyCrypt {
  //
  static function encrypt($text, $pass, $mac = true) {
    if (static::isEmpty($text))
      return $text;
    $c = static::getCryptastic();
    $key = static::getEncryptKey($c, $pass);
    return $c->encrypt($text, $key, true, $mac);
  }
  static function decrypt($text, $pass, $mac = true) {
    if (static::isEmpty($text))
      return $text;
    $c = static::getCryptastic();
    $key = static::getEncryptKey($c, $pass);
    return $c->decrypt($text, $key, true, $mac);
  }
  static function getEncryptKey($c, $pass) {
    static $lastPass;
    static $lastKey;
    if ($pass == $lastPass)
      return $lastKey;
    if ($c == null)
      $c = static::getCryptastic();
    $key = $c->pbkdf2($pass, 'sodiumchloride', 1000, 32);
    $lastPass = $pass;
    $lastKey = $key;
    return $key;
  }
  //
  protected static function getCryptastic() {
    static $c;
    if ($c == null)
      $c = new cryptastic();
    return $c;
  }
  protected static function isEmpty($s) {
    return is_null($s) || $s === '';
  }
}
//
interface AutoEncrypt {}  /* For marking records that should be automatically encrypted on save, decrypted on fetch */
//
class MyCrypt_Auto extends MyCrypt {
  //
  static function encrypt($text) {
    if (! static::shouldEncrypt())
      return $text;
    if (static::isEmpty($text) || static::isEncrypted($text))
      return $text;
    $c = static::getCryptastic();
    $key = static::getEncryptKey($c);
    $text = "@@@@" . $c->encrypt($text, $key, true, false);
    return $text;
  }
  static function decrypt($text) {
    if (static::isEmpty($text))
      return $text;
    if (static::isEncrypted($text)) {
      $c = static::getCryptastic();
      $key = static::getEncryptKey($c);
      $text = $c->decrypt(substr($text, 4), $key, true, false);
    }
    return $text;
  }
  static function hash($text) {
    if (static::isEmpty($text))
      return $text;
    $key = static::getHashKey();
    $text = sha1(strtoupper($text) . $key);
    return $text;
  }
  //
  static function shouldEncrypt() {
    return false;
    global $login;
    if ($login && ($login->userGroupId == 1 || $login->userGroupId == 2481 || $login->userGroupId == 73)) {
      $encrypt = 1;
    } else {
      $encrypt = MyEnv::$ENCRYPT;
    }
    return $encrypt;
  }
  static function getEncryptKey($c) {
    global $login;
    return ($login && isset($login->mcsk)) ? $login->mcsk : LoginSession::fetchMcsk();
  }
  static function getHashKey() {
    global $login;
    return ($login && isset($login->mchk)) ? $login->mchk : MyEnv::getMchk();
  }
  static function isEncrypted($text) {
    if (! empty($text))
      return substr($text, 0, 4) == '@@@@';
  }
}
class MyCrypt_Sql extends MyCrypt_Auto {
  //
  static function encrypt($rec, $fid) {
    if (isset($rec->$fid))
      $rec->$fid = parent::encrypt($rec->$fid);
  }
  static function decrypt($rec, $fid) {
    if (isset($rec->$fid))
      $rec->$fid = parent::decrypt($rec->$fid);
  }
}
/**
 * Cryptastic
 * http://www.itnewb.com/tutorial/PHP-Encryption-Decryption-Using-the-MCrypt-Library-libmcrypt
 *
 * Cipher used: rijndael-256 @see http://en.wikipedia.org/wiki/Advanced_Encryption_Standard
 * rijndael-256 is identical to AES-256 except for size of IV (initialization vector). AES-256 uses 32 byte key, 16 byte IV; rijndael-256 uses 32 byte key, 32 byte IV.
 */
class cryptastic {

    /** Encryption Procedure
     *
     *  @param mixed msg message/data
     *  @param string k encryption key
     *  @param boolean base64 base64 encode result
     *
     *  @return string iv+ciphertext+mac or
     * boolean false on error
    */
    public function encrypt( $msg, $k, $base64 = false, $usemac = true ) {
        # open cipher module (do not change cipher/mode)
        if ( ! $td = mcrypt_module_open('rijndael-256', '', 'ctr', '') )
            return false;

        //$msg = serialize($msg);                         # serialize
        $iv = mcrypt_create_iv(32, MCRYPT_RAND);        # create iv

        if ( mcrypt_generic_init($td, $k, $iv) !== 0 )  # initialize buffers
            return false;

        $msg = mcrypt_generic($td, $msg);               # encrypt
        $msg = $iv . $msg;                              # prepend iv
        if ($usemac) {
          $mac = $this->pbkdf2($msg, $k, 1000, 32);       # create mac
          $msg .= $mac;                                   # append mac
        }

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
    public function decrypt( $msg, $k, $base64 = false, $usemac = true ) {

        if ( $base64 ) $msg = base64_decode($msg);          # base64 decode?

        # open cipher module (do not change cipher/mode)
        if ( ! $td = mcrypt_module_open('rijndael-256', '', 'ctr', '') )
            return false;

        $iv = substr($msg, 0, 32);                          # extract iv
        if ($usemac) {
          $mo = strlen($msg) - 32;                            # mac offset
          $em = substr($msg, $mo);                            # extract mac
          $msg = substr($msg, 32, strlen($msg)-64);           # extract ciphertext
          $mac = $this->pbkdf2($iv . $msg, $k, 1000, 32);     # create mac
          if ( $em !== $mac )                                 # authenticate mac
              return false;
        } else {
          $msg = substr($msg, 32);
        }

        if ( mcrypt_generic_init($td, $k, $iv) !== 0 )      # initialize buffers
            return false;

        $msg = mdecrypt_generic($td, $msg);                 # decrypt
        //$msg = unserialize($msg);                           # unserialize

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

        $hl = strlen(hash($a, null, true)); # Hash length
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
}