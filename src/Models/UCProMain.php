<?php

namespace dekuan\deuclientpro\Models;

use dekuan\delib\CLib;
use dekuan\deuclientpro\Libs\UCProLib;
use dekuan\deuclientpro\UCProError;


/**
 *      class of UCProMain
 *
 */
class UCProMain extends UCProBase
{
	protected $m_cUCProXT		= null;
	
	
	
        public function __construct()
        {
        	parent::__construct();

		//	...
		$this->m_cUCProXT	= new UCProXT();
        }
        public function __destruct()
        {
        	parent::__destruct();
        }

	public function setConfig( $sKey, $vValue )
	{
		return ( parent::setConfig( $sKey, $vValue ) &&
			$this->m_cUCProXT->setConfig( $sKey, $vValue ) );
	}


	public function getXTInstance()
	{
		return $this->m_cUCProXT;
	}


	//
	//	make login by XT Array
	//
	public function makeLoginByXTArray( $arrData, $bKeepAlive = false, & $sCookieString = '' )
	{
		//
		//	arrData		- [in] Array
		//	(
		//		'X'	=> Array
		//		(
		//			'mid'		=> '101101aaefe12342aaefe12342aaefe12342',
		//			'nkn'		=> '',
		//			't'		=> 0,
		//			'imgid'		=> '',
		//			'act'		=> 0,
		//			'src'		=> '',
		//			'digest'	=> '',
		//		)
		//		'T'	=> Array
		//		(
		//			'v'		=> '',
		//			'ltm'		=> 0,
		//			'rtm'		=> 0,
		//			'utm'		=> 0,
		//			'kpa'		=> 1,
		//			...
		//		)
		//	)
		//	bKeepAlive	- [in] keep alive
		//      sCkString       - [out] a string contains the full XT cookie
		//	RETURN		- self::SUCCESS successfully, otherwise error id
		//
		$nRet		= UCProError::MODELS_UCPROMAIN_MAKELOGINBYXTARRAY_FAILURE;
		$arrXTArray	= null;
		$nCall		= $this->m_cUCProXT->flashXTArray( $arrData, $bKeepAlive, $arrXTArray );
		if ( UCProError::SUCCESS == $nCall &&
			UCProLib::isValidXTArrayInDetail( $arrXTArray ) )
		{
			//	...
			$arrCookie	= $this->m_cUCProXT->encryptXTArray( $arrXTArray );
			$nCall		= $this->m_cUCProXT->getCookieInstance()->setCookiesForLogin( $arrCookie, $bKeepAlive, $sCookieString );
			if ( UCProError::SUCCESS == $nCall )
			{
				$nRet = UCProError::SUCCESS;
			}
			else
			{
				$nRet = $nCall;
			}
		}
		else
		{
			$nRet = $nCall;
		}

		//	...
		return $nRet;
	}
	
	//
	//	make login by cookie string
	//
	public function makeLoginByCookieString( $sCookieString, $bKeepAlive = false, $bResponseCookie = true )
	{
		if ( ! CLib::IsExistingString( $sCookieString ) )
		{
			return UCProError::MODELS_UCPROMAIN_MAKELOGINBYCOOKIESTRING_PARAM_COOKIESTRING;
		}

		//	...
		$nRet		= UCProError::MODELS_UCPROMAIN_MAKELOGINBYCOOKIESTRING_FAILURE;
		$bKeepAlive	= boolval( $bKeepAlive );
		$arrCookie	= $this->m_cUCProXT->getCookieInstance()->parseCookieString( $sCookieString );
		if ( $this->m_cUCProXT->getCookieInstance()->isValidCookieArray( $arrCookie ) )
		{
			$nCall	= $this->m_cUCProXT->getCookieInstance()->msetCookieByCookieArray( $arrCookie );
			if ( UCProError::SUCCESS == $nCall )
			{
				if ( $bResponseCookie )
				{
					//
					//	response cookie via HTTP header to the client
					//
					$nCall = $this->m_cUCProXT->getCookieInstance()->setCookiesForLogin( $arrCookie, $bKeepAlive );
					if ( UCProError::SUCCESS == $nCall )
					{
						$nRet = UCProError::SUCCESS;
					}
					else
					{
						$nRet = $nCall;
					}
				}
				else
				{
					//	from now on, we are successful
					$nRet = UCProError::SUCCESS;
				}
			}
			else
			{
				$nRet = $nCall;
			}
		}
		else
		{
			$nRet = UCProError::MODELS_UCPROMAIN_MAKELOGINBYCOOKIESTRING_INVALID_COOKIE;
		}

		return $nRet;
	}


	
}

