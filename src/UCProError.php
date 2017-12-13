<?php

namespace dekuan\deuclientpro;


/**
 *	Created by PhpStorm.
 *	User: xing
 *	Date: 11:36 AM December 13, 2017
 *	Time: 
 */
class UCProError
{
	//
	//	error ids
	//
	const ERR_UNKNOWN		= -1;		//	unknown error
	const ERR_SUCCESS		= 0;		//	successfully
	const ERR_FAILURE		= -1000;	//      failed
	const ERR_PARAMETER		= -1001;	//      error in parameter
	const ERR_INVALID_XT_COOKIE	= -1002;	//	invalid XT cookie
	const ERR_INVALID_CRC		= -1003;	//	invalid CRC
	const ERR_INVALID_SIGN		= -1004;	//	invalid sign
	const ERR_LOGIN_TIMEOUT		= -1005;	//	login timeout
	const ERR_BAD_COOKIE		= -1006;	//	bad cookie
	const ERR_ENCRYPT_XT		= -1007;	//      failed to encrypt xt
	const ERR_SET_COOKIE		= -1008;	//      failed to set cookie
	const ERR_PARSE_COOKIE_STRING	= -1009;	//	failed to parse cookie string
	const ERR_RESET_COOKIE		= -1010;	//	failed to reset cookie
	
	
	
}
