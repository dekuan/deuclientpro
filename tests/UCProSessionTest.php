<?php

namespace dekuan\deuclientpro;


use dekuan\delib\CLib;
use dekuan\vdata\CConst;


/**
 * Created by PhpStorm.
 * User: xing
 * Date: 17/02/2017
 * Time: 12:49 AM
 */
class UCProSessionTest extends \PHPUnit\Framework\TestCase
{
	/**
	 *	@runInSeparateProcess
	 */
	public function testCheckSessionByRPC()
	{
		$cProSession	= UCProSession::getInstance();

		$objParam	= new \stdClass();
		$objParam->{ UCProConst::CKX_MID }	= '120999';
		$objParam->{ UCProConst::CKT_SS_ID }	= '123';
		$objParam->{ UCProConst::CKT_SS_URL }	= 'http://api.account.w.yunkuan.org/api/user/check/session';
		$objParam->{ 'cookie_array' }		= [ UCProConst::CKX => '', UCProConst::CKT => '' ];

		$nCheckReturn	= null;
		$nCallCheck	= $cProSession->checkSessionByRPC( $objParam, $nCheckReturn );

		$this->assertEquals( CConst::ERROR_SUCCESS, $nCallCheck );
		$this->assertEquals( CConst::ERROR_SUCCESS, $nCheckReturn );
	}



	
}
