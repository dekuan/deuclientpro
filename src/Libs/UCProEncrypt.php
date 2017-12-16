<?php

namespace dekuan\deuclientpro\Libs;




/**
 *      class of UCProEncrypt
 */
class UCProEncrypt
{
	static function decrypt( $vData )
	{
		if ( ! is_string( $vData ) && ! is_numeric( $vData ) )
		{
			return '';
		}
		$sString = strval( $vData );
		return rawurldecode( str_rot13( $sString ) );
	}

	static function encrypt( $vData )
	{
		if ( ! is_string( $vData ) && ! is_numeric( $vData ) )
		{
			return '';
		}
		$sString = strval( $vData );
		return str_rot13( rawurlencode( $sString ) );
	}

	static function decryptBase64( $vData )
	{
		if ( ! is_string( $vData ) && ! is_numeric( $vData ) )
		{
			return '';
		}
		$sString = strval( $vData );
		return rawurldecode( base64_decode( str_rot13( $sString ) ) );
	}

	static function encryptBase64( $vData )
	{
		if ( ! is_string( $vData ) && ! is_numeric( $vData ) )
		{
			return '';
		}
		$sString = strval( $vData );
		return str_rot13( base64_encode( rawurlencode( $sString ) ) );
	}
        
}

