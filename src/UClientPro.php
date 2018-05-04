<?php

namespace dekuan\deuclientpro;

use dekuan\delib\CLib;
use dekuan\deuclientpro\Libs\UCProLib;
use dekuan\vdata\CConst;


/**
 *	Class UClientPro
 *	@package dekuan\deuclientpro
 */
class UClientPro
{
	/**
	 *	statics
	 *	@var
	 */
	protected static $g_cStaticInstance;

	/**
	 *	All operations/methods about cookies and encrypted $_COOKIE['X'], $_COOKIE['T']
	 *	@var Models\UCProMain|null
	 */
	protected $m_cUCProMain		= null;
	protected $m_cProSession	= null;
	
	
	/**
	 *	UClientPro constructor.
	 */
        public function __construct()
        {
		$this->m_cUCProMain	= new Models\UCProMain();
		$this->m_cProSession	= UCProSession::getInstance();
	}
        public function __destruct()
        {
        }
	static function getInstance()
	{
		if ( is_null( self::$g_cStaticInstance ) || ! isset( self::$g_cStaticInstance ) )
		{
			self::$g_cStaticInstance = new self();
		}
		return self::$g_cStaticInstance;
	}
	final private function __clone()
	{
	}

	

	/**
	 *	get configuration
	 *	@param	string	$sKey
	 *	@return	array|mixed
	 */
        public function getConfig( $sKey = '' )
        {
        	return $this->m_cUCProMain->getConfig( $sKey );
        }
	
	/**
	 *	set configuration
	 *	@param	$sKey
	 *	@param	$vValue
	 *	@return bool
	 */
        public function setConfig( $sKey, $vValue )
        {
		return $this->m_cUCProMain->setConfig( $sKey, $vValue );
        }

	/**
	 *	make user login
	 *
	 *	@param	$vData
	 *	@param	bool	$bKeepAlive
	 *	@param	string	$sCkString
	 *	@return	int
	 */
        public function makeLogin( $vData, $bKeepAlive = false, & $sCkString = '' )
        {
		if ( UCProLib::isValidXTArray( $vData ) )
		{
			return $this->m_cUCProMain->makeLoginByXTArray( $vData, $bKeepAlive, $sCkString );
		}
		else if ( CLib::IsExistingString( $vData ) )
		{
			return $this->m_cUCProMain->makeLoginByCookieString( $vData, $bKeepAlive, true );
		}
		else
		{
			return UCProError::UCLIENTPRO_MAKELOGIN_FAILURE;
		}
	}

	/**
	 *	logout
	 *	send HTTP headers to clear cookies
	 *
	 *	@return int
	 */
        public function logout()
        {
                return $this->m_cUCProMain->getXTInstance()->getCookieInstance()->setCookiesForLogout();
        }


	/**
	 *	check login
	 *	@return int
	 */
        public function checkLogin()
        {
		//	...
		$nRet	= UCProError::UCLIENTPRO_CHECKLOGIN_FAILURE;

                //	...
		$nCall	= $this->m_cUCProMain->getXTInstance()->checkXTArray();
                if ( UCProError::SUCCESS == $nCall )
		{
			if ( $this->isCookieAlive() )
			{
				$nRet	= UCProError::SUCCESS;
			}
			else
			{
				//      session is timeout
				$nRet	= UCProError::UCLIENTPRO_CHECKLOGIN_SESSION_TIMEOUT;
			}
                }
                else
                {
                        $nRet	= $nCall;
                }

                //	...
                return $nRet;
        }
	
	
	/**
	 *	check session
	 *	@param	int	$nCheckReturn
	 *	@return bool
	 */
	public function checkSession( & $nCheckReturn = null )
	{
		//      ...
		$nRet	= UCProError::UCLIENTPRO_CHECKSESSION_FAILURE;

		//	...
		$objParam	= new \stdClass();
		$objParam->{ UCProConst::CKX_MID }	= $this->getMId();
		$objParam->{ UCProConst::CKT_SS_ID }	= $this->getSessionId();
		$objParam->{ UCProConst::CKT_SS_URL }	= $this->getSessionUrl();
		$objParam->{ 'cookie_array' }		= $this->getCookieArray();

		$nCheckReturn	= null;
		$nCallCheck	= $this->m_cProSession->checkSessionByRPC( $objParam, $nCheckReturn );
		if ( UCProError::SUCCESS == $nCallCheck )
		{
			if ( CConst::ERROR_SUCCESS == $nCheckReturn )
			{
				$nRet	= UCProError::SUCCESS;
			}
			else
			{
				$nRet	= UCProError::UCLIENTPRO_CHECKSESSION_INVALID_RETURN;
			}
		}
		else
		{
			$nRet	= $nCallCheck;
		}

		return $nRet;
	}
	
	
	/**
	 *	check if cookie alive
	 *	@return bool
	 */
	public function isCookieAlive()
	{
		//	...
		$bRet = false;

		if ( $this->isKeepAlive() )
		{
			//
			//	return false if user set to keep alive
			//
			$bRet	= true;
		}
		else
		{
			//	...
			$nLoginTime = $this->m_cUCProMain->getXTInstance()->getTValue( UCProConst::CKT_LOGIN_TM );
			if ( is_numeric( $nLoginTime ) )
			{
				//
				//	escaped time in seconds after user logged in
				//      the default timeout is 1 day.
				//
				$fTerm	= floatval( time() - floatval( $nLoginTime ) );
				$bRet	= ( $fTerm <= $this->m_cUCProMain->getConfig_nCookieTimeout() );
			}
			else
			{
				//
				//      login time is invalid
				//      So, we marked this cookie as timeout
				//
				$bRet	= false;
			}
		}

		return $bRet;
	}
	
	
	/**
	 *	if user set flag to keep the session alive
	 *	@return bool
	 */
	public function isKeepAlive()
	{
		$nKeepAlive = intval( $this->m_cUCProMain->getXTInstance()->getTValue( UCProConst::CKT_KP_ALIVE ) );
		return ( 1 === $nKeepAlive );
	}
	
	/**
	 *	get full cookie string
	 *	@return string
	 */
	public function getCookieArray()
	{
		return $this->m_cUCProMain->getXTInstance()->getCookieInstance()->getCookieArray();
	}

	/**
	 *	get full cookie string
	 *	@return string
	 */
	public function getCookieString()
	{
		return $this->m_cUCProMain->getXTInstance()->getCookieInstance()->getCookieString();
	}
	
	
	/**
	 *	get XT array
	 *	@return array
	 */
	public function getXTArray()
	{
		return $this->m_cUCProMain->getXTInstance()->getXTArray();
	}
	
	/**
	 *	get X value
	 *	@param	$sKey
	 *	@return	null|string
	 */
	public function getXValue( $sKey )
	{
		return $this->m_cUCProMain->getXTInstance()->getXValue( $sKey );
	}
	
	/**
	 *	get T value
	 *	@param	$sKey
	 *	@return	null|string
	 */
	public function getTValue( $sKey )
	{
		return $this->m_cUCProMain->getXTInstance()->getTValue( $sKey );
	}


	/**
	 *	@return	null|string
	 */
	public function getMId()
	{
		return $this->getXValue( UCProConst::CKX_MID );
	}

	/**
	 *	@return	null|string
	 */
	public function getSessionId()
	{
		return $this->getTValue( UCProConst::CKT_SS_ID );
	}

	/**
	 *	@return	null|string
	 */
	public function getSessionUrl()
	{
		return $this->getTValue( UCProConst::CKT_SS_URL );
	}

}