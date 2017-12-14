<?php

namespace dekuan\deuclientpro\Models;

use dekuan\delib\CLib;
use dekuan\deuclientpro\Libs\UCProLib;
use dekuan\deuclientpro\UCProConst;
use dekuan\deuclientpro\UCProError;


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




	public function isExistsCookie()
	{
		return $this->isValidCookieArray( $this->m_arrCookie );
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
		$arrCookie	= $this->getCookieArray();
		if ( $this->isValidCookieArray( $arrCookie ) )
		{
			$sRet = http_build_query
			(
				[
					UCProConst::CKX	=> $arrCookie[ UCProConst::CKX ],
					UCProConst::CKT	=> $arrCookie[ UCProConst::CKT ]
				],
				'', '; '
			);
		}

		return $sRet;
	}

	public function parseCookieString( $sCookieString )
	{
		if ( ! CLib::IsExistingString( $sCookieString ) )
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
		$arrData = explode( '; ', $sCookieString );
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

	public function msetCookieByCookieArray( $arrCookie )
	{
		if ( ! $this->isValidCookieArray( $arrCookie ) )
		{
			return UCProError::MODELS_UCPROCOOKIE_MSETCOOKIEBYCOOKIEARRAY_PARAM_COOKIE;
		}

		//
		//	set XT values to member variable
		//
		if ( ! is_array( $this->m_arrCookie ) )
		{
			$this->m_arrCookie = [];
		}

		$this->m_arrCookie[ UCProConst::CKX ]	= $arrCookie[ UCProConst::CKX ];
		$this->m_arrCookie[ UCProConst::CKT ]	= $arrCookie[ UCProConst::CKT ];

		return UCProError::SUCCESS;
	}



        //
	//	...
	//
	public function setCookiesForLogin( $arrCookie, $bKeepAlive, & $sCookieString = '' )
	{
		if ( ! $this->isValidCookieArray( $arrCookie ) )
		{
			return UCProError::MODELS_UCPROCOOKIE_SETCOOKIESFORLOGIN_PARAM_COOKIE;
		}

		//
		//	set the expire date as 1 year.
		//	the browser will keep this cookie for 1 year.
		//
		$tmExpire	= ( $bKeepAlive ? ( time() + UCProConst::CONFIG_TIME_SECONDS_YEAR ) : 0 );
		$nCall		= $this->setCookies( $arrCookie, $tmExpire, $sCookieString );
		if ( UCProError::SUCCESS == $nCall )
		{
			return UCProError::SUCCESS;
		}
		else
		{
			return $nCall;
		}
	}

	public function setCookiesForLogout()
	{
		$arrCookie	= [ UCProConst::CKX => '', UCProConst::CKT => '' ];
		$tmExpire	= time() - UCProConst::CONFIG_TIME_SECONDS_YEAR;
		$nCall		= $this->setCookies( $arrCookie, $tmExpire );
		if ( UCProError::SUCCESS == $nCall )
		{
			return UCProError::SUCCESS;
		}
		else
		{
			return $nCall;
		}
	}

	public function setCookies( $arrCookie, $tmExpire, & $sCookieString = '' )
	{
		if ( ! $this->isValidCookieArray( $arrCookie ) )
		{
			return UCProError::MODELS_UCPROCOOKIE_SETCOOKIES_PARAM_COOKIE;
		}
		if ( ! is_numeric( $tmExpire ) )
		{
			return UCProError::MODELS_UCPROCOOKIE_SETCOOKIES_PARAM_TMEXPIRE;
		}

		//	...
		$sDomain	= $this->getConfig_sDomain();
		$sPath		= $this->m_arrCfg[ UCProConst::CFGKEY_PATH ];
		$sXValue	= rawurlencode( $arrCookie[ UCProConst::CKX ] );
		$sTValue	= rawurlencode( $arrCookie[ UCProConst::CKT ] );
		$sCookieString	= http_build_query( Array( UCProConst::CKX => $sXValue, UCProConst::CKT => $sTValue ), '', '; ' );

		//	...
		if ( $this->getConfig_bHttpOnly() && $this->isSupportedSetHttpOnly() )
		{
			setcookie( UCProConst::CKX, $sXValue, $tmExpire, $sPath, $sDomain, $this->getConfig_bSecure(), true );
			setcookie( UCProConst::CKT, $sTValue, $tmExpire, $sPath, $sDomain, $this->getConfig_bSecure(), true );
		}
		else
		{
			setcookie( UCProConst::CKX, $sXValue, $tmExpire, $sPath, $sDomain );
			setcookie( UCProConst::CKT, $sTValue, $tmExpire, $sPath, $sDomain );
		}

		return UCProError::SUCCESS;
	}

	public function isSupportedSetHttpOnly()
	{
		//	set http-only for cookie is supported
		return version_compare( phpversion(), '5.2.0', '>=' );
	}
}

