<?php

class ModuleMetadataTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Fixture::reset();
        Ps_BestSellersClearSpy::resetClearCalls();
    }

    public function testConfigXmlMatchesModule()
    {
        $module = Fixture::module();
        $path = dirname(dirname(__DIR__)) . '/config.xml';
        if (class_exists('DOMDocument')) {
            $dom = new DOMDocument();
            $dom->load($path);
            $name = $dom->getElementsByTagName('name')->item(0)->nodeValue;
            $version = $dom->getElementsByTagName('version')->item(0)->nodeValue;
            $need = $dom->getElementsByTagName('need_instance')->item(0)->nodeValue;
            $configurable = $dom->getElementsByTagName('is_configurable')->item(0)->nodeValue;
        } else {
            $xml = simplexml_load_file($path);
            $name = (string) $xml->name;
            $version = (string) $xml->version;
            $need = (string) $xml->need_instance;
            $configurable = (string) $xml->is_configurable;
        }
        $this->assertSame($module->name, $name);
        $this->assertSame($module->version, $version);
        $this->assertSame('0', $need);
        $this->assertSame('1', $configurable);
    }

    public function testTemplateUsesWidgetVariables()
    {
        $path = dirname(dirname(__DIR__)) . '/views/templates/hook/ps_bestsellers.tpl';
        $source = file_get_contents($path);
        $this->assertContains('{$allBestSellers}', $source);
        $this->assertContains('$products', $source);
        $this->assertContains('catalog/_partials/miniatures/product.tpl', $source);
        $this->assertContains('Modules.Bestsellers.Shop', $source);
    }
}
