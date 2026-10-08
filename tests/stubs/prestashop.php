<?php

namespace PrestaShop\PrestaShop\Core\Module
{
    interface WidgetInterface
    {
    }
}

namespace PrestaShop\PrestaShop\Core\Product\Search
{
    class SortOrder
    {
        public $entity;
        public $field;
        public $direction;

        public function __construct($entity, $field, $direction)
        {
            $this->entity = $entity;
            $this->field = $field;
            $this->direction = $direction;
        }
    }

    class ProductSearchQuery
    {
        private $resultsPerPage;
        private $page;
        private $sortOrder;

        public function setResultsPerPage($n)
        {
            $this->resultsPerPage = $n;

            return $this;
        }

        public function getResultsPerPage()
        {
            return $this->resultsPerPage;
        }

        public function setPage($page)
        {
            $this->page = $page;

            return $this;
        }

        public function getPage()
        {
            return $this->page;
        }

        public function setSortOrder(SortOrder $sortOrder)
        {
            $this->sortOrder = $sortOrder;

            return $this;
        }

        public function getSortOrder()
        {
            return $this->sortOrder;
        }
    }

    class ProductSearchContext
    {
        public $context;

        public function __construct($context)
        {
            $this->context = $context;
            \StubState::$searchContexts[] = $context;
        }
    }
}

namespace PrestaShop\PrestaShop\Adapter\BestSales
{
    class BestSalesProductSearchProvider
    {
        public $translator;

        public function __construct($translator)
        {
            $this->translator = $translator;
            \StubState::$providerConstructCount++;
            \StubState::$providerTranslators[] = $translator;
        }

        public function runQuery($context, $query)
        {
            \StubState::$runQueryCalls[] = array($context, $query);

            return new \SearchResultStub();
        }
    }
}

namespace PrestaShop\PrestaShop\Adapter\Image
{
    class ImageRetriever
    {
        public $link;

        public function __construct($link)
        {
            $this->link = $link;
            \StubState::$imageRetrieverLinks[] = $link;
        }
    }
}

namespace PrestaShop\PrestaShop\Adapter\Product
{
    class PriceFormatter
    {
        public function __construct()
        {
            \StubState::$priceFormatterCount++;
        }
    }

    class ProductColorsRetriever
    {
        public function __construct()
        {
            \StubState::$colorsRetrieverCount++;
        }
    }
}

namespace PrestaShop\PrestaShop\Adapter\Presenter\Product
{
    class ProductListingPresenter
    {
        public $imageRetriever;
        public $link;
        public $priceFormatter;
        public $colorsRetriever;
        public $translator;

        public function __construct($imageRetriever, $link, $priceFormatter, $colorsRetriever, $translator)
        {
            $this->imageRetriever = $imageRetriever;
            $this->link = $link;
            $this->priceFormatter = $priceFormatter;
            $this->colorsRetriever = $colorsRetriever;
            $this->translator = $translator;
            \StubState::$adapterPresenterCount++;
            \StubState::$presenterLinks[] = $link;
            \StubState::$presenterTranslators[] = $translator;
        }

        public function present($settings, $assembled, $language)
        {
            \StubState::$presentCalls[] = array($settings, $assembled, $language);
            $id = is_array($assembled) && isset($assembled['id']) ? $assembled['id'] : 'unknown';

            return array('id' => $id, 'presented' => true);
        }
    }
}

namespace PrestaShop\PrestaShop\Core\Product
{
    class ProductListingPresenter
    {
        public $imageRetriever;
        public $link;
        public $priceFormatter;
        public $colorsRetriever;
        public $translator;

        public function __construct($imageRetriever, $link, $priceFormatter, $colorsRetriever, $translator)
        {
            $this->imageRetriever = $imageRetriever;
            $this->link = $link;
            $this->priceFormatter = $priceFormatter;
            $this->colorsRetriever = $colorsRetriever;
            $this->translator = $translator;
            \StubState::$corePresenterCount++;
            \StubState::$presenterLinks[] = $link;
            \StubState::$presenterTranslators[] = $translator;
        }

        public function present($settings, $assembled, $language)
        {
            \StubState::$presentCalls[] = array($settings, $assembled, $language);
            $id = is_array($assembled) && isset($assembled['id']) ? $assembled['id'] : 'unknown';

            return array('id' => $id, 'presented' => true);
        }
    }
}

namespace
{
class SearchResultStub
{
    public function getProducts()
    {
        return StubState::$rawProducts;
    }
}

class StubState
{
    public static $config = array();
    public static $updateValueFails = false;
    public static $deleteByNameFails = false;

    public static $post = array();
    public static $submitKeys = array();
    public static $adminTokenLite = 'token-admin-modules';
    public static $adminTokenLiteCalls = array();

    public static $fillProductSalesResult = true;
    public static $fillProductSalesCalls = 0;

    public static $parentInstallResult = true;
    public static $parentUninstallResult = true;
    public static $registeredHooks = array();
    public static $registerHookResults = array();
    public static $sequenceLog = array();

    public static $providerConstructCount = 0;
    public static $providerTranslators = array();
    public static $runQueryCalls = array();
    public static $searchContexts = array();

    public static $presentationSettings = array('presentation' => 'settings-sentinel');
    public static $rawProducts = array();
    public static $assembleProductReturns = array();
    public static $assembleProductsReturn = array();
    public static $assembleProductCalls = 0;
    public static $assembleProductsCalls = 0;

    public static $presentCalls = array();
    public static $adapterPresenterCount = 0;
    public static $corePresenterCount = 0;
    public static $presenterLinks = array();
    public static $presenterTranslators = array();
    public static $imageRetrieverLinks = array();
    public static $priceFormatterCount = 0;
    public static $colorsRetrieverCount = 0;

    public static $transLog = array();
    public static $formHtml = 'FORM_HTML_SENTINEL';
    public static $languageConstructArgs = array();

    public static $isCached = false;
    public static $fetchReturn = 'FETCH_SENTINEL_HTML';
    public static $isCachedCalls = array();
    public static $fetchCalls = array();
    public static $getCacheIdCalls = array();
    public static $smartyAssigns = array();
    public static $parentClearCacheCalls = array();
    public static $pageLinkCalls = array();
    public static $adminLinkCalls = array();

    public static $lastHelperForm;
    public static $contextInstance;

    public static function reset()
    {
        self::$config = array();
        self::$updateValueFails = false;
        self::$deleteByNameFails = false;
        self::$post = array();
        self::$submitKeys = array();
        self::$adminTokenLiteCalls = array();
        self::$fillProductSalesResult = true;
        self::$fillProductSalesCalls = 0;
        self::$parentInstallResult = true;
        self::$parentUninstallResult = true;
        self::$registeredHooks = array();
        self::$registerHookResults = array();
        self::$sequenceLog = array();
        self::$providerConstructCount = 0;
        self::$providerTranslators = array();
        self::$runQueryCalls = array();
        self::$searchContexts = array();
        self::$rawProducts = array();
        self::$assembleProductReturns = array();
        self::$assembleProductsReturn = array();
        self::$assembleProductCalls = 0;
        self::$assembleProductsCalls = 0;
        self::$presentCalls = array();
        self::$adapterPresenterCount = 0;
        self::$corePresenterCount = 0;
        self::$presenterLinks = array();
        self::$presenterTranslators = array();
        self::$imageRetrieverLinks = array();
        self::$priceFormatterCount = 0;
        self::$colorsRetrieverCount = 0;
        self::$transLog = array();
        self::$languageConstructArgs = array();
        self::$isCached = false;
        self::$isCachedCalls = array();
        self::$fetchCalls = array();
        self::$getCacheIdCalls = array();
        self::$smartyAssigns = array();
        self::$parentClearCacheCalls = array();
        self::$pageLinkCalls = array();
        self::$adminLinkCalls = array();
        self::$lastHelperForm = null;
        self::$contextInstance = null;
    }
}

class Configuration
{
    public static function get($key)
    {
        return isset(StubState::$config[$key]) ? StubState::$config[$key] : false;
    }

    public static function updateValue($key, $value)
    {
        if (StubState::$updateValueFails) {
            return false;
        }
        StubState::$config[$key] = $value;

        return true;
    }

    public static function deleteByName($key)
    {
        if (StubState::$deleteByNameFails) {
            return false;
        }
        unset(StubState::$config[$key]);

        return true;
    }
}

class Tools
{
    public static function isSubmit($key)
    {
        return in_array($key, StubState::$submitKeys, true);
    }

    public static function getValue($key, $default = false)
    {
        if (array_key_exists($key, StubState::$post)) {
            return StubState::$post[$key];
        }

        return $default;
    }

    public static function getAdminTokenLite($controller)
    {
        StubState::$adminTokenLiteCalls[] = $controller;

        return StubState::$adminTokenLite;
    }
}

class LinkStub
{
    public function getPageLink($page)
    {
        StubState::$pageLinkCalls[] = $page;

        return 'https://shop.test/' . $page;
    }

    public function getAdminLink($controller, $withToken = true)
    {
        StubState::$adminLinkCalls[] = array($controller, $withToken);

        return 'https://shop.test/admin/index.php?controller=' . $controller;
    }
}

class ControllerStub
{
    public function getLanguages()
    {
        return array(array('id_lang' => 1, 'iso_code' => 'en'));
    }
}

class LanguageStub
{
    public $id;

    public function __construct($id)
    {
        $this->id = (int) $id;
        StubState::$languageConstructArgs[] = (int) $id;
    }
}

class Language extends LanguageStub
{
}

class Context
{
    public $link;
    public $controller;
    public $language;
    private $translator;

    public function __construct()
    {
        $this->link = new LinkStub();
        $this->controller = new ControllerStub();
        $this->language = (object) array('id' => 7);
        $this->translator = (object) array('marker' => 'context-translator');
    }

    public static function getContext()
    {
        if (StubState::$contextInstance === null) {
            StubState::$contextInstance = new Context();
        }

        return StubState::$contextInstance;
    }

    public function getTranslator()
    {
        return $this->translator;
    }
}

class ProductSale
{
    public static function fillProductSales()
    {
        StubState::$fillProductSalesCalls++;

        return StubState::$fillProductSalesResult;
    }
}

class HelperForm
{
    public static $lastInstance;

    public $show_toolbar;
    public $table;
    public $default_form_language;
    public $allow_employee_form_lang;
    public $identifier;
    public $submit_action;
    public $currentIndex;
    public $token;
    public $tpl_vars;
    public $lastForm;

    public function __construct()
    {
        StubState::$lastHelperForm = $this;
    }

    public function generateForm($forms)
    {
        $this->lastForm = $forms;

        return StubState::$formHtml;
    }
}

class ProductPresenterFactory
{
    public $context;

    public function __construct($context)
    {
        $this->context = $context;
    }

    public function getPresentationSettings()
    {
        return StubState::$presentationSettings;
    }
}

$assembleBulk = getenv('PS_BESTSELLERS_ASSEMBLE_BULK') === '1';

if ($assembleBulk) {
    class ProductAssembler
    {
        public $context;

        public function __construct($context)
        {
            $this->context = $context;
        }

        public function assembleProducts($rawProducts)
        {
            StubState::$assembleProductsCalls++;
            if (!empty(StubState::$assembleProductsReturn)) {
                return StubState::$assembleProductsReturn;
            }
            $out = array();
            foreach ($rawProducts as $raw) {
                $out[] = array('id' => $raw['id'], 'bulk_id' => true);
            }

            return $out;
        }
    }
} else {
    class ProductAssembler
    {
        public $context;

        public function __construct($context)
        {
            $this->context = $context;
        }

        public function assembleProduct($rawProduct)
        {
            StubState::$assembleProductCalls++;
            if (isset(StubState::$assembleProductReturns[$rawProduct['id']])) {
                return StubState::$assembleProductReturns[$rawProduct['id']];
            }

            return array('id' => $rawProduct['id'], 'assembled_id' => true);
        }
    }
}

class Module
{
    public $name;
    public $tab;
    public $author;
    public $version;
    public $need_instance;
    public $bootstrap;
    public $displayName;
    public $description;
    public $ps_versions_compliancy;
    public $table = 'module';
    public $identifier = 'id_module';
    public $context;
    public $smarty;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->smarty = new SmartyStub();
    }

    public function install()
    {
        StubState::$sequenceLog[] = 'parent_install';

        return StubState::$parentInstallResult;
    }

    public function uninstall()
    {
        StubState::$sequenceLog[] = 'parent_uninstall';

        return StubState::$parentUninstallResult;
    }

    public function registerHook($hookName)
    {
        if (isset(StubState::$registerHookResults[$hookName])) {
            $result = StubState::$registerHookResults[$hookName];
        } else {
            $result = true;
        }
        if ($result) {
            StubState::$registeredHooks[] = $hookName;
        }

        return $result;
    }

    protected function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        StubState::$parentClearCacheCalls[] = array($template, $cache_id, $compile_id);
    }

    public function isCached($template, $cache_id = null, $compile_id = null)
    {
        StubState::$isCachedCalls[] = array($template, $cache_id, $compile_id);

        return StubState::$isCached;
    }

    public function fetch($template, $cache_id = null, $compile_id = null)
    {
        StubState::$fetchCalls[] = array($template, $cache_id, $compile_id);

        return StubState::$fetchReturn;
    }

    public function getCacheId($name)
    {
        StubState::$getCacheIdCalls[] = $name;

        return 'cid:' . $name;
    }

    public function trans($string, $params, $domain)
    {
        StubState::$transLog[] = array($string, $params, $domain);

        return $string;
    }

    public function displayConfirmation($html)
    {
        return '<div class="bootstrap">' . $html . '</div>';
    }
}

class SmartyStub
{
    public function assign($variables)
    {
        StubState::$smartyAssigns[] = $variables;
    }
}

}
