<?php

namespace dekuan\deuclientpro;

use dekuan\delib\CLib;


/**
 *      class of UCProMain
 * 
 * 	All operations/methods about XT Array
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
		//	RETURN		- self::ERR_SUCCESS successfully, otherwise error id
		//
		$arrXTArray	= null;
		$nCall		= $this->m_cUCProXT->flashXTArray( $arrData, $bKeepAlive, $arrXTArray );
		if ( UCProError::ERR_SUCCESS == $nCall )
		{
			//	...
			$arrCookie	= $this->m_cUCProXT->encryptXTArray( $arrData );
			$nCall		= $this->m_cUCProXT->getCookieInstance()->setCookiesForLogin( $arrCookie, $bKeepAlive, $sCookieString );
			if ( UCProError::ERR_SUCCESS == $nCall )
			{
				$nRet = UCProError::ERR_SUCCESS;
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
			return UCProError::ERR_PARAMETER;
		}

		//	...
		$nRet		= UCProError::ERR_UNKNOWN;
		$bKeepAlive	= boolval( $bKeepAlive );
		$arrCookie	= $this->m_cUCProXT->getCookieInstance()->parseCookieString( $sCookieString );
		if ( $this->m_cUCProXT->getCookieInstance()->isValidCookieArray( $arrCookie ) )
		{
			if ( UCProError::ERR_SUCCESS ==
				$this->m_cUCProXT->getCookieInstance()->msetCookieByCookieArray( $arrCookie ) )
			{
				if ( $bResponseCookie )
				{
					//
					//	response cookie via HTTP header to the client
					//
					$nCall = $this->m_cUCProXT->getCookieInstance()->setCookiesForLogin( $arrCookie, $bKeepAlive );
					if ( UCProError::ERR_SUCCESS == $nCall )
					{
						$nRet = UCProError::ERR_SUCCESS;
					}
					else
					{
						$nRet = $nCall;
					}
				}
				else
				{
					//	from now on, we are successful
					$nRet = UCProError::ERR_SUCCESS;
				}
			}
			else
			{
				$nRet = UCProError::ERR_RESET_COOKIE;
			}
		}
		else
		{
			$nRet = UCProError::ERR_INVALID_XT_COOKIE;
		}

		return $nRet;
	}


	
}

