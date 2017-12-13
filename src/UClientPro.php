<?php

namespace dekuan\deuclientpro;

use dekuan\delib\CLib;


/**
 *	CUClientPro
 */
class UClientPro
{
	//	statics
	protected static $g_cStaticInstance;


	//
	//	All operations/methods about cookies and encrypted $_COOKIE['X'], $_COOKIE['T']
	//
	protected $m_cUCProMain		= null;

	//
	//	cache
	//
	protected $m_bIsLoggedIn	= null;
	


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

	

        //
        //	configuration
        //
        public function getConfig( $sKey = '' )
        {
        	return $this->m_cUCProMain->getConfig( $sKey );
        }
        public function setConfig( $sKey, $vValue )
        {
		return $this->m_cUCProMain->setConfig( $sKey, $vValue );
        }


        //
        //	make user login
        //
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
			return UCProError::ERR_FAILURE;
		}
	}

	//
	//	log out
	//
        public function logout()
        {
                return $this->m_cUCProMain->getXTInstance()->getCookieInstance()->setCookiesForLogout();
        }

        //
        //	log in
        //
        public function checkLogin()
        {
                if ( null !== $this->m_bIsLoggedIn )
                {
                        //	have already checked
                        return ( $this->m_bIsLoggedIn ? UCProError::ERR_SUCCESS : UCProError::ERR_FAILURE );
                }

		//	...
		$nRet	= UCProError::ERR_UNKNOWN;
                
                //	...
		$nCall	= $this->m_cUCProMain->getXTInstance()->checkXTArray();
                if ( UCProError::ERR_SUCCESS == $nCall )
		{
			if ( ! $this->isSessionTimeout() )
			{
				$nRet = UCProError::ERR_SUCCESS;
			}
			else
			{
				//      Session is timeout
				$nRet = UCProError::ERR_LOGIN_TIMEOUT;
			}
                }
                else
                {
                        $nRet = $nCall;
                }

                //	push to cache
                $this->m_bIsLoggedIn = ( UCProError::ERR_SUCCESS == $nRet );

                //	...
                return $nRet;
        }

	public function isKeepAlive()
	{
		$nKeepAlive = intval( $this->m_cUCProMain->getXTInstance()->getTValue( UCProConst::CKT_KP_ALIVE ) );
		return ( 1 === $nKeepAlive );
	}

	public function getCookieString()
	{
		return $this->m_cUCProMain->getXTInstance()->getCookieInstance()->getCookieString();
	}


	//	if login info has timeout
	public function isSessionTimeout()
	{
		//      ...
		$bRet = false;

		//      ...
		$bValidSession  = true;
		$cSession	= new UCSession();

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



}

