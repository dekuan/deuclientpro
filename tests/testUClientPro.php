<?php

namespace dekuan\deuclientpro;


/**
 * Created by PhpStorm.
 * User: xing
 * Date: 17/02/2017
 * Time: 12:49 AM
 */
class testUClientPro extends \PHPUnit\Framework\TestCase
{
	const CONST_SEED	= '5adf23adb-8815-46ea-ees1f-198sdcsf380f04/dfdfdfdfdf-5af5c-1234f-88acd-1121134234343434343';
	

	public function testMakeLogin()
	{
		$cUClientPro	= UClientPro::getInstance();

		//	...
		$nLoginTime	= time();
		$nRefreshTime	= $nLoginTime + 60 * 60 * 24;
		$nUpdateTime	= $nRefreshTime;
		$arrData	= Array
		(
			UCProConst::CKX => Array
			(
				UCProConst::CKX_MID		=> '1011301016111816483435812320',
				UCProConst::CKX_NICKNAME	=> '李小龙',
				UCProConst::CKX_TYPE		=> 0,
				UCProConst::CKX_AVATAR		=> '159588ac912e08093c37b5064930e6064',
				UCProConst::CKX_STATUS		=> UCProConst::STATUS_OKAY,
				UCProConst::CKX_ACTION		=> 0,
				UCProConst::CKX_SRC		=> 'PCWEB',
			),
			UCProConst::CKT => Array
			(
				UCProConst::CKT_LOGIN_TM	=> $nLoginTime,
				UCProConst::CKT_REFRESH_TM	=> $nRefreshTime,
				UCProConst::CKT_UPDATE_TM	=> $nUpdateTime,
				UCProConst::CKT_KP_ALIVE	=> 1,
				UCProConst::CKT_SS_ID		=> '',
			),
		);
		$cUClientPro->setConfig( UCProConst::CFGKEY_DOMAIN, '.xs.cn' );
		$cUClientPro->setConfig( UCProConst::CFGKEY_SEED, self::CONST_SEED );

		$sUMId		= $arrData[ UCProConst::CKX ][ UCProConst::CKX_MID ];
		$sCkString      = '';
		$nErrorId       = $cUClientPro->makeLogin( $arrData, true, $sCkString );
		$bSuccess	= ( UCProError::SUCCESS == $nErrorId );

		$this->_OutputResult( __FUNCTION__, 'makeLogin', $nErrorId, $bSuccess );
		echo "\t@ try to make login for user [ $sUMId ]: \r\n";
		if ( UCProError::SUCCESS == $nErrorId )
		{
			echo "\t- successfully.\r\n";
			echo "\t- Cookie string: " . $sCkString . "\r\n";
		}
		else
		{
			echo "\t- failed. error id=" . $nErrorId . "\r\n";
		}
		echo "\r\n";
	}




	protected function _OutputResult( $sFuncName, $sCallMethod, $nErrorId, $bAssert )
	{
		printf( "\r\n# %s->%s\r\n", $sFuncName, $sCallMethod );
		printf( "# ErrorId : %6d, result : [%s]", $nErrorId, ( $bAssert ? "OK" : "ERROR" ) );
		printf( "\r\n" );

		$this->assertTrue( $bAssert );
	}
}
