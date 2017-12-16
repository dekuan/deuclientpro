<?php

namespace dekuan\deuclientpro\Libs;

use dekuan\delib\CLib;
use dekuan\deuclientpro\UCProConst;


/**
 *      class of UCProLib
 */
class UCProLib
{
	static function isPhpServerEnv()
	{
		return CLib::IsArrayWithKeys( $_SERVER, [ 'HTTP_USER_AGENT', 'HTTP_HOST', 'SERVER_NAME' ] );
	}

	static function getSafeVal( $sKey, $arrData, $vDefault = null )
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

	static function buildQueryString( $arrParams )
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


	//
	//	check if XT array is valid
	//
	static function isValidXTArray( $arrCk )
	{
		return ( CLib::IsArrayWithKeys( $arrCk, [ UCProConst::CKX, UCProConst::CKT ] ) &&
			is_array( $arrCk[ UCProConst::CKX ] ) &&
			is_array( $arrCk[ UCProConst::CKT ] ) );
	}

	//
	//	check if XT array is valid in detail
	//
	static function isValidXTArrayInDetail( $arrCk = null )
	{
		$bRet = false;

		if ( self::isValidXTArray( $arrCk ) )
		{
			if ( count( $arrCk[ UCProConst::CKX ] ) && count( $arrCk[ UCProConst::CKT ] ) )
			{
				if ( CLib::IsArrayWithKeys( $arrCk[ UCProConst::CKX ],
					[
						UCProConst::CKX_MID,
						UCProConst::CKX_TYPE,
						UCProConst::CKX_STATUS,
						UCProConst::CKX_ACTION
					] ) )
				{
					if ( CLib::IsArrayWithKeys( $arrCk[ UCProConst::CKT ],
						[
							UCProConst::CKT_VER,
							UCProConst::CKT_LOGIN_TM,
							UCProConst::CKT_REFRESH_TM,
							UCProConst::CKT_UPDATE_TM,
							UCProConst::CKT_KP_ALIVE
						] ) )
					{
						$bRet = true;
					}
				}
			}
		}

		return $bRet;
	}
}

