<?php

class PsBestSellersQueryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Fixture::reset();
        Ps_BestSellersClearSpy::resetClearCalls();
        StubState::$config['PS_CATALOG_MODE'] = '0';
        Fixture::rawProducts(array(10, 20));
        StubState::$isCached = false;
    }

    public function testQueryUsesConfiguredPageSizeAndSalesSort()
    {
        StubState::$config['PS_BLOCK_BESTSELLERS_TO_DISPLAY'] = '4';
        $module = Fixture::module();
        $module->getWidgetVariables('displayHome', array());
        $this->assertCount(1, StubState::$runQueryCalls);
        $query = StubState::$runQueryCalls[0][1];
        $this->assertSame(4, $query->getResultsPerPage());
        $this->assertSame(1, $query->getPage());
        $sort = $query->getSortOrder();
        $this->assertSame('product', $sort->entity);
        $this->assertSame('sales', $sort->field);
        $this->assertSame('desc', $sort->direction);
        $context = Context::getContext();
        $this->assertSame($context->getTranslator(), StubState::$providerTranslators[0]);
        $this->assertSame($context, StubState::$searchContexts[0]);
    }

    public function testPresenterReceivesAssembledRowsAndContextLanguage()
    {
        $module = Fixture::module();
        $module->getWidgetVariables('displayHome', array());
        $context = Context::getContext();
        $bulk = getenv('PS_BESTSELLERS_ASSEMBLE_BULK') === '1';
        if ($bulk) {
            $this->assertSame(1, StubState::$assembleProductsCalls);
            $this->assertSame(0, StubState::$assembleProductCalls);
            foreach (StubState::$presentCalls as $call) {
                $this->assertTrue(isset($call[1]['bulk_id']));
            }
        } else {
            $this->assertSame(2, StubState::$assembleProductCalls);
            $this->assertSame(0, StubState::$assembleProductsCalls);
            foreach (StubState::$presentCalls as $call) {
                $this->assertTrue(isset($call[1]['assembled_id']));
            }
        }
        foreach (StubState::$presentCalls as $call) {
            $this->assertSame(StubState::$presentationSettings, $call[0]);
            $this->assertSame($context->language, $call[2]);
        }
        $this->assertSame(1, StubState::$priceFormatterCount);
        $this->assertSame(1, StubState::$colorsRetrieverCount);
        $this->assertSame($context->link, StubState::$imageRetrieverLinks[0]);
        $this->assertSame($context->link, StubState::$presenterLinks[0]);
        $this->assertSame($context->getTranslator(), StubState::$presenterTranslators[0]);
    }

    public function testPresenterClassFollowsPinnedVersionMap()
    {
        $map = array(
            '1.7.0.0' => 'core',
            '1.7.4.4' => 'core',
            '1.7.5' => 'adapter',
            '1.7.5.0' => 'adapter',
            '8.1.0' => 'adapter',
        );
        if (!isset($map[_PS_VERSION_])) {
            $this->fail('Unexpected _PS_VERSION_ ' . _PS_VERSION_);
        }
        $module = Fixture::module();
        $module->getWidgetVariables('displayHome', array());
        $expected = $map[_PS_VERSION_];
        if ($expected === 'core') {
            $this->assertSame(1, StubState::$corePresenterCount);
            $this->assertSame(0, StubState::$adapterPresenterCount);
        } else {
            $this->assertSame(1, StubState::$adapterPresenterCount);
            $this->assertSame(0, StubState::$corePresenterCount);
        }
    }
}
