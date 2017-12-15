<?php

namespace dekuan\deuclientpro;


/**
 *	Created by PhpStorm.
 *	User: xing
 *	Date: 11:36 December 13, 2017
 *	Time: 
 */
class UCProConst
{
	//
	//	keys for cookie
	//
	const CKX               = 'X';
	const CKT               = 'T';

	const CKX_MID		= 'mid';	//	string	- user mid ( a string with length of 32/64 characters )
	const CKX_NICKNAME	= 'nkn';	//	string	- user nick name
	const CKX_TYPE		= 't';		//	int	- user type, values( NORMAL, TEMP, ... )
	const CKX_AVATAR	= 'avatar';	//	string	- the mid of user avatar
	const CKX_STATUS	= 'sts';	//	int	- user status
	const CKX_ACTION	= 'act';	//	int	- user action
	const CKX_SRC		= 'src';	//	string	- the source which a user logged on from
	const CKX_DIGEST	= 'digest';	//	string	- message digest calculation

	const CKT_VER		= 'v';		//	string	- cookie version
	const CKT_LOGIN_TM	= 'ltm';	//	int	- login time, unix time stamp in timezone 0.
	const CKT_REFRESH_TM	= 'rtm';	//	int	- last refresh time
	const CKT_UPDATE_TM	= 'utm';	//	int	- last update time
	const CKT_KP_ALIVE	= 'kpa';	//	int	- keep alive, values( YES, NO )
	const CKT_SS_ID		= 'ssid';	//	string	- session id
	const CKT_CKS_MD5	= 'csm';	//	string	- checksum sign
	const CKT_CKS_CRC	= 'csc';	//	string	- checksum crc

	//
	//      keys for configuration
	//
	const CONFIG_COOKIE_DOMAIN	= 'domain';     //      domain that the cookie is available to.
	const CONFIG_COOKIE_PATH	= 'path';       //      path on the server in which the cookie will be available on.
	const CONFIG_COOKIE_SEED	= 'seed';       //      seed for making sign
	const CONFIG_SECURE		= 'secure';     //      indicates that the cookie should only be transmitted over a secure HTTPS connection from the client.
	const CONFIG_HTTP_ONLY		= 'httponly';   //      when TRUE the cookie will be made accessible only through the HTTP protocol.
	const CONFIG_SS_TIMEOUT		= 'stimeout';   //      session timeout

	//
	//	default values
	//
	const DEFAULT_DOMAIN            = '.dekuan.org';
	const DEFAULT_PATH	        = '/';
	const DEFAULT_SIGN_SEED	        = '03abafc5ssss-2f15-66ea-bc1f-51805f380f06/9b2331cb-8a9c-4a29-a9ab-25e13359279c';
	const DEFAULT_SECURE	        = false;	//	cookie should only be transmitted over a secure HTTPS connection from the client
	const DEFAULT_HTTP_ONLY	        = true;		//	cookie will be made accessible only through the HTTP protocol
	const DEFAULT_SS_TIMEOUT	= 86400;	//	session timeout, default is 1 day.

	//
	//	config values
	//
	const CONFIG_TIME_SECONDS_YEAR	= 365 * 24 * 60 * 60;


	//
	//      user status
	//
	const STATUS_UNVERIFIED		= 0;	//	unverified
	const STATUS_OKAY		= 1;	//	okay
	const STATUS_DELETED		= 2;	//	deleted
	const STATUS_EXPIRED		= 3;	//	expired
	const STATUS_DENIED		= 4;	//	denied
	const STATUS_COMPLETE		= 5;	//	complete
	const STATUS_ABORT		= 6;	//	abort
	const STATUS_PENDING		= 7;	//	pending
	const STATUS_ACCEPTED		= 8;	//	accepted
	const STATUS_REJECTED		= 9;	//	rejected
	const STATUS_ARCHIVED		= 10;	//	archived

	//
	//	cookie information
	//
	const COOKIE_VERSION            = '1.0.1.1000';	
	
	
}
