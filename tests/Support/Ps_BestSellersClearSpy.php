<?php

class Ps_BestSellersClearSpy extends Ps_BestSellers
{
    public static $clearCalls = array();

    public static function resetClearCalls()
    {
        self::$clearCalls = array();
    }

    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        self::$clearCalls[] = array($template, $cache_id, $compile_id);
    }
}
