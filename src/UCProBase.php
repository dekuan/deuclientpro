<?php

namespace dekuan\deuclientpro;


/**
 *      class of UCProBase
 */
class UCProBase
{
        //	statics
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
	final private function __clone()
	{
	}

}





?>