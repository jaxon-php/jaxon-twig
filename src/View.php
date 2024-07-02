<?php

namespace Jaxon\Twig;

use Jaxon\App\View\Store;
use Jaxon\App\View\ViewInterface;
use Jaxon\Script\JsExpr;
use Jaxon\Script\JxnCall;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

use function Jaxon\attr;
use function Jaxon\jq;
use function Jaxon\js;
use function Jaxon\pm;
use function Jaxon\rq;
use function ltrim;
use function str_replace;
use function trim;

class View implements ViewInterface
{
    /**
     * @var Twig|null
     */
    private static ?Twig $xRenderer = null;

    /**
     * @var FilesystemLoader|null
     */
    private static ?FilesystemLoader $xLoader = null;

    /**
     * @var array
     */
    private static array $aExtensions = [];

    /**
     * @return FilesystemLoader
     */
    private static function _loader(): FilesystemLoader
    {
        if(!self::$xLoader)
        {
            self::$xLoader = new FilesystemLoader([], '');
        }
        return self::$xLoader;
    }

    /**
     * @return Twig
     */
    private static function _renderer(): Twig
    {
        if(!self::$xRenderer)
        {
            self::$xRenderer = new Twig(self::_loader(), [
                'cache' => __DIR__ . '/../cache',
            ]);

            // Filters for custom Jaxon attributes
            self::$xRenderer->addFilter(new TwigFilter('jxnHtml',
                fn(JxnCall $xJxnCall) => attr()->html($xJxnCall), ['is_safe' => ['html']]));
            self::$xRenderer->addFilter(new TwigFilter('jxnShow',
                fn(JxnCall $xJxnCall) => attr()->show($xJxnCall), ['is_safe' => ['html']]));
            self::$xRenderer->addFilter(new TwigFilter('jxnTarget',
                fn(string $name = '') => attr()->target($name), ['is_safe' => ['html']]));
            self::$xRenderer->addFilter(new TwigFilter('jxnOn',
                fn(string|array $on, JsExpr $xJsExpr, array $options = []) =>
                    attr()->on($on, $xJsExpr, $options), ['is_safe' => ['html']]));

            // Functions for custom Jaxon attributes
            self::$xRenderer->addFunction(new TwigFunction('jq', fn(...$aParams) => jq(...$aParams)));
            self::$xRenderer->addFunction(new TwigFunction('js', fn(...$aParams) => js(...$aParams)));
            self::$xRenderer->addFunction(new TwigFunction('rq', fn(...$aParams) => rq(...$aParams)));
            self::$xRenderer->addFunction(new TwigFunction('pm', fn() => pm()));
        }
        return self::$xRenderer;
    }

    /**
     * @inheritDoc
     */
    public function addNamespace(string $sNamespace, string $sDirectory, string $sExtension = '')
    {
        self::$aExtensions[$sNamespace] = '.' . ltrim($sExtension, '.');
        self::_loader()->addPath($sDirectory, $sNamespace);
    }

    /**
     * @inheritDoc
     */
    public function render(Store $store): string
    {
        $sNamespace = $store->getNamespace();
        $sViewName = !$sNamespace ? $store->getViewName() :
            '@' . $sNamespace . '/' . $store->getViewName();
        $sViewName = str_replace('.', '/', $sViewName);
        if(isset(self::$aExtensions[$sNamespace]))
        {
            $sViewName .= self::$aExtensions[$sNamespace];
        }

        // Render the template
        return trim(self::_renderer()->render($sViewName, $store->getViewData()), " \t\n");
    }
}
