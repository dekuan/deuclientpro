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
	protected $m_cUCProCookie	= null;
	
	

        public function __construct()
        {
        	parent::__construct();

		//	...
		$this->m_cUCProXT	= new UCProXT();
		$this->m_cUCProCookie	= new UCProCookie();
        }
        public function __destruct()
        {
        	parent::__destruct();
        }

	public function cloneConfig( $arrCfg )
	{
		$this->m_cUCProXT->cloneConfig( $arrCfg );
		$this->m_cUCProCookie->cloneConfig( $arrCfg );
	}

	public function getXTInstance()
	{
		return $this->m_cUCProXT;
	}
	public function getCookieInstance()
	{
		return $this->m_cUCProCookie;
	}

	public function makeLoginByXTArray( $arrData, $bKeepAlive = false, & $sCkString = '' )
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
		$arrData[ UCProConst::CKT ][ UCProConst::CKT_CKS_MD5 ]	= $this->getChecksumMd5( $arrData );
		$arrData[ UCProConst::CKT ][ UCProConst::CKT_CKS_CRC ]	= $this->getChecksumCrc( $arrData );

		//	...
		$arrEncryptedCk = $this->m_cUCProMain->getXTInstance()->encryptXTArray( $arrData );
		if ( CLib::IsArrayWithKeys( $arrEncryptedCk, [ UCProConst::CKX, UCProConst::CKT ] ) )
		{
			if ( $this->m_cUCProMain->getCookieInstance()->setCookiesForLogin( $arrEncryptedCk, $bKeepAlive, $sCkString ) )
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
	public function makeLoginByCookieString( $sCookieString, $bKeepAlive = false, $bResponseCookie = true )
	{
		if ( ! CLib::IsExistingString( $sCookieString ) )
		{
			return UCProError::ERR_PARAMETER;
		}

		//	...
		$nRet		= UCProError::ERR_UNKNOWN;
		$bKeepAlive	= boolval( $bKeepAlive );
		$arrEncryptedXT	= $this->m_cUCProMain->getCookieInstance()->parseCookieString( $sCookieString );
		if ( $this->m_cUCProMain->getCookieInstance()->isValidCookieArray( $arrEncryptedXT ) )
		{
			if ( UCProError::ERR_SUCCESS ==
				$this->m_cUCProMain->getCookieInstance()->memsetCookieByCookieArray( $arrEncryptedXT ) )
			{
				if ( $bResponseCookie )
				{
					//
					//	response cookie via HTTP header to the client
					//
					if ( $this->m_cUCProMain->getCookieInstance()->setCookiesForLogin( $arrEncryptedXT, $bKeepAlive ) )
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
	
	
	public function checkXT()
	{
		$nRet = UCProError::ERR_UNKNOWN;

		//	...
		if ( $this->m_cUCProCookie->isExistsCookie() )
		{
			if ( $this->isValidChecksumMd5( $this->m_cUCProXT->decryptXTArray( $this->m_cUCProCookie->getCookieArray() ) ) )
			{
				if ( $this->isValidChecksumCrc( $this->m_cUCProXT->decryptXTArray( $this->m_cUCProCookie->getCookieArray() ) ) )
				{
					$nRet = UCProError::ERR_SUCCESS;
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

		//	...
		return $nRet;
	}


	//	build signature
	public function getChecksumMd5( $arrData )
	{
		if ( ! UCProLib::isValidXTArray( $arrData ) )
		{
			return '';
		}

		$sRet		= '';
		$sString	= $this->_GetDigestSource( $arrData );
		if ( CLib::IsExistingString( $sString ) )
		{
			$sData	= ( md5( $sString ) . "-" . $sString . "-" . $this->m_arrCfg[ UCProConst::CFGKEY_SEED ] );
			$sData	= mb_strtolower( $sData );
			$sRet	= md5( $sData );
		}

		//	...
		return $sRet;
	}

	//      build crc checksum
	public function getChecksumCrc( $arrData )
	{
		if ( ! UCProLib::isValidXTArray( $arrData ) )
		{
			return '';
		}

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

	//	check signature
	public function isValidChecksumMd5( $arrXTArray )
	{
		$bRet	= false;

		if ( UCProLib::isValidXTArray( $arrXTArray ) )
		{
			$sSignDataNow	= $this->getChecksumMd5( $arrXTArray );
			$sSignDataCk	= UCProLib::getSafeVal( UCProConst::CKT_CKS_MD5, $arrXTArray[ UCProConst::CKT ], '' );
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
	public function isValidChecksumCrc( $arrXTArray )
	{
		//	...
		$bRet	= false;

		if ( UCProLib::isValidXTArray( $arrXTArray ) )
		{
			$nCRCDataNow	= $this->getChecksumCrc( $arrXTArray );
			$nCRCDataCk	= UCProLib::getSafeVal( UCProConst::CKT_CKS_CRC, $arrXTArray[ UCProConst::CKT ], 0 );

			if ( is_numeric( $nCRCDataNow ) &&
				is_numeric( $nCRCDataCk ) &&
				$nCRCDataNow === $nCRCDataCk )
			{
				$bRet = true;
			}
		}

		return $bRet;
	}






	protected function _GetDigestSource( $arrData )
	{
		if ( ! UCProLib::isValidXTArray( $arrData ) )
		{
			return '';
		}

		//
		//	prevent all of the following fields from tampering
		//
		$sRet = "" .
		strval( UCProLib::getSafeVal( UCProConst::CKX_MID, $arrData[ UCProConst::CKX ], '' ) ) . "-" .
		strval( UCProLib::getSafeVal( UCProConst::CKX_TYPE, $arrData[ UCProConst::CKX ], 0 ) ) . "-" .
		strval( UCProLib::getSafeVal( UCProConst::CKX_STATUS, $arrData[ UCProConst::CKX ], 0 ) ) . "-" .
		strval( UCProLib::getSafeVal( UCProConst::CKX_ACTION, $arrData[ UCProConst::CKX ], 0 ) ) . "-" .
		strval( UCProLib::getSafeVal( UCProConst::CKX_SRC, $arrData[ UCProConst::CKX ], '' ) ) . "-" .
		strval( UCProLib::getSafeVal( UCProConst::CKX_DIGEST, $arrData[ UCProConst::CKX ], '' ) ) . "-" .
		"---" .
		strval( UCProLib::getSafeVal( UCProConst::CKT_VER, $arrData[ UCProConst::CKT ], '' ) ) . "-" .
		strval( UCProLib::getSafeVal( UCProConst::CKT_LOGIN_TM, $arrData[ UCProConst::CKT ], 0 ) ) . "-" .
		strval( UCProLib::getSafeVal( UCProConst::CKT_REFRESH_TM, $arrData[ UCProConst::CKT ], 0 ) ) . "-" .
		strval( UCProLib::getSafeVal( UCProConst::CKT_UPDATE_TM, $arrData[ UCProConst::CKT ], 0 ) ) . "-" .
		strval( UCProLib::getSafeVal( UCProConst::CKT_KP_ALIVE, $arrData[ UCProConst::CKT ], 0 ) ) . "-" .
		strval( UCProLib::getSafeVal( UCProConst::CKT_SS_MID, $arrData[ UCProConst::CKT ], '' ) );

		//	...
		return $sRet;
	}
}

