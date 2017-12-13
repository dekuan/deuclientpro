<?php

namespace dekuan\deuclientpro;

use dekuan\delib\CLib;


/**
 *      class of UCProConfig
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
			UCProConst::CFGKEY_DOMAIN	=> UCProConst::DEFAULT_DOMAIN,
			UCProConst::CFGKEY_PATH		=> UCProConst::DEFAULT_PATH,
			UCProConst::CFGKEY_SEED		=> UCProConst::DEFAULT_SIGN_SEED,	//	seed
			UCProConst::CFGKEY_SECURE	=> UCProConst::DEFAULT_SECURE,
			UCProConst::CFGKEY_HTTPONLY	=> UCProConst::DEFAULT_HTTPONLY,
			UCProConst::CFGKEY_SS_TIMEOUT	=> UCProConst::DEFAULT_SS_TIMEOUT,	//	session timeout, default is 1 day.
		];
        }
        public function __destruct()
        {
        }


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
	public function cloneConfig( $arrCfg )
	{
		$this->m_arrCfg = $arrCfg;
	}
	
	
	
	
	public function getConfig_SessionTimeout()
	{
		return UCProLib::getSafeVal( UCProConst::CFGKEY_SS_TIMEOUT, $this->m_arrCfg, UCProConst::DEFAULT_SS_TIMEOUT );
	}

}

