<?php

namespace dekuan\deuclientpro;

use dekuan\delib\CLib;


/**
 *	CUClientPro
 */
class UClientPro extends UCProBase
{
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
        	parent::__construct();

        	//	...
		$this->m_cUCProMain	= new UCProMain();

        	//	...
                $this->m_arrCfg		=
                [
			UCProConst::CFGKEY_DOMAIN	=> UCProConst::DEFAULT_DOMAIN,
			UCProConst::CFGKEY_PATH		=> UCProConst::DEFAULT_PATH,
			UCProConst::CFGKEY_SEED		=> UCProConst::DEFAULT_SIGN_SEED,	//	seed
			UCProConst::CFGKEY_SECURE	=> UCProConst::DEFAULT_SECURE,
			UCProConst::CFGKEY_HTTPONLY	=> UCProConst::DEFAULT_HTTPONLY,
			UCProConst::CFGKEY_SS_TIMEOUT	=> UCProConst::DEFAULT_SS_TIMEOUT,	//	session timeout, default is 1 day.
                ];

		//	clone config to ...
		$this->m_cUCProMain->cloneConfig( $this->m_arrCfg );
	}
        public function __destruct()
        {
        	parent::__destruct();
        }


        //
        //	configuration
        //
        public function getConfig( $sKey = '' )
        {
                if ( CLib::IsExistingString( $sKey ) &&
			array_key_exists( $sKey, $this->m_arrCfg ) )
                {
                        return $this->m_arrCfg[ $sKey ];
                }
                else
                {
                        return $this->m_arrCfg;
                }
        }
        public function setConfig( $sKey, $vValue )
        {
                if ( CLib::IsExistingString( $sKey ) &&
			array_key_exists( $sKey, $this->m_arrCfg ) )
                {
                	//	...
                        $this->m_arrCfg[ $sKey ] = $vValue;

                        //	clone config to ...
			$this->m_cUCProMain->cloneConfig( $this->m_arrCfg );

			//	...
                        return true;
                }
                else
                {
                        return false;
                }
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
                return ( $this->m_cUCProMain->getCookieInstance()->setCookiesForLogout() ? UCProError::ERR_SUCCESS : UCProError::ERR_FAILURE );
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
		$nCall	= $this->m_cUCProMain->checkXT();
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
		$nKeepAlive = intval( $this->getXTValue( UCProConst::CKT, UCProConst::CKT_KP_ALIVE ) );
		return ( 1 === $nKeepAlive );
	}

	public function getCookieString()
	{
		return $this->m_cUCProMain->getCookieInstance()->getCookieString();
	}
	

	
	
	
	
	
	public function getXTArray()
	{
		return $this->m_cUCProMain->getXTInstance()->decryptXTArray( $this->m_cUCProMain->getCookieInstance()->getCookieArray() );
        }

	public function getXTValue( $sNode, $sKey )
	{
		//
		//	sNode	- values( 'X', 'T' )
		//	sKey	- keys
		//	RETURN	- ...
		//
		if ( ! CLib::IsExistingString( $sNode ) ||
			! CLib::IsExistingString( $sKey ) )
		{
			return null;
		}

		//	...
		$vRet = null;

		//	...
		$arrData = $this->getXTArray();
		if ( CLib::IsArrayWithKeys( $arrData, $sNode ) )
		{
			if ( CLib::IsArrayWithKeys( $arrData[ $sNode ], $sKey ) )
			{
				$vRet = $arrData[ $sNode ][ $sKey ];
			}
		}

		return $vRet;
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
		$nRefreshTime = $this->getXTValue( UCProConst::CKT, UCProConst::CKT_REFRESH_TM );
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
				$nLoginTime = $this->getXTValue( UCProConst::CKT, UCProConst::CKT_LOGIN_TM );
				if ( is_numeric( $nLoginTime ) )
				{
					//
					//	escaped time in seconds after user logged in
					//      the default timeout is 1 day.
					//
					$fTerm	= floatval( time() - floatval( $nLoginTime ) );
					$bRet	= ( $fTerm > $this->m_arrCfg[ UCProConst::CFGKEY_SS_TIMEOUT ] );
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

        ////////////////////////////////////////////////////////////////////////////////
        //	protected
        //







}

