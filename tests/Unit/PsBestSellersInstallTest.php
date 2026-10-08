<?php

class PsBestSellersInstallTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Fixture::reset();
        Ps_BestSellersClearSpy::resetClearCalls();
    }

    public function testInstallSucceedsAndRegistersHooks()
    {
        $spy = Fixture::clearSpy();
        $this->assertTrue($spy->install());
        $this->assertSame(8, Configuration::get('PS_BLOCK_BESTSELLERS_TO_DISPLAY'));
        $this->assertSame(
            array(
                'actionOrderStatusPostUpdate',
                'actionProductAdd',
                'actionProductUpdate',
                'actionProductDelete',
                'displayHome',
            ),
            StubState::$registeredHooks
        );
        $this->assertSame(1, StubState::$fillProductSalesCalls);
        $this->assertCount(1, Ps_BestSellersClearSpy::$clearCalls);
        $this->assertSame(array('*', null, null), Ps_BestSellersClearSpy::$clearCalls[0]);
        $this->assertSame('parent_install', StubState::$sequenceLog[0]);
    }

    public function testInstallStopsWhenParentInstallFails()
    {
        StubState::$parentInstallResult = false;
        $spy = Fixture::clearSpy();
        $this->assertFalse($spy->install());
        $this->assertFalse(array_key_exists('PS_BLOCK_BESTSELLERS_TO_DISPLAY', StubState::$config));
        $this->assertSame(array(), StubState::$registeredHooks);
        $this->assertSame(0, StubState::$fillProductSalesCalls);
    }

    public function testInstallStopsWhenConfigUpdateFails()
    {
        StubState::$updateValueFails = true;
        $spy = Fixture::clearSpy();
        $this->assertFalse($spy->install());
        $this->assertSame(array(), StubState::$registeredHooks);
        $this->assertSame(0, StubState::$fillProductSalesCalls);
    }

    public function testInstallStopsOnThirdHook()
    {
        StubState::$registerHookResults['actionProductUpdate'] = false;
        $spy = Fixture::clearSpy();
        $this->assertFalse($spy->install());
        $this->assertSame(
            array('actionOrderStatusPostUpdate', 'actionProductAdd'),
            StubState::$registeredHooks
        );
        $this->assertSame(0, StubState::$fillProductSalesCalls);
    }

    public function testInstallFailsWhenFillProductSalesFails()
    {
        StubState::$fillProductSalesResult = false;
        $spy = Fixture::clearSpy();
        $this->assertFalse($spy->install());
        $this->assertCount(5, StubState::$registeredHooks);
    }

    public function testEachHookClearsWithStar()
    {
        $hooks = array(
            'hookActionProductAdd',
            'hookActionProductUpdate',
            'hookActionProductDelete',
            'hookActionOrderStatusPostUpdate',
        );
        foreach ($hooks as $hook) {
            Ps_BestSellersClearSpy::resetClearCalls();
            Fixture::reset();
            $spy = Fixture::clearSpy();
            $spy->$hook(array());
            $this->assertCount(1, Ps_BestSellersClearSpy::$clearCalls);
            $this->assertSame(array('*', null, null), Ps_BestSellersClearSpy::$clearCalls[0]);
        }
    }

    public function testUninstallDeletesOnlyTheDisplayKey()
    {
        StubState::$config['PS_BLOCK_BESTSELLERS_TO_DISPLAY'] = '4';
        StubState::$config['OTHER_KEY'] = 'keep';
        $spy = Fixture::clearSpy();
        $this->assertTrue($spy->uninstall());
        $this->assertFalse(array_key_exists('PS_BLOCK_BESTSELLERS_TO_DISPLAY', StubState::$config));
        $this->assertSame('keep', StubState::$config['OTHER_KEY']);
    }

    public function testUninstallStopsWhenParentFails()
    {
        StubState::$config['PS_BLOCK_BESTSELLERS_TO_DISPLAY'] = '3';
        StubState::$parentUninstallResult = false;
        $spy = Fixture::clearSpy();
        $this->assertFalse($spy->uninstall());
        $this->assertSame('3', StubState::$config['PS_BLOCK_BESTSELLERS_TO_DISPLAY']);
    }

    public function testUninstallFailsWhenDeleteFails()
    {
        StubState::$deleteByNameFails = true;
        $spy = Fixture::clearSpy();
        $this->assertFalse($spy->uninstall());
    }
}
