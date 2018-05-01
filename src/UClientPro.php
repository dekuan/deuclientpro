<?php

namespace dekuan\deuclientpro;

use dekuan\delib\CLib;
use dekuan\deuclientpro\Libs\UCProLib;


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

	/**
	 *	cache
	 *	@var null
	 */
	protected $m_bIsLoggedIn	= null;
	
	
	/**
	 *	UClientPro constructor.
	 */
        public function __construct()
        {
		$this->m_cUCProMain	= new Models\UCProMain();
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
	 *	@param string $sKey
	 *	@return array|mixed
	 */
        public function getConfig( $sKey = '' )
        {
        	return $this->m_cUCProMain->getConfig( $sKey );
        }
	
	/**
	 *	set configuration
	 *	@param $sKey
	 *	@param $vValue
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
                if ( null !== $this->m_bIsLoggedIn )
                {
                        //	have already checked
                        return ( $this->m_bIsLoggedIn ? 
				UCProError::SUCCESS : UCProError::UCLIENTPRO_CHECKLOGIN_FAILURE );
                }

		//	...
		$nRet	= UCProError::UCLIENTPRO_CHECKLOGIN_FAILURE;

                //	...
		$nCall	= $this->m_cUCProMain->getXTInstance()->checkXTArray();
                if ( UCProError::SUCCESS == $nCall )
		{
			if ( ! $this->isSessionTimeout() )
			{
				$nRet = UCProError::SUCCESS;
			}
			else
			{
				//      Session is timeout
				$nRet = UCProError::UCLIENTPRO_CHECKLOGIN_SESSION_TIMEOUT;
			}
                }
                else
                {
                        $nRet = $nCall;
                }

                //	push to cache
                $this->m_bIsLoggedIn = ( UCProError::SUCCESS == $nRet );

                //	...
                return $nRet;
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
	public function getCookieString()
	{
		return $this->m_cUCProMain->getXTInstance()->getCookieInstance()->getCookieString();
	}

	/**
	 *	if the session timeout
	 *	@return bool
	 */
	public function isSessionTimeout()
	{
		//      ...
		$bRet = false;

		//      ...
		$bValidSession  = true;
		$cSession	= new UCProSession();

		//
		//	Check session via service if T->CKT_REFRESH_TM is timeout
		//
		$nRefreshTime = $this->m_cUCProMain->getXTInstance()->getTValue( UCProConst::CKT_REFRESH_TM );
		if ( time() - $nRefreshTime > 0 )
		{
			//	refresh time is timeout, it's time to check via Redis
			$bValidSession = $cSession->IsValidSession();
		}

		//      ...
		if ( $bValidSession )
		{
			if ( $this->isKeepAlive() )
			{
				//
				//	return false if user set to keep alive
				//
				$bRet = false;
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
					$bRet	= ( $fTerm > $this->m_cUCProMain->getConfig_nSessionTimeout() );
				}
				else
				{
					//
					//      login time is invalid
					//      So, we marked this session as timeout
					//
					$bRet = true;
				}
			}
		}
		else
		{
			//
			//      session is timeout.
			//      So, we marked this session as timeout
			//
			$bRet = true;
		}

		return $bRet;
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
	 *	@return null|string
	 */
	public function getSessionId()
	{
		return $this->getTValue( UCProConst::CKT_SS_ID );
	}

}