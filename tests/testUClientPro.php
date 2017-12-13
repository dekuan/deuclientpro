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
	public function testMakeLogin()
	{
		$cUClientPro	= UClientPro::getInstance();
		
		
		var_dump( $cUClientPro );
	}
}
