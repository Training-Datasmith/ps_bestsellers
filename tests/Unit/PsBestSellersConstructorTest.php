<?php

class PsBestSellersConstructorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Fixture::reset();
        Ps_BestSellersClearSpy::resetClearCalls();
    }

    public function testIdentity()
    {
        $module = Fixture::module();
        $this->assertSame('ps_bestsellers', $module->name);
        $this->assertSame('front_office_features', $module->tab);
        $this->assertSame('PrestaShop', $module->author);
        $this->assertSame('1.0.7', $module->version);
        $this->assertSame(0, $module->need_instance);
        $this->assertTrue($module->bootstrap);
        $this->assertSame(
            array('min' => '1.7.0.0', 'max' => _PS_VERSION_),
            $module->ps_versions_compliancy
        );
    }

    public function testTranslatedLabels()
    {
        $module = Fixture::module();
        $this->assertSame('Top-sellers block', $module->displayName);
        $this->assertSame(
            'Show your visitors what are your top-selling products directly on your homepage.',
            $module->description
        );
        $domains = array();
        foreach (StubState::$transLog as $entry) {
            $domains[] = $entry[2];
        }
        $this->assertContains('Modules.Bestsellers.Admin', $domains);
    }

    public function testWidgetMethodSignatures()
    {
        $ref = new ReflectionClass('Ps_BestSellers');
        foreach (array('renderWidget', 'getWidgetVariables') as $methodName) {
            $method = $ref->getMethod($methodName);
            $params = $method->getParameters();
            $this->assertCount(2, $params);
            $this->assertTrue($params[1]->isArray());
            $this->assertFalse($params[1]->isOptional());
        }
    }
}
