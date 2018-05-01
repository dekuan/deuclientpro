<?php

namespace dekuan\deuclientpro;


use dekuan\delib\CLib;



/**
 * Created by PhpStorm.
 * User: xing
 * Date: 17/02/2017
 * Time: 12:49 AM
 */
class UClientProTest extends \PHPUnit\Framework\TestCase
{
	const CONST_SEED		= '5adf23adb-8815-46ea-ees1f-198sdcsf380f04/dfdfdfdfdf-5af5c-1234f-88acd-1121134234343434343';
	const CONST_COOKIE_STRING	= 'X=mid%253D137954853350540966%2526nkn%253D%2525R5%2525O0%25258S%2525R6%252598%25259S%2525R6%252598%25259S%2526t%253D0%2526avatar%253D137954853350540966%2526sts%253D1%2526act%253D0%2526src%253DJRPUNG%2526digest%253Dqvtrfg; T=v%253D1.0.1.1000%2526ltm%253D1513337630%2526rtm%253D1513424030%2526utm%253D1513424030%2526kpa%253D1%2526ssid%253D129018269989342999%2526csm%253D7o1759243n59rn9106nos9os7s9pn39o%2526csc%253D387498538';
	
	
	/**
	 * @runInSeparateProcess
	 */
	public function testMakeLoginByXTArray()
	{
		$cUClientPro	= new UClientPro();

		//	...
		$nLoginTime	= time();
		$nRefreshTime	= $nLoginTime + 60 * 60 * 24;
		$nUpdateTime	= $nRefreshTime;
		$arrData	= Array
		(
			UCProConst::CKX => Array
			(
				UCProConst::CKX_MID		=> 137954853350540966,
				UCProConst::CKX_NICKNAME	=> '小星星',
				UCProConst::CKX_TYPE		=> 0,
				UCProConst::CKX_AVATAR		=> 137954853350540966,
				UCProConst::CKX_STATUS		=> UCProConst::STATUS_OKAY,
				UCProConst::CKX_ACTION		=> 0,
				UCProConst::CKX_SRC		=> 'WECHAT',
				UCProConst::CKX_DIGEST		=> 'digest',
			),
			UCProConst::CKT => Array
			(
				UCProConst::CKT_LOGIN_TM	=> $nLoginTime,
				UCProConst::CKT_REFRESH_TM	=> $nRefreshTime,
				UCProConst::CKT_UPDATE_TM	=> $nUpdateTime,
				UCProConst::CKT_KP_ALIVE	=> 1,
				UCProConst::CKT_SS_ID		=> 129018269989342999,
			),
		);

		$arrDump = Array
		(
			'X' => Array
			(
				'mid' => 137954853350540966,
				'nkn' => '小星星',
				't' => 0,
				'avatar' => 137954853350540966,
				'sts' => 1,
				'act' => 0,
				'src' => 'WECHAT',
				'digest' => 'digest',
			),
			'T' => Array
			(
				'v' => '1.0.1.1000',
				'ltm' => 1513334152,
				'rtm' => 1513420552,
				'utm' => 1513420552,
				'kpa' => 1,
				'ssid' => 129018269989342999,
				'csm' => '3335aeb3ed625b1da40ec5cf0aaa2f14',
				'csc' => 2079964156,
			)
		);

		$cUClientPro->setConfig( UCProConst::CONFIG_COOKIE_DOMAIN, '.xs.cn' );
		$cUClientPro->setConfig( UCProConst::CONFIG_COOKIE_SEED, self::CONST_SEED );

		$sCkString      = '';
		$nErrorId       = $cUClientPro->makeLogin( $arrData, true, $sCkString );

		return new CAssertResult( __CLASS__,__FUNCTION__, 'logout', $nErrorId );
	}


	/**
	 * @runInSeparateProcess
	 */
	public function testMakeLoginByCookieString()
	{
		$cUClientPro	= new UClientPro();

		//	...
		$cUClientPro->setConfig( UCProConst::CONFIG_COOKIE_DOMAIN, '.xs.cn' );
		$cUClientPro->setConfig( UCProConst::CONFIG_COOKIE_SEED, self::CONST_SEED );

		$sCookieString	= self::CONST_COOKIE_STRING;
		$nErrorId       = $cUClientPro->makeLogin( $sCookieString, true );

		return new CAssertResult( __CLASS__,__FUNCTION__, 'makeLogin', $nErrorId );
	}


	/**
	 * @runInSeparateProcess
	 */
	public function testLogout()
	{
		//
		//	...
		//
		$cUClientPro	= new UClientPro();

		//	...
		$cUClientPro->setConfig( UCProConst::CONFIG_COOKIE_DOMAIN, '.xs.cn' );
		$cUClientPro->setConfig( UCProConst::CONFIG_COOKIE_SEED, self::CONST_SEED );
		$nErrorId       = $cUClientPro->logout();
		
		return new CAssertResult( __CLASS__,__FUNCTION__, 'logout', $nErrorId );
	}


	/**
	 * @runInSeparateProcess
	 */
	public function testCheckLogin()
	{
		//
		//	build cookie
		//
		global $_COOKIE;

		if ( ! is_array( $_COOKIE ) )
		{
			$_COOKIE = [];
		}

		$sCookieString	= self::CONST_COOKIE_STRING;
		$arrCookie	= [];
		@ parse_str( @ str_replace( '; ', '&', $sCookieString ), $arrCookie );
		if ( CLib::IsArrayWithKeys( $arrCookie, [ UCProConst::CKX, UCProConst::CKT ] ) )
		{
			//
			//	make cookie
			//
			$_COOKIE[ UCProConst::CKX ]	= $arrCookie[ UCProConst::CKX ];
			$_COOKIE[ UCProConst::CKT ]	= $arrCookie[ UCProConst::CKT ];
		}

		//
		//	...
		//
		$cUClientPro	= new UClientPro();
		$cUClientPro->setConfig( UCProConst::CONFIG_COOKIE_DOMAIN, '.xs.cn' );
		$cUClientPro->setConfig( UCProConst::CONFIG_COOKIE_SEED, self::CONST_SEED );

		//	...
		$nErrorId	= $cUClientPro->checkLogin();
		$arrXT		= $cUClientPro->getXTArray();

		return new CAssertResult( __CLASS__,__FUNCTION__, 'checkLogin', $nErrorId );
	}
	
	
	
	
}
