<?php

namespace dekuan\deuclientpro;

use dekuan\delib\CLib;


/**
 *      class of UCProCookie
 * 
 * 	All operations/methods about cookies and encrypted $_COOKIE['X'], $_COOKIE['T']
 *
 */
class UCProCookie extends UCProBase
{
	//
	//	$_COOKIE or parsed from sCookieString
	//
	protected $m_arrCookie		= [];



        public function __construct()
        {
        	parent::__construct();

        	//	...
		$this->m_arrCookie	= ( is_array( $_COOKIE ) ? $_COOKIE : [] );
        }
        public function __destruct()
        {
        	parent::__destruct();
        }




	public function isExistsCookie( $arrCk = null )
	{
		if ( null == $arrCk )
		{
			$arrCk = $this->m_arrCookie;
		}
		return $this->isValidCookieArray( $arrCk );
	}

	public function isValidCookieArray( $arrCk )
	{
		return ( CLib::IsArrayWithKeys( $arrCk, [ UCProConst::CKX, UCProConst::CKT ] ) &&
			CLib::IsExistingString( $arrCk[ UCProConst::CKX ] ) &&
			CLib::IsExistingString( $arrCk[ UCProConst::CKT ] ) );
	}

	public function getCookieArray()
	{
		//
		//	get XT array from cookie
		//
		return Array
		(
			UCProConst::CKX => UCProLib::getSafeVal( UCProConst::CKX, $this->m_arrCookie, '' ),
			UCProConst::CKT => UCProLib::getSafeVal( UCProConst::CKT, $this->m_arrCookie, '' ),
		);
	}

	public function getCookieString()
	{
		$sRet		= '';
		$arrDecryptedXT	= $this->getCookieArray();
		if ( CLib::IsArrayWithKeys( $arrDecryptedXT, [ UCProConst::CKX, UCProConst::CKT ] ) )
		{
			$sRet = http_build_query
			(
				[
					UCProConst::CKX	=> $arrDecryptedXT[ UCProConst::CKX ],
					UCProConst::CKT	=> $arrDecryptedXT[ UCProConst::CKT ]
				],
				'', '; '
			);
		}

		return $sRet;
	}

	public function parseCookieString( $sXTString )
	{
		if ( ! CLib::IsExistingString( $sXTString ) )
		{
			return null;
		}

		//	...
		$arrRet	= [];
		$sX	= '';
		$sT	= '';

		//
		//	parse X, T from string
		//
		$arrData = explode( '; ', $sXTString );
		if ( is_array( $arrData ) && count( $arrData ) > 1 )
		{
			parse_str( $arrData[ 0 ], $arrCk0 );
			parse_str( $arrData[ 1 ], $arrCk1 );

			if ( is_array( $arrCk0 ) )
			{
				if ( empty( $sX ) && array_key_exists( UCProConst::CKX, $arrCk0 ) )
				{
					$sX = $arrCk0[ UCProConst::CKX ];
				}
				else if ( empty( $sT ) && array_key_exists( UCProConst::CKT, $arrCk0 ) )
				{
					$sT = $arrCk0[ UCProConst::CKT ];
				}
			}
			if ( is_array( $arrCk1 ) )
			{
				if ( empty( $sX ) && array_key_exists( UCProConst::CKX, $arrCk1 ) )
				{
					$sX = $arrCk1[ UCProConst::CKX ];
				}
				else if ( empty( $sT ) && array_key_exists( UCProConst::CKT, $arrCk1 ) )
				{
					$sT = $arrCk1[ UCProConst::CKT ];
				}
			}

			//
			//	put the values to cookie
			//
			if ( is_string( $sX ) && strlen( $sX ) &&
				is_string( $sT ) && strlen( $sT ) )
			{
				$arrRet[ UCProConst::CKX ]	= $sX;
				$arrRet[ UCProConst::CKT ]	= $sT;
			}
		}

		return $arrRet;
	}

	public function memsetCookieByCookieArray( $arrEncryptedCk )
	{
		if ( ! $this->isValidCookieArray( $arrEncryptedCk ) )
		{
			return UCProError::ERR_INVALID_XT_COOKIE;
		}

		//
		//	set XT values to member variable
		//
		if ( ! is_array( $this->m_arrCookie ) )
		{
			$this->m_arrCookie = [];
		}

		$this->m_arrCookie[ UCProConst::CKX ]	= $arrEncryptedCk[ UCProConst::CKX ];
		$this->m_arrCookie[ UCProConst::CKT ]	= $arrEncryptedCk[ UCProConst::CKT ];

		return UCProError::ERR_SUCCESS;
	}



        //
	//	...
	//
	public function setCookiesForLogin( $arrCookie, $bKeepAlive, & $sCkString = '' )
	{
		if ( ! $this->isValidCookieArray( $arrCookie ) )
		{
			return false;
		}

		//
		//	set the expire date as 1 year.
		//	the browser will keep this cookie for 1 year.
		//
		$tmExpire = ( $bKeepAlive ? ( time() + UCProConst::CONFIG_TIME_SECONDS_YEAR ) : 0 );
		return $this->setCookies( $arrCookie, $tmExpire, $sCkString );
	}

	public function setCookiesForLogout()
	{
		$arrCookie	= Array( UCProConst::CKX => '', UCProConst::CKT => '' );
		$tmExpire	= time() - UCProConst::CONFIG_TIME_SECONDS_YEAR;
		return $this->setCookies( $arrCookie, $tmExpire );
	}

	public function setCookies( $arrCookie, $tmExpire, & $sCkString = '' )
	{
		if ( ! CLib::IsArrayWithKeys( $arrCookie, [ UCProConst::CKX, UCProConst::CKT ] ) ||
			! is_string( $arrCookie[ UCProConst::CKX ] ) ||
			! is_string( $arrCookie[ UCProConst::CKT ] ) )
		{
			return false;
		}
		if ( ! is_numeric( $tmExpire ) )
		{
			return false;
		}

		//	...
		$sDomain	= $this->getCookieDomain();
		$sPath		= $this->m_arrCfg[ UCProConst::CFGKEY_PATH ];
		$sXValue        = rawurlencode( $arrCookie[ UCProConst::CKX ] );
		$sTValue        = rawurlencode( $arrCookie[ UCProConst::CKT ] );
		$sCkString      = http_build_query( Array( UCProConst::CKX => $sXValue, UCProConst::CKT => $sTValue ), '', '; ' );

		//	...
		if ( $this->m_arrCfg[ UCProConst::CFGKEY_HTTPONLY ] && $this->isSupportedSetHttpOnly() )
		{
			setcookie( UCProConst::CKX, $sXValue, $tmExpire, $sPath, $sDomain, $this->m_arrCfg[ UCProConst::CFGKEY_SECURE ], true );
			setcookie( UCProConst::CKT, $sTValue, $tmExpire, $sPath, $sDomain, $this->m_arrCfg[ UCProConst::CFGKEY_SECURE ], true );
		}
		else
		{
			setcookie( UCProConst::CKX, $sXValue, $tmExpire, $sPath, $sDomain );
			setcookie( UCProConst::CKT, $sTValue, $tmExpire, $sPath, $sDomain );
		}

		return true;
	}


	public function getCookieDomain()
	{
		return UCProLib::getSafeVal( UCProConst::CFGKEY_DOMAIN, $this->m_arrCfg, '' );
	}

	public function isSupportedSetHttpOnly()
	{
		//	set http-only for cookie is supported
		return version_compare( phpversion(), '5.2.0', '>=' );
	}
}

