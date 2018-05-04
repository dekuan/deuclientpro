<?php

namespace dekuan\deuclientpro\Models;

use dekuan\delib\CLib;
use dekuan\deuclientpro\Libs\UCProLib;
use dekuan\deuclientpro\UCProConst;



/**
 *	Class UCProConfig
 *	@package dekuan\deuclientpro\Models
 */
class UCProConfig
{
	//
	//	configuration
	//
	protected $m_arrCfg	= [];



        public function __construct()
        {
		$this->m_arrCfg	=
		[
			UCProConst::CONFIG_COOKIE_DOMAIN	=> UCProConst::DEFAULT_DOMAIN,
			UCProConst::CONFIG_COOKIE_PATH		=> UCProConst::DEFAULT_PATH,
			UCProConst::CONFIG_COOKIE_SEED		=> UCProConst::DEFAULT_SIGN_SEED,	//	seed
			UCProConst::CONFIG_SECURE		=> UCProConst::DEFAULT_SECURE,
			UCProConst::CONFIG_HTTP_ONLY		=> UCProConst::DEFAULT_HTTP_ONLY,
			UCProConst::CONFIG_COOKIE_TIMEOUT	=> UCProConst::DEFAULT_COOKIE_TIMEOUT,	//	session timeout, default is 1 day.
		];
        }
        public function __destruct()
        {
        }
	
	
	/**
	 *	@param	null $sKey
	 *	@return	array|mixed
	 */
	public function getConfig( $sKey = null )
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
	
	/**
	 *	@param	$sKey
	 *	@param	$vValue
	 *	@return bool
	 */
	public function setConfig( $sKey, $vValue )
	{
		if ( CLib::IsExistingString( $sKey ) &&
			array_key_exists( $sKey, $this->m_arrCfg ) )
		{
			//	...
			$this->m_arrCfg[ $sKey ] = $vValue;
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 *	@param $arrCfg
	 */
	public function cloneConfig( $arrCfg )
	{
		$this->m_arrCfg = $arrCfg;
	}

	
	

	public function getConfig_nCookieTimeout()
	{
		return intval( UCProLib::getSafeVal( UCProConst::CONFIG_COOKIE_TIMEOUT, $this->m_arrCfg, UCProConst::DEFAULT_COOKIE_TIMEOUT ) );
	}

	public function getConfig_bHttpOnly()
	{
		return boolval( UCProLib::getSafeVal( UCProConst::CONFIG_HTTP_ONLY, $this->m_arrCfg, UCProConst::DEFAULT_HTTP_ONLY ) );
	}

	public function getConfig_bSecure()
	{
		return boolval( UCProLib::getSafeVal( UCProConst::CONFIG_SECURE, $this->m_arrCfg, UCProConst::DEFAULT_SECURE ) );
	}

	public function getConfig_sCookieDomain()
	{
		return strval( UCProLib::getSafeVal( UCProConst::CONFIG_COOKIE_DOMAIN, $this->m_arrCfg, '' ) );
	}
	

	
	
}

