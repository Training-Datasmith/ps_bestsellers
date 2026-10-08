<?php

class Fixture
{
    public static function reset()
    {
        StubState::reset();
        Ps_BestSellersClearSpy::resetClearCalls();
    }

    public static function module()
    {
        return new Ps_BestSellers();
    }

    public static function clearSpy()
    {
        return new Ps_BestSellersClearSpy();
    }

    public static function rawProducts($ids)
    {
        $products = array();
        foreach ($ids as $id) {
            $products[] = array('id' => $id);
        }
        StubState::$rawProducts = $products;
    }
}
