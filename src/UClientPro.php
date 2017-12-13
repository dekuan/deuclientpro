<?php

namespace dekuan\deuclientpro;

use dekuan\delib\CLib;


/**
 *	CUClientPro
 */
class UClientPro extends UCProBase
{
	//
	//	$_COOKIE or parsed from sCookieString
	//
	protected $m_arrCookie		= [];

	//
	//	configuration
	//
	protected $m_arrCfg		= [];

	//
	//	cache
	//
	protected $m_bIsLoggedIn	= null;
	
	


        public function __construct()
        {
        	parent::__construct();
        	
        	//	...
		$this->m_arrCookie	= ( is_array( $_COOKIE ) ? $_COOKIE : [] );
                $this->m_arrCfg		=
                [
			UCProConst::CFGKEY_DOMAIN	=> UCProConst::DEFAULT_DOMAIN,
			UCProConst::CFGKEY_PATH		=> UCProConst::DEFAULT_PATH,
			UCProConst::CFGKEY_SEED		=> UCProConst::DEFAULT_SIGN_SEED,	//	seed
			UCProConst::CFGKEY_SECURE	=> UCProConst::DEFAULT_SECURE,
			UCProConst::CFGKEY_HTTPONLY	=> UCProConst::DEFAULT_HTTPONLY,
			UCProConst::CFGKEY_STIMEOUT	=> UCProConst::DEFAULT_SS_TIMEOUT,	//	session timeout, default is 1 day.
                ];
        }
        public function __destruct()
        {
        	parent::__destruct();
        }


        //
        //	configuration
        //
        public function GetConfig( $sKey = '' )
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
        public function SetConfig( $sKey, $vValue )
        {
                if ( CLib::IsExistingString( $sKey ) &&
			array_key_exists( $sKey, $this->m_arrCfg ) )
                {
                        $this->m_arrCfg[ $sKey ] = $vValue;
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
		if ( $this->isValidXTArray( $vData ) )
		{
			return $this->_makeLoginByDataArray( $vData, $bKeepAlive, $sCkString );
		}
		else if ( CLib::IsExistingString( $vData ) )
		{
			return $this->_makeLoginByCookieString( $vData, $bKeepAlive );
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
                return ( $this->_setCookieForLogout() ? UCProError::ERR_SUCCESS : UCProError::ERR_FAILURE );
        }

        //
        //	log in
        //
        public function checkLogin()
        {
                //	...
                $nRet = UCProError::ERR_UNKNOWN;

                //	...
                if ( null !== $this->m_bIsLoggedIn )
                {
                        //	have already checked
                        return ( $this->m_bIsLoggedIn ? UCProError::ERR_SUCCESS : UCProError::ERR_FAILURE );
                }

                //	...
                if ( $this->isExistsXT() )
		{
			if ( $this->isValidChecksumMd5() )
			{
				if ( $this->isValidChecksumCrc() )
				{
					if ( ! $this->_IsSessionTimeout() )
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
					$nRet = UCProError::ERR_INVALID_CRC;
				}
			}
			else
			{
				//      invalid sign
				$nRet = UCProError::ERR_INVALID_SIGN;
			}
                }
                else
                {
                        //      cookie is not exists
                        $nRet = UCProError::ERR_BAD_COOKIE;
                }

                //	push to cache
                $this->m_bIsLoggedIn = ( UCProError::ERR_SUCCESS == $nRet );

                //	...
                return $nRet;
        }

	//
	//	reset cookie via cookie string
	//
	public function resetCookie( $sCkString )
	{
		if ( ! CLib::IsExistingString( $sCkString ) )
		{
			return UCProError::ERR_PARAMETER;
		}

		//	...
		$nRet = UCProError::ERR_UNKNOWN;

		//
		//	parse X, T from string
		//
		$arrEncryptedCookie	= $this->GetEncryptedXTArray( $sCkString );
		$nErrorId		= $this->_CheckEncryptedXTArray( $arrEncryptedCookie );
		if ( UCProError::ERR_SUCCESS == $nErrorId )
		{
			//
			//	we checked the cookie from string is okay.
			//
			if ( UCProError::ERR_SUCCESS ==
				$this->_ResetCookieByEncryptedXTArray( $arrEncryptedCookie ) )
			{
				$nRet = UCProError::ERR_SUCCESS;
			}
			else
			{
				$nRet = UCProError::ERR_RESET_COOKIE;
			}
		}
		else
		{
			$nRet = $nErrorId;
		}

		return $nRet;
	}

        public function isExistsXT( $arrCk = null )
        {
		if ( null == $arrCk )
		{
			$arrCk = $this->m_arrCookie;
		}
		return CLib::IsArrayWithKeys( $arrCk, [ UCProConst::CKX, UCProConst::CKT ] );
        }

        public function GetCookieString()
        {
		$sRet		= '';
                $arrDecryptedXT	= $this->GetEncryptedXTArray();
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

	public function GetEncryptedXTArray( $sCkString = null )
	{
		if ( ! CLib::IsExistingString( $sCkString ) )
		{
			//
			//	get XT array from cookie
			//
			return Array
			(
				UCProConst::CKX => $this->_GetSafeVal( UCProConst::CKX, $this->m_arrCookie, '' ),
				UCProConst::CKT => $this->_GetSafeVal( UCProConst::CKT, $this->m_arrCookie, '' ),
			);
		}

		//	...
		$arrRet	= [];
		$sX	= '';
		$sT	= '';

		//
		//	parse X, T from string
		//
		$arrData = explode( '; ', $sCkString );
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
	
	public function GetXTArray()
	{
		return $this->_DecryptXTArray( $this->GetEncryptedXTArray() );
        }

	public function GetXTValue( $sNode, $sKey )
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
		$arrData = $this->GetXTArray();
		if ( CLib::IsArrayWithKeys( $arrData, $sNode ) )
		{
			if ( CLib::IsArrayWithKeys( $arrData[ $sNode ], $sKey ) )
			{
				$vRet = $arrData[ $sNode ][ $sKey ];
			}
		}

		return $vRet;
	}

        public function IsKeepAlive()
        {
                $nKeepAlive = intval( $this->GetXTValue( UCProConst::CKT, UCProConst::CKT_KP_ALIVE ) );
		return ( 1 === $nKeepAlive );
        }

        //	build signature
        public function GetSignData( $arrData )
        {
                $sRet		= '';
                $sString	= $this->_GetDigestSource( $arrData );
                if ( CLib::IsExistingString( $sString ) )
                {
                        $sData	= mb_strtolower( trim( $sString . "-" . $this->m_arrCfg[ UCProConst::CFGKEY_SEED ] ) );
                        $sRet	= md5( $sData );
                }

                //	...
                return $sRet;
        }

        //      build crc checksum
        public function GetCRCData( $arrData )
        {
                $nRet		= 0;
                $sString	= $this->_GetDigestSource( $arrData );
                if ( CLib::IsExistingString( $sString ) )
                {
                        $sData	= mb_strtolower( trim( $sString . "-" . $this->m_arrCfg[ UCProConst::CFGKEY_SEED ] ) );
                        $nRet	= abs( crc32( $sData ) );
                }

                //	...
                return $nRet;
        }

	//
	//	check if XT array is valid
	//
	public function isValidXTArray( $arrCk )
	{
		return ( CLib::IsArrayWithKeys( $arrCk, [ UCProConst::CKX, UCProConst::CKT ] ) &&
			is_array( $arrCk[ UCProConst::CKX ] ) &&
			is_array( $arrCk[ UCProConst::CKT ] ) );
	}

	//
	//	check if XT array is valid in detail
	//
	public function isValidXTArrayInDetail( $arrCk = null )
	{
		$bRet = false;

		if ( null == $arrCk )
		{
			$arrCk = $this->m_arrCookie;
		}

		if ( CLib::IsArrayWithKeys( $arrCk, [ UCProConst::CKX, UCProConst::CKT ] ) )
		{
			if ( is_array( $arrCk[ UCProConst::CKX ] ) && is_array( $arrCk[ UCProConst::CKT ] ) )
			{
				if ( count( $arrCk[ UCProConst::CKX ] ) && count( $arrCk[ UCProConst::CKT ] ) )
				{
					if ( array_key_exists( UCProConst::CKX_MID, $arrCk[ UCProConst::CKX ] ) &&
						array_key_exists( UCProConst::CKX_TYPE, $arrCk[ UCProConst::CKX ] ) &&
						array_key_exists( UCProConst::CKX_STATUS, $arrCk[ UCProConst::CKX ] ) &&
						array_key_exists( UCProConst::CKX_ACTION, $arrCk[ UCProConst::CKX ] ) )
					{
						if ( array_key_exists( UCProConst::CKT_VER, $arrCk[ UCProConst::CKT ] ) &&
							array_key_exists( UCProConst::CKT_LOGIN_TM, $arrCk[ UCProConst::CKT ] ) &&
							array_key_exists( UCProConst::CKT_REFRESH_TM, $arrCk[ UCProConst::CKT ] ) &&
							array_key_exists( UCProConst::CKT_UPDATE_TM, $arrCk[ UCProConst::CKT ] ) &&
							array_key_exists( UCProConst::CKT_KP_ALIVE, $arrCk[ UCProConst::CKT ] ) )
						{
							$bRet = true;
						}
					}
				}
			}
		}

		return $bRet;
	}

	//	check signature
	public function isValidChecksumMd5( $arrEncryptedCk = null )
	{
		$bRet	= false;
		$arrCk	= [];

		if ( null !== $arrEncryptedCk )
		{
			$arrCk = $this->_DecryptXTArray( $arrEncryptedCk );
		}
		else
		{
			$arrCk = $this->GetXTArray();
		}

		if ( $this->isValidXTArray( $arrCk ) )
		{
			$sSignDataNow	= $this->GetSignData( $arrCk );
			$sSignDataCk	= $this->_GetSafeVal( UCProConst::CKT_CKS_SIGN, $arrCk[ UCProConst::CKT ], '' );
			if ( CLib::IsExistingString( $sSignDataNow ) &&
				CLib::IsExistingString( $sSignDataCk ) &&
				CLib::IsCaseSameString( $sSignDataNow, $sSignDataCk ) )
			{
				$bRet = true;
			}
		}

		return $bRet;
	}

	//	check CRC
	public function isValidChecksumCrc( $arrEncryptedCk = null )
	{
		//	...
		$bRet	= false;
		$arrCk	= [];

		if ( null !== $arrEncryptedCk )
		{
			$arrCk = $this->_DecryptXTArray( $arrEncryptedCk );
		}
		else
		{
			$arrCk = $this->GetXTArray();
		}

		if ( $this->isValidXTArray( $arrCk ) )
		{
			$nCRCDataNow	= $this->GetCRCData( $arrCk );
			$nCRCDataCk	= $this->_GetSafeVal( UCProConst::CKT_CKS_CRC, $arrCk[ UCProConst::CKT ], 0 );

			if ( is_numeric( $nCRCDataNow ) &&
				is_numeric( $nCRCDataCk ) &&
				$nCRCDataNow === $nCRCDataCk )
			{
				$bRet = true;
			}
		}

		return $bRet;
	}
	

        ////////////////////////////////////////////////////////////////////////////////
        //	protected
        //

	protected function _makeLoginByDataArray( $arrData, $bKeepAlive = false, & $sCkString = '' )
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
		if ( ! CLib::IsArrayWithKeys( $arrData, [ UCProConst::CKX, UCProConst::CKT ] ) )
		{
			return UCProError::ERR_PARAMETER;
		}
		if ( ! CLib::IsArrayWithKeys( $arrData[ UCProConst::CKX ] ) ||
			! CLib::IsArrayWithKeys( $arrData[ UCProConst::CKT ] ) )
		{
			return UCProError::ERR_PARAMETER;
		}
		if ( ! CLib::IsArrayWithKeys( $arrData[ UCProConst::CKX ],
			[ UCProConst::CKX_MID, UCProConst::CKX_TYPE, UCProConst::CKX_STATUS, UCProConst::CKX_ACTION ] ) )
		{
			return UCProError::ERR_PARAMETER;
		}
		if ( ! CLib::IsArrayWithKeys( $arrData[ UCProConst::CKT ],
			[ UCProConst::CKT_LOGIN_TM, UCProConst::CKT_REFRESH_TM, UCProConst::CKT_UPDATE_TM ] ) )
		{
			return UCProError::ERR_PARAMETER;
		}

		//	...
		$nRet = UCProError::ERR_UNKNOWN;

		//
		//      make signature and crc checksum
		//
		$arrData[ UCProConst::CKT ][ UCProConst::CKT_KP_ALIVE ]	= ( $bKeepAlive ? 1 : 0 );
		$arrData[ UCProConst::CKT ][ UCProConst::CKT_VER ]	= UCProConst::COOKIE_VERSION;
		$arrData[ UCProConst::CKT ][ UCProConst::CKT_CKS_SIGN ]	= $this->GetSignData( $arrData );
		$arrData[ UCProConst::CKT ][ UCProConst::CKT_CKS_CRC ]	= $this->GetCRCData( $arrData );

		//	...
		$arrEncryptedCk = $this->_EncryptXTArray( $arrData );
		if ( CLib::IsArrayWithKeys( $arrEncryptedCk, [ UCProConst::CKX, UCProConst::CKT ] ) )
		{
			if ( $this->_setCookieForLogin( $arrEncryptedCk, $bKeepAlive, $sCkString ) )
			{
				$nRet = UCProError::ERR_SUCCESS;
			}
			else
			{
				$nRet = UCProError::ERR_SET_COOKIE;
			}
		}
		else
		{
			$nRet = UCProError::ERR_ENCRYPT_XT;
		}

		//	...
		return $nRet;
	}
	protected function _makeLoginByCookieString( $sCookieString, $bKeepAlive = false )
	{
		if ( ! CLib::IsExistingString( $sCookieString ) )
		{
			return UCProError::ERR_PARAMETER;
		}

		//	...
		$nRet = UCProError::ERR_UNKNOWN;

		//	...
		$bKeepAlive	= boolval( $bKeepAlive );
		$arrEncryptedCk	= $this->GetEncryptedXTArray( $sCookieString );
		$nErrorId	= $this->_CheckEncryptedXTArray( $arrEncryptedCk );

		if ( UCProError::ERR_SUCCESS == $nErrorId )
		{
			$arrCookie = $this->_DecryptXTArray( $arrEncryptedCk );
			if ( CLib::IsArrayWithKeys( $arrCookie, UCProConst::CKT ) &&
				CLib::IsArrayWithKeys( $arrCookie[ UCProConst::CKT ], UCProConst::CKT_KP_ALIVE ) )
			{
				if ( UCProError::ERR_SUCCESS == $this->resetCookie( $sCookieString ) )
				{
					if ( $this->_setCookieForLogin( $arrEncryptedCk, $bKeepAlive ) )
					{
						$nRet = UCProError::ERR_SUCCESS;
					}
					else
					{
						$nRet = UCProError::ERR_SET_COOKIE;
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
		}
		else
		{
			$nRet = $nErrorId;
		}

		return $nRet;
	}


	protected function _CheckCookieString( $sCkString )
	{
		if ( ! CLib::IsExistingString( $sCkString ) )
		{
			return UCProError::ERR_PARAMETER;
		}
		return $this->_CheckEncryptedXTArray( $this->GetEncryptedXTArray( $sCkString ) );
	}

	protected function _CheckEncryptedXTArray( $arrEncryptedCookie )
	{
		if ( ! is_array( $arrEncryptedCookie ) ||
			! array_key_exists( UCProConst::CKX, $arrEncryptedCookie ) ||
			! array_key_exists( UCProConst::CKT, $arrEncryptedCookie ) )
		{
			return UCProError::ERR_INVALID_XT_COOKIE;
		}

		//	...
		$nRet = UCProError::ERR_UNKNOWN;

		if ( $this->isExistsXT( $arrEncryptedCookie ) )
		{
			if ( $this->isValidChecksumMd5( $arrEncryptedCookie ) )
			{
				if ( $this->isValidChecksumCrc( $arrEncryptedCookie ) )
				{
					$nRet = UCProError::ERR_SUCCESS;
				}
				else
				{
					//	invalid crc
					$nRet = UCProError::ERR_INVALID_CRC;
				}
			}
			else
			{
				//      invalid sign
				$nRet = UCProError::ERR_INVALID_SIGN;
			}
		}
		else
		{
			//      cookie is not exists
			$nRet = UCProError::ERR_BAD_COOKIE;
		}

		return $nRet;
	}


	protected function _ResetCookieByEncryptedXTArray( $arrEncryptedCk )
	{
		if ( ! $this->isValidXTArray( $arrEncryptedCk ) )
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

        protected function _GetSafeVal( $sKey, $arrData, $vDefault = null )
        {
                if ( ! CLib::IsArrayWithKeys( $arrData ) )
                {
                        return $vDefault;
                }
                if ( ! CLib::IsExistingString( $sKey ) )
                {
                        return $vDefault;
                }

                return array_key_exists( $sKey, $arrData ) ? $arrData[ $sKey ] : $vDefault;
        }








	//	if login info has timeout
	protected function _IsSessionTimeout()
	{
		//      ...
		$bRet = false;

		//      ...
		$bValidSession  = true;
		$cSession	= new UCSession();

		//
		//	Check session via service if T->CKT_REFRESH_TM is timeout
		//
		$nRefreshTime = $this->GetXTValue( UCProConst::CKT, UCProConst::CKT_REFRESH_TM );
		if ( time() - $nRefreshTime > 0 )
		{
			//	refresh time is timeout, it's time to check via Redis
			$bValidSession = $cSession->IsValidSession();
		}

		//      ...
		if ( $bValidSession )
		{
			if ( $this->IsKeepAlive() )
			{
				//
				//	return false if user set to keep alive
				//
				$bRet = false;
			}
			else
			{
				//	...
				$nLoginTime = $this->GetXTValue( UCProConst::CKT, UCProConst::CKT_LOGIN_TM );
				if ( is_numeric( $nLoginTime ) )
				{
					//
					//	escaped time in seconds after user logged in
					//      the default timeout is 1 day.
					//
					$fTerm	= floatval( time() - floatval( $nLoginTime ) );
					$bRet	= ( $fTerm > $this->m_arrCfg[ UCProConst::CFGKEY_STIMEOUT ] );
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

	protected function _EncryptXTArray( $arrData )
	{
		if ( ! $this->isValidXTArrayInDetail( $arrData ) )
		{
			return null;
		}

		//	...
		$arrX = Array
		(
			UCProConst::CKX_MID		=> $this->_GetSafeVal( UCProConst::CKX_MID, $arrData[ UCProConst::CKX ], '' ),
			UCProConst::CKX_NICKNAME	=> $this->_GetSafeVal( UCProConst::CKX_NICKNAME, $arrData[ UCProConst::CKX ], '' ),
			UCProConst::CKX_TYPE		=> $this->_GetSafeVal( UCProConst::CKX_TYPE, $arrData[ UCProConst::CKX ], 0 ),
			UCProConst::CKX_AVATAR		=> $this->_GetSafeVal( UCProConst::CKX_AVATAR, $arrData[ UCProConst::CKX ], '' ),
			UCProConst::CKX_STATUS		=> $this->_GetSafeVal( UCProConst::CKX_STATUS, $arrData[ UCProConst::CKX ], 0 ),
			UCProConst::CKX_ACTION		=> $this->_GetSafeVal( UCProConst::CKX_ACTION, $arrData[ UCProConst::CKX ], 0 ),
			UCProConst::CKX_SRC		=> $this->_GetSafeVal( UCProConst::CKX_SRC, $arrData[ UCProConst::CKX ], '' ),
			UCProConst::CKX_DIGEST		=> $this->_GetSafeVal( UCProConst::CKX_DIGEST, $arrData[ UCProConst::CKX ], '' ),
		);
		$arrT = Array
		(
			UCProConst::CKT_VER		=> $this->_GetSafeVal( UCProConst::CKT_VER, $arrData[ UCProConst::CKT ], '' ),
			UCProConst::CKT_LOGIN_TM	=> $this->_GetSafeVal( UCProConst::CKT_LOGIN_TM, $arrData[ UCProConst::CKT ], 0 ),
			UCProConst::CKT_REFRESH_TM	=> $this->_GetSafeVal( UCProConst::CKT_REFRESH_TM, $arrData[ UCProConst::CKT ], 0 ),
			UCProConst::CKT_UPDATE_TM	=> $this->_GetSafeVal( UCProConst::CKT_UPDATE_TM, $arrData[ UCProConst::CKT ], 0 ),
			UCProConst::CKT_KP_ALIVE	=> $this->_GetSafeVal( UCProConst::CKT_KP_ALIVE, $arrData[ UCProConst::CKT ], 0 ),
			UCProConst::CKT_SS_MID		=> $this->_GetSafeVal( UCProConst::CKT_SS_MID, $arrData[ UCProConst::CKT ], '' ),
			UCProConst::CKT_CKS_SIGN	=> $this->_GetSafeVal( UCProConst::CKT_CKS_SIGN, $arrData[ UCProConst::CKT ], '' ),
			UCProConst::CKT_CKS_CRC		=> $this->_GetSafeVal( UCProConst::CKT_CKS_CRC, $arrData[ UCProConst::CKT ], '' ),
		);

		foreach ( $arrX as $sKey => $sVal )
		{
			$arrX[ $sKey ]	= $this->_Encrypt( $sVal );
		}
		foreach ( $arrT as $sKey => $sVal )
		{
			$arrT[ $sKey ]	= $this->_Encrypt( $sVal );
		}

		//	...
		return Array
		(
			UCProConst::CKX	=> $this->_BuildQuery( $arrX ),
			UCProConst::CKT	=> $this->_BuildQuery( $arrT ),
		);
	}

	protected function _DecryptXTArray( $arrData )
	{
		if ( ! $this->isValidXTArray( $arrData ) )
		{
			return null;
		}

		//      ...
		$sX	= rawurldecode( $this->_GetSafeVal( UCProConst::CKX, $arrData, '' ) );
		$sT	= rawurldecode( $this->_GetSafeVal( UCProConst::CKT, $arrData, '' ) );
		$arrX	= Array();
		$arrT	= Array();

		try
		{
			//      parse string to array
			parse_str( $sX, $arrPX );
			parse_str( $sT, $arrPT );

			if ( is_array( $arrPX ) && count( $arrPX ) &&
				is_array( $arrPT ) && count( $arrPT ) )
			{
				if ( $this->isValidXTArrayInDetail( Array( self::CKX => $arrPX, self::CKT => $arrPT ) ) )
				{
					$arrX = Array
					(
						UCProConst::CKX_MID		=> $this->_GetSafeVal( UCProConst::CKX_MID, $arrPX, '' ),
						UCProConst::CKX_NICKNAME	=> $this->_GetSafeVal( UCProConst::CKX_NICKNAME, $arrPX, '' ),
						UCProConst::CKX_TYPE		=> $this->_GetSafeVal( UCProConst::CKX_TYPE, $arrPX, 0 ),
						UCProConst::CKX_AVATAR		=> $this->_GetSafeVal( UCProConst::CKX_AVATAR, $arrPX, '' ),
						UCProConst::CKX_STATUS		=> $this->_GetSafeVal( UCProConst::CKX_STATUS, $arrPX, 0 ),
						UCProConst::CKX_ACTION		=> $this->_GetSafeVal( UCProConst::CKX_ACTION, $arrPX, 0 ),
						UCProConst::CKX_SRC		=> $this->_GetSafeVal( UCProConst::CKX_SRC, $arrPX, '' ),
						UCProConst::CKX_DIGEST		=> $this->_GetSafeVal( UCProConst::CKX_DIGEST, $arrPX, '' ),
					);
					$arrT = Array
					(
						UCProConst::CKT_VER		=> $this->_GetSafeVal( UCProConst::CKT_VER, $arrPT, '' ),
						UCProConst::CKT_LOGIN_TM	=> $this->_GetSafeVal( UCProConst::CKT_LOGIN_TM, $arrPT, 0 ),
						UCProConst::CKT_REFRESH_TM	=> $this->_GetSafeVal( UCProConst::CKT_REFRESH_TM, $arrPT, 0 ),
						UCProConst::CKT_UPDATE_TM	=> $this->_GetSafeVal( UCProConst::CKT_UPDATE_TM, $arrPT, 0 ),
						UCProConst::CKT_KP_ALIVE	=> $this->_GetSafeVal( UCProConst::CKT_KP_ALIVE, $arrPT, 0 ),
						UCProConst::CKT_SS_MID		=> $this->_GetSafeVal( UCProConst::CKT_SS_MID, $arrPT, '' ),
						UCProConst::CKT_CKS_SIGN	=> $this->_GetSafeVal( UCProConst::CKT_CKS_SIGN, $arrPT, '' ),
						UCProConst::CKT_CKS_CRC		=> $this->_GetSafeVal( UCProConst::CKT_CKS_CRC, $arrPT, 0 ),
					);

					unset( $arrPX );
					unset( $arrPT );

					foreach ( $arrX as $sKey => $sVal )
					{
						$arrX[ $sKey ]	= $this->_Decrypt( $sVal );
					}
					foreach ( $arrT as $sKey => $sVal )
					{
						$arrT[ $sKey ]	= $this->_Decrypt( $sVal );
					}

					//
					//      type converting
					//
					$arrX[ UCProConst::CKX_TYPE ]		= intval( $arrX[ UCProConst::CKX_TYPE ] );
					$arrX[ UCProConst::CKX_STATUS ]		= intval( $arrX[ UCProConst::CKX_STATUS ] );
					$arrX[ UCProConst::CKX_ACTION ]		= intval( $arrX[ UCProConst::CKX_ACTION ] );

					$arrT[ UCProConst::CKT_LOGIN_TM ]	= intval( $arrT[ UCProConst::CKT_LOGIN_TM ] );
					$arrT[ UCProConst::CKT_REFRESH_TM ]	= intval( $arrT[ UCProConst::CKT_REFRESH_TM ] );
					$arrT[ UCProConst::CKT_UPDATE_TM ]	= intval( $arrT[ UCProConst::CKT_UPDATE_TM ] );
					$arrT[ UCProConst::CKT_KP_ALIVE ]	= intval( $arrT[ UCProConst::CKT_KP_ALIVE ] );
					$arrT[ UCProConst::CKT_CKS_CRC ]	= intval( $arrT[ UCProConst::CKT_CKS_CRC ] );
				}
			}
		}
		catch ( \Exception $e )
		{
		}

		//	...
		return Array
		(
			UCProConst::CKX	=> $arrX,
			UCProConst::CKT	=> $arrT,
		);
	}



        protected function _setCookieForLogin( $arrCookie, $bKeepAlive, & $sCkString = '' )
        {
                if ( ! CLib::IsArrayWithKeys( $arrCookie, [ UCProConst::CKX, UCProConst::CKT ] ) ||
                        ! is_string( $arrCookie[ UCProConst::CKX ] ) ||
                        ! is_string( $arrCookie[ UCProConst::CKT ] ) )
                {
                        return false;
                }

                //
                //	set the expire date as 1 year.
                //	the browser will keep this cookie for 1 year.
                //
                $tmExpire = ( $bKeepAlive ? ( time() + UCProConst::CONFIG_TIME_SECONDS_YEAR ) : 0 );
                return $this->_setCookie( $arrCookie, $tmExpire, $sCkString );
        }
        protected function _setCookieForLogout()
        {
                $arrCookie	= Array( UCProConst::CKX => '', UCProConst::CKT => '' );
                $tmExpire	= time() - UCProConst::CONFIG_TIME_SECONDS_YEAR;
                return $this->_setCookie( $arrCookie, $tmExpire );
        }
        protected function _setCookie( $arrCookie, $tmExpire, & $sCkString = '' )
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
                $sDomain	= $this->_GetCookieDomain();
                $sPath		= $this->m_arrCfg[ UCProConst::CFGKEY_PATH ];
                $sXValue        = rawurlencode( $arrCookie[ UCProConst::CKX ] );
                $sTValue        = rawurlencode( $arrCookie[ UCProConst::CKT ] );
                $sCkString      = http_build_query( Array( UCProConst::CKX => $sXValue, UCProConst::CKT => $sTValue ), '', '; ' );

                //	...
                if ( $this->m_arrCfg[ UCProConst::CFGKEY_HTTPONLY ] && $this->_IsSupportedSetHttpOnly() )
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

        protected function _GetDigestSource( $arrData )
        {
		if ( ! $this->isValidXTArray( $arrData ) )
		{
			return '';
		}

                //
                //	prevent all of the following fields from tampering
                //
                $sRet = "" .
		strval( $this->_GetSafeVal( UCProConst::CKX_MID, $arrData[ UCProConst::CKX ], '' ) ) . "-" .
		strval( $this->_GetSafeVal( UCProConst::CKX_TYPE, $arrData[ UCProConst::CKX ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( UCProConst::CKX_STATUS, $arrData[ UCProConst::CKX ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( UCProConst::CKX_ACTION, $arrData[ UCProConst::CKX ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( UCProConst::CKX_SRC, $arrData[ UCProConst::CKX ], '' ) ) . "-" .
		strval( $this->_GetSafeVal( UCProConst::CKX_DIGEST, $arrData[ UCProConst::CKX ], '' ) ) . "-" .
		"---" .
		strval( $this->_GetSafeVal( UCProConst::CKT_VER, $arrData[ UCProConst::CKT ], '' ) ) . "-" .
		strval( $this->_GetSafeVal( UCProConst::CKT_LOGIN_TM, $arrData[ UCProConst::CKT ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( UCProConst::CKT_REFRESH_TM, $arrData[ UCProConst::CKT ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( UCProConst::CKT_UPDATE_TM, $arrData[ UCProConst::CKT ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( UCProConst::CKT_KP_ALIVE, $arrData[ UCProConst::CKT ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( UCProConst::CKT_SS_MID, $arrData[ UCProConst::CKT ], '' ) );

                //	...
                return $sRet;
        }

        protected function _BuildQuery( $arrParams )
        {
                $sRet		= '';
                $arrPairs       = Array();

                if ( is_array( $arrParams ) )
                {
                        foreach ( $arrParams as $key => $value )
                        {
                                $arrPairs[] = $key . '=' . $value;
                        }
                        $sRet = implode( '&', $arrPairs );
                }
                else
                {
                        $sRet = $arrParams;
                }

                return $sRet;
        }
        protected function _GetCookieDomain()
        {
                return ( array_key_exists( UCProConst::CFGKEY_DOMAIN, $this->m_arrCfg ) ? $this->m_arrCfg[ UCProConst::CFGKEY_DOMAIN ] : '' );
        }

        protected function _IsSupportedSetHttpOnly()
        {
                //	set http-only for cookie is supported
                return version_compare( phpversion(), '5.2.0', '>=' );
        }

}

?>