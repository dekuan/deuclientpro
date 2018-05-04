<?php

namespace dekuan\deuclientpro\Models;

use dekuan\delib\CLib;
use dekuan\deuclientpro\Libs\UCProLib;
use dekuan\deuclientpro\UCProConst;


/**
 *      class of UCProChecksum
 *
 */
class UCProChecksum extends UCProBase
{
        public function __construct()
        {
        	parent::__construct();
        }
        public function __destruct()
        {
        	parent::__destruct();
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
			$sData	= ( md5( $sString ) . "-" . $sString . "-" . $this->m_arrCfg[ UCProConst::CONFIG_COOKIE_SEED ] );
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
			$sData	= mb_strtolower( trim( $sString . "-" . $this->m_arrCfg[ UCProConst::CONFIG_COOKIE_SEED ] ) );
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



	////////////////////////////////////////////////////////////////////////////////
	//	protected
	//


	protected function _GetDigestSource( $arrData )
	{
		if ( ! UCProLib::isValidXTArray( $arrData ) )
		{
			return '';
		}

		//
		//	prevent all of the following fields from tampering
		//
		$arrSourceList	=
			[
				strval( UCProLib::getSafeVal( UCProConst::CKX_MID, $arrData[ UCProConst::CKX ], '' ) ),
				strval( UCProLib::getSafeVal( UCProConst::CKX_TYPE, $arrData[ UCProConst::CKX ], 0 ) ),
				strval( UCProLib::getSafeVal( UCProConst::CKX_STATUS, $arrData[ UCProConst::CKX ], 0 ) ),
				strval( UCProLib::getSafeVal( UCProConst::CKX_ACTION, $arrData[ UCProConst::CKX ], 0 ) ),
				strval( UCProLib::getSafeVal( UCProConst::CKX_SRC, $arrData[ UCProConst::CKX ], '' ) ),
				strval( UCProLib::getSafeVal( UCProConst::CKX_DIGEST, $arrData[ UCProConst::CKX ], '' ) ),
				strval( UCProLib::getSafeVal( UCProConst::CKT_VER, $arrData[ UCProConst::CKT ], '' ) ),
				strval( UCProLib::getSafeVal( UCProConst::CKT_LOGIN_TM, $arrData[ UCProConst::CKT ], 0 ) ),
				strval( UCProLib::getSafeVal( UCProConst::CKT_REFRESH_TM, $arrData[ UCProConst::CKT ], 0 ) ),
				strval( UCProLib::getSafeVal( UCProConst::CKT_UPDATE_TM, $arrData[ UCProConst::CKT ], 0 ) ),
				strval( UCProLib::getSafeVal( UCProConst::CKT_KP_ALIVE, $arrData[ UCProConst::CKT ], 0 ) ),
				strval( UCProLib::getSafeVal( UCProConst::CKT_SS_ID, $arrData[ UCProConst::CKT ], '' ) ),
				strval( UCProLib::getSafeVal( UCProConst::CKT_SS_URL, $arrData[ UCProConst::CKT ], '' ) ),
			];

		//	...
		return implode( '--', $arrSourceList );
	}
}

