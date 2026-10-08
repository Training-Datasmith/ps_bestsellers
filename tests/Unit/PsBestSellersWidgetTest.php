<?php

class PsBestSellersWidgetTest extends PHPUnit_Framework_TestCase
{
    private $template = 'module:ps_bestsellers/views/templates/hook/ps_bestsellers.tpl';

    protected function setUp()
    {
        Fixture::reset();
        Ps_BestSellersClearSpy::resetClearCalls();
    }

    public function testCatalogModeSkipsSearch()
    {
        StubState::$config['PS_CATALOG_MODE'] = '1';
        $module = Fixture::module();
        $this->assertSame(false, $module->getWidgetVariables('displayHome', array()));
        $this->assertSame(0, StubState::$providerConstructCount);
        $this->assertSame(false, $module->renderWidget('displayHome', array()));
        $this->assertSame(0, count(StubState::$fetchCalls));
    }

    public function testCatalogModeOffStillSearches()
    {
        StubState::$config['PS_CATALOG_MODE'] = '0';
        Fixture::rawProducts(array(1));
        $module = Fixture::module();
        $module->getWidgetVariables('displayHome', array());
        $this->assertSame(1, StubState::$providerConstructCount);
    }

    public function testEmptySearchRendersNothing()
    {
        StubState::$config['PS_CATALOG_MODE'] = '0';
        StubState::$rawProducts = array();
        $module = Fixture::module();
        $this->assertSame(false, $module->getWidgetVariables('displayHome', array()));
        $this->assertSame(1, StubState::$providerConstructCount);
        $this->assertSame(0, count(StubState::$presentCalls));
        $this->assertSame(false, $module->renderWidget('displayHome', array()));
        $this->assertSame(0, count(StubState::$fetchCalls));
        $this->assertSame(0, count(StubState::$smartyAssigns));
    }

    public function testCacheHitSkipsRebuild()
    {
        StubState::$config['PS_CATALOG_MODE'] = '0';
        Fixture::rawProducts(array(1, 2, 3));
        StubState::$isCached = true;
        $module = Fixture::module();
        $result = $module->renderWidget('displayHome', array());
        $this->assertSame('FETCH_SENTINEL_HTML', $result);
        $this->assertSame(0, StubState::$providerConstructCount);
        $this->assertSame(0, count(StubState::$smartyAssigns));
        $this->assertSame(array('ps_bestsellers', 'ps_bestsellers'), StubState::$getCacheIdCalls);
        $this->assertCount(1, StubState::$isCachedCalls);
        $this->assertSame($this->template, StubState::$isCachedCalls[0][0]);
        $this->assertSame('cid:ps_bestsellers', StubState::$isCachedCalls[0][1]);
        $this->assertCount(1, StubState::$fetchCalls);
        $this->assertSame($this->template, StubState::$fetchCalls[0][0]);
        $this->assertSame('cid:ps_bestsellers', StubState::$fetchCalls[0][1]);
    }

    public function testCacheMissAssignsThenFetches()
    {
        StubState::$config['PS_CATALOG_MODE'] = '0';
        Fixture::rawProducts(array(10, 20, 30));
        StubState::$isCached = false;
        $module = Fixture::module();
        $variables = $module->getWidgetVariables('displayHome', array());
        $this->assertTrue(is_array($variables));
        $this->assertSame(array('products', 'allBestSellers'), array_keys($variables));
        $this->assertSame('https://shop.test/best-sales', $variables['allBestSellers']);
        $this->assertSame(array('best-sales'), StubState::$pageLinkCalls);
        $presentedIds = array();
        foreach ($variables['products'] as $product) {
            $presentedIds[] = $product['id'];
        }
        $this->assertSame(array(10, 20, 30), $presentedIds);
        $result = $module->renderWidget('displayHome', array());
        $this->assertSame('FETCH_SENTINEL_HTML', $result);
        $this->assertCount(1, StubState::$smartyAssigns);
        $this->assertSame($variables, StubState::$smartyAssigns[0]);
    }
}
