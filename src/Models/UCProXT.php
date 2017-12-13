<?php

namespace dekuan\deuclientpro;

use dekuan\delib\CLib;


/**
 *      class of UCProCookie
 * 
 * 	All operations/methods about XT Array
 *
 */
class UCProXT extends UCProBase
{
        public function __construct()
        {
        	parent::__construct();
        }
        public function __destruct()
        {
        	parent::__destruct();
        }

	

	public function encryptXTArray( $arrData )
	{
		if ( ! $this->isValidXTArrayInDetail( $arrData ) )
		{
			return null;
		}

		//	...
		$arrX = Array
		(
			UCProConst::CKX_MID		=> UCProLib::getSafeVal( UCProConst::CKX_MID, $arrData[ UCProConst::CKX ], '' ),
			UCProConst::CKX_NICKNAME	=> UCProLib::getSafeVal( UCProConst::CKX_NICKNAME, $arrData[ UCProConst::CKX ], '' ),
			UCProConst::CKX_TYPE		=> UCProLib::getSafeVal( UCProConst::CKX_TYPE, $arrData[ UCProConst::CKX ], 0 ),
			UCProConst::CKX_AVATAR		=> UCProLib::getSafeVal( UCProConst::CKX_AVATAR, $arrData[ UCProConst::CKX ], '' ),
			UCProConst::CKX_STATUS		=> UCProLib::getSafeVal( UCProConst::CKX_STATUS, $arrData[ UCProConst::CKX ], 0 ),
			UCProConst::CKX_ACTION		=> UCProLib::getSafeVal( UCProConst::CKX_ACTION, $arrData[ UCProConst::CKX ], 0 ),
			UCProConst::CKX_SRC		=> UCProLib::getSafeVal( UCProConst::CKX_SRC, $arrData[ UCProConst::CKX ], '' ),
			UCProConst::CKX_DIGEST		=> UCProLib::getSafeVal( UCProConst::CKX_DIGEST, $arrData[ UCProConst::CKX ], '' ),
		);
		$arrT = Array
		(
			UCProConst::CKT_VER		=> UCProLib::getSafeVal( UCProConst::CKT_VER, $arrData[ UCProConst::CKT ], '' ),
			UCProConst::CKT_LOGIN_TM	=> UCProLib::getSafeVal( UCProConst::CKT_LOGIN_TM, $arrData[ UCProConst::CKT ], 0 ),
			UCProConst::CKT_REFRESH_TM	=> UCProLib::getSafeVal( UCProConst::CKT_REFRESH_TM, $arrData[ UCProConst::CKT ], 0 ),
			UCProConst::CKT_UPDATE_TM	=> UCProLib::getSafeVal( UCProConst::CKT_UPDATE_TM, $arrData[ UCProConst::CKT ], 0 ),
			UCProConst::CKT_KP_ALIVE	=> UCProLib::getSafeVal( UCProConst::CKT_KP_ALIVE, $arrData[ UCProConst::CKT ], 0 ),
			UCProConst::CKT_SS_MID		=> UCProLib::getSafeVal( UCProConst::CKT_SS_MID, $arrData[ UCProConst::CKT ], '' ),
			UCProConst::CKT_CKS_MD5		=> UCProLib::getSafeVal( UCProConst::CKT_CKS_MD5, $arrData[ UCProConst::CKT ], '' ),
			UCProConst::CKT_CKS_CRC		=> UCProLib::getSafeVal( UCProConst::CKT_CKS_CRC, $arrData[ UCProConst::CKT ], '' ),
		);

		foreach ( $arrX as $sKey => $sVal )
		{
			$arrX[ $sKey ]	= UCProEncrypt::encrypt( $sVal );
		}
		foreach ( $arrT as $sKey => $sVal )
		{
			$arrT[ $sKey ]	= UCProEncrypt::encrypt( $sVal );
		}

		//	...
		return Array
		(
			UCProConst::CKX	=> UCProLib::buildQueryString( $arrX ),
			UCProConst::CKT	=> UCProLib::buildQueryString( $arrT ),
		);
	}

	public function decryptXTArray( $arrData )
	{
		if ( ! CLib::IsArrayWithKeys( $arrData, [ UCProConst::CKX, UCProConst::CKT ] ) )
		{
			return null;
		}

		//      ...
		$sX	= rawurldecode( UCProLib::getSafeVal( UCProConst::CKX, $arrData, '' ) );
		$sT	= rawurldecode( UCProLib::getSafeVal( UCProConst::CKT, $arrData, '' ) );
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
				if ( $this->isValidXTArrayInDetail( Array( UCProConst::CKX => $arrPX, UCProConst::CKT => $arrPT ) ) )
				{
					$arrX = Array
					(
						UCProConst::CKX_MID		=> UCProLib::getSafeVal( UCProConst::CKX_MID, $arrPX, '' ),
						UCProConst::CKX_NICKNAME	=> UCProLib::getSafeVal( UCProConst::CKX_NICKNAME, $arrPX, '' ),
						UCProConst::CKX_TYPE		=> UCProLib::getSafeVal( UCProConst::CKX_TYPE, $arrPX, 0 ),
						UCProConst::CKX_AVATAR		=> UCProLib::getSafeVal( UCProConst::CKX_AVATAR, $arrPX, '' ),
						UCProConst::CKX_STATUS		=> UCProLib::getSafeVal( UCProConst::CKX_STATUS, $arrPX, 0 ),
						UCProConst::CKX_ACTION		=> UCProLib::getSafeVal( UCProConst::CKX_ACTION, $arrPX, 0 ),
						UCProConst::CKX_SRC		=> UCProLib::getSafeVal( UCProConst::CKX_SRC, $arrPX, '' ),
						UCProConst::CKX_DIGEST		=> UCProLib::getSafeVal( UCProConst::CKX_DIGEST, $arrPX, '' ),
					);
					$arrT = Array
					(
						UCProConst::CKT_VER		=> UCProLib::getSafeVal( UCProConst::CKT_VER, $arrPT, '' ),
						UCProConst::CKT_LOGIN_TM	=> UCProLib::getSafeVal( UCProConst::CKT_LOGIN_TM, $arrPT, 0 ),
						UCProConst::CKT_REFRESH_TM	=> UCProLib::getSafeVal( UCProConst::CKT_REFRESH_TM, $arrPT, 0 ),
						UCProConst::CKT_UPDATE_TM	=> UCProLib::getSafeVal( UCProConst::CKT_UPDATE_TM, $arrPT, 0 ),
						UCProConst::CKT_KP_ALIVE	=> UCProLib::getSafeVal( UCProConst::CKT_KP_ALIVE, $arrPT, 0 ),
						UCProConst::CKT_SS_MID		=> UCProLib::getSafeVal( UCProConst::CKT_SS_MID, $arrPT, '' ),
						UCProConst::CKT_CKS_MD5		=> UCProLib::getSafeVal( UCProConst::CKT_CKS_MD5, $arrPT, '' ),
						UCProConst::CKT_CKS_CRC		=> UCProLib::getSafeVal( UCProConst::CKT_CKS_CRC, $arrPT, 0 ),
					);

					unset( $arrPX );
					unset( $arrPT );

					foreach ( $arrX as $sKey => $sVal )
					{
						$arrX[ $sKey ]	= UCProEncrypt::decrypt( $sVal );
					}
					foreach ( $arrT as $sKey => $sVal )
					{
						$arrT[ $sKey ]	= UCProEncrypt::decrypt( $sVal );
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

        
        
        
        
        
        
}

