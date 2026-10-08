<?php

class PsBestSellersConfigTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Fixture::reset();
        Ps_BestSellersClearSpy::resetClearCalls();
    }

    public function testGetContentWithoutSubmitOnlyRendersTheForm()
    {
        $module = Fixture::module();
        $output = $module->getContent();
        $this->assertFalse(array_key_exists('PS_BLOCK_BESTSELLERS_TO_DISPLAY', StubState::$config));
        $this->assertSame('FORM_HTML_SENTINEL', $output);
    }

    public function testGetContentSavesIntegerAndConfirms()
    {
        StubState::$submitKeys[] = 'submitBestSellers';
        StubState::$post['PS_BLOCK_BESTSELLERS_TO_DISPLAY'] = '5';
        StubState::$config['PS_BLOCK_BESTSELLERS_TO_DISPLAY'] = '9';
        $module = Fixture::module();
        $output = $module->getContent();
        $this->assertSame(5, StubState::$config['PS_BLOCK_BESTSELLERS_TO_DISPLAY']);
        $this->assertContains('The settings have been updated.', $output);
        $this->assertContains('FORM_HTML_SENTINEL', $output);
        $posConfirm = strpos($output, 'The settings have been updated.');
        $posForm = strpos($output, 'FORM_HTML_SENTINEL');
        $this->assertTrue(is_int($posConfirm));
        $this->assertTrue($posConfirm < $posForm);
        $found = false;
        foreach (StubState::$transLog as $entry) {
            if ($entry[0] === 'The settings have been updated.' && $entry[2] === 'Admin.Notifications.Success') {
                $found = true;
            }
        }
        $this->assertTrue($found);
    }

    public function testGetContentCastsMissingPostToZero()
    {
        StubState::$submitKeys[] = 'submitBestSellers';
        $module = Fixture::module();
        $module->getContent();
        $this->assertSame(0, StubState::$config['PS_BLOCK_BESTSELLERS_TO_DISPLAY']);
    }

    public function testFieldValuesPreferPostOverConfiguration()
    {
        StubState::$config['PS_BLOCK_BESTSELLERS_TO_DISPLAY'] = '6';
        $module = Fixture::module();
        $this->assertSame(
            array('PS_BLOCK_BESTSELLERS_TO_DISPLAY' => 6),
            $module->getConfigFieldsValues()
        );
        StubState::$post['PS_BLOCK_BESTSELLERS_TO_DISPLAY'] = '2';
        $this->assertSame(
            array('PS_BLOCK_BESTSELLERS_TO_DISPLAY' => 2),
            $module->getConfigFieldsValues()
        );
        StubState::$post['PS_BLOCK_BESTSELLERS_TO_DISPLAY'] = '';
        $this->assertSame(
            array('PS_BLOCK_BESTSELLERS_TO_DISPLAY' => 0),
            $module->getConfigFieldsValues()
        );
    }

    public function testRenderFormShape()
    {
        StubState::$config['PS_LANG_DEFAULT'] = '2';
        StubState::$config['PS_BO_ALLOW_EMPLOYEE_FORM_LANG'] = false;
        StubState::$config['PS_BLOCK_BESTSELLERS_TO_DISPLAY'] = '6';
        $module = Fixture::module();
        $html = $module->renderForm();
        $this->assertSame('FORM_HTML_SENTINEL', $html);
        $helper = StubState::$lastHelperForm;
        $this->assertInstanceOf('HelperForm', $helper);
        $this->assertFalse($helper->show_toolbar);
        $this->assertSame('submitBestSellers', $helper->submit_action);
        $this->assertSame(2, $helper->default_form_language);
        $this->assertSame(0, $helper->allow_employee_form_lang);
        $this->assertSame(array(2), StubState::$languageConstructArgs);
        $this->assertSame('token-admin-modules', $helper->token);
        $this->assertSame(array('AdminModules'), StubState::$adminTokenLiteCalls);
        $this->assertTrue(is_string($helper->currentIndex));
        $this->assertTrue(strpos($helper->currentIndex, 'https://shop.test/admin/index.php?controller=AdminModules') === 0);
        $this->assertContains('configure=ps_bestsellers', $helper->currentIndex);
        $this->assertContains('tab_module=front_office_features', $helper->currentIndex);
        $this->assertContains('module_name=ps_bestsellers', $helper->currentIndex);
        $form = $helper->lastForm[0]['form'];
        $this->assertSame('text', $form['input'][0]['type']);
        $this->assertSame('PS_BLOCK_BESTSELLERS_TO_DISPLAY', $form['input'][0]['name']);
        $this->assertSame('fixed-width-xs', $form['input'][0]['class']);
        $this->assertSame('icon-cogs', $form['legend']['icon']);
        $this->assertSame('Save', $form['submit']['title']);
        $this->assertSame($module->getConfigFieldsValues(), $helper->tpl_vars['fields_value']);
        $this->assertSame($module->context->controller->getLanguages(), $helper->tpl_vars['languages']);
        $this->assertSame(7, $helper->tpl_vars['id_language']);
        $this->assertTranslationExists('Settings', 'Admin.Global');
        $this->assertTranslationExists('Products to display', 'Modules.Bestsellers.Admin');
        $this->assertTranslationExists('Determine the number of product to display in this block', 'Modules.Bestsellers.Admin');
        $this->assertTranslationExists('Save', 'Admin.Actions');
    }

    private function assertTranslationExists($string, $domain)
    {
        foreach (StubState::$transLog as $entry) {
            if ($entry[0] === $string && $entry[2] === $domain) {
                return;
            }
        }
        $this->fail('Missing translation ' . $string . ' in ' . $domain);
    }
}
