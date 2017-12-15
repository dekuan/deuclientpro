<?php

require_once( __DIR__ . '/deuclient/src/CUCClient.php' );
require_once( __DIR__ . '/deuclient/src/CUCSession.php' );

use dekuan\deuclient as ucli;



testdeuclient_main();


function testdeuclient_main()
{
	$cUCli	= ucli\CUCClient::getInstance();

	//	...
	$cUCli->setConfig( ucli\CUCClient::CONFIG_COOKIE_DOMAIN, '.xs.cn' );
	$cUCli->setConfig( ucli\CUCClient::CONFIG_COOKIE_SEED, '5adf23adb-8815-46ea-ees1f-198sdcsf380f04/83221234cb-5af5c-1234f-88acd-12348sdsda2sdf' );
	

	echo "<pre>";

	if ( ucli\CUCClient::SUCCESS == $cUCli->checkLogin() )
	{
		echo "checkLogin successfully.\r\n";
		print_r( $cUCli->getXTArray() );
	}
	else
	{
		echo "Failed to checkLogin.\r\n";
	}


	$cUCli->logout();

	echo "Logged out.\r\n";


	echo "</pre>";
}


?>
