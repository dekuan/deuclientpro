<?php

namespace dekuan\deuclientpro;


/**
 *      class of UCProEncrypt
 */
class UCProEncrypt extends UCProBase
{
        public function __construct()
        {
        	parent::__construct();
        }
        public function __destruct()
        {
        	parent::__destruct();
        }


	static function _Decrypt( $vData )
	{
		if ( ! is_string( $vData ) && ! is_numeric( $vData ) )
		{
			return '';
		}
		$sString = strval( $vData );
		return rawurldecode( str_rot13( $sString ) );
	}

	static function _Encrypt( $vData )
	{
		if ( ! is_string( $vData ) && ! is_numeric( $vData ) )
		{
			return '';
		}
		$sString = strval( $vData );
		return str_rot13( rawurlencode( $sString ) );
	}

	static function _DecryptBase64( $vData )
	{
		if ( ! is_string( $vData ) && ! is_numeric( $vData ) )
		{
			return '';
		}
		$sString = strval( $vData );
		return rawurldecode( base64_decode( str_rot13( $sString ) ) );
	}

	static function _EncryptBase64( $vData )
	{
		if ( ! is_string( $vData ) && ! is_numeric( $vData ) )
		{
			return '';
		}
		$sString = strval( $vData );
		return str_rot13( base64_encode( rawurlencode( $sString ) ) );
	}
        
}

