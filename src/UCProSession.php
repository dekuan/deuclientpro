<?php

namespace dekuan\deuclientpro;

use dekuan\delib\CLib;
use dekuan\vdata\CConst;
use dekuan\vdata\CRequest;
use dekuan\vdata\CVData;


/**
 *	Class UCProSession
 *	@package dekuan\deuclientpro
 */
class UCProSession
{
	/**
	 *	statics
	 *	@var
	 */
        protected static $g_cStaticInstance;



        public function __construct()
        {
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


	/**
	 *	check session from remote server
	 *	@param	object	$objParam
	 *	@param	int	$nCheckReturn
	 *	@return	bool
	 */
	public function checkSessionByRPC( $objParam, & $nCheckReturn = null )
	{
		if ( ! CLib::IsObjectWithProperties( $objParam,
			[
				UCProConst::CKX_MID,
				UCProConst::CKT_SS_ID,
				UCProConst::CKT_SS_URL,
				'cookie_array'
			] ) )
		{
			return UCProError::UCPROSESSION_CHECKSESSIONBYRPC_PARAM;
		}

		//	...
		$nRet		= UCProError::UCPROSESSION_CHECKSESSIONBYRPC_FAILURE;
		$nCheckReturn	= CConst::ERROR_UNKNOWN;

		//	...
		$sMId		= $objParam->{ UCProConst::CKX_MID };
		$sSessionId	= $objParam->{ UCProConst::CKT_SS_ID };
		$sSessionUrl	= $objParam->{ UCProConst::CKT_SS_URL };
		$arrCookie	= $objParam->{ 'cookie_array' };

		if ( ! CLib::IsExistingString( $sMId, true ) )
		{
			return UCProError::UCPROSESSION_CHECKSESSIONBYRPC_PARAM_MID;
		}
		if ( ! CLib::IsExistingString( $sSessionId, true ) )
		{
			return UCProError::UCPROSESSION_CHECKSESSIONBYRPC_PARAM_SS_ID;
		}
		if ( ! CLib::IsExistingString( $sSessionUrl, true ) )
		{
			return UCProError::UCPROSESSION_CHECKSESSIONBYRPC_PARAM_SS_URL;
		}
		if ( false === filter_var( $sSessionUrl, FILTER_VALIDATE_URL ) )
		{
			return UCProError::UCPROSESSION_CHECKSESSIONBYRPC_PARAM_SS_URL2;
		}

		//	...
		$nTimeout	= 5;
		$cRequest	= CRequest::GetInstance();
		$arrResponse	= [];
		$nCall		= $cRequest->Get
		(
			[
				'url'		=> $sSessionUrl,
				'data'		=> [
					UCProConst::CKX_MID	=> $sMId,
					UCProConst::CKT_SS_ID	=> $sSessionId,
				],
				'version'	=> '1.0',		//	required version of service
				'timeout'	=> $nTimeout,		//	timeout in seconds
				'cookie'	=> $arrCookie,
			],
			$arrResponse
		);
		if ( CConst::ERROR_SUCCESS == $nCall )
		{
			if ( CVData::GetInstance()->IsValidVData( $arrResponse ) )
			{
				//	...
				$nRet		= UCProError::SUCCESS;
				$nCheckReturn	= $arrResponse[ 'errorid' ];
			}
			else
			{
				$nRet	= UCProError::UCPROSESSION_CHECKSESSIONBYRPC_INVALID_VDATA;
			}
		}
		else
		{
			$nRet	= $nCall;
		}

		//	...
		return $nRet;
	}



}