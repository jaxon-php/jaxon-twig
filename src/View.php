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
use function Jaxon\jaxon;
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
    private ?Twig $xRenderer = null;

    /**
     * @var FilesystemLoader|null
     */
    private ?FilesystemLoader $xLoader = null;

    /**
     * @var array
     */
    private array $aExtensions = [];

    /**
     * @return FilesystemLoader
     */
    private function _loader(): FilesystemLoader
    {
        if(!$this->xLoader)
        {
            $this->xLoader = new FilesystemLoader([], '');
        }
        return $this->xLoader;
    }

    /**
     * @return Twig
     */
    private function _renderer(): Twig
    {
        if(!$this->xRenderer)
        {
            $this->xRenderer = new Twig($this->_loader(), [
                'cache' => __DIR__ . '/../cache',
            ]);

            // Filters for custom Jaxon attributes
            $this->xRenderer->addFilter(new TwigFilter('jxnHtml',
                fn(JxnCall $xJxnCall) => attr()->html($xJxnCall), ['is_safe' => ['html']]));
            $this->xRenderer->addFilter(new TwigFilter('jxnShow',
                fn(JxnCall $xJxnCall) => attr()->show($xJxnCall), ['is_safe' => ['html']]));
            $this->xRenderer->addFilter(new TwigFilter('jxnOn',
                fn(JsExpr $xJsExpr, string|array $on, array $options = []) =>
                    attr()->on($on, $xJsExpr, $options), ['is_safe' => ['html']]));
            $this->xRenderer->addFilter(new TwigFilter('jxnClick',
                fn(JsExpr $xJsExpr, array $options = []) =>
                    attr()->click($xJsExpr, $options), ['is_safe' => ['html']]));

            // Functions for custom Jaxon attributes
            $this->xRenderer->addFunction(new TwigFunction('jxnHtml',
                fn(JxnCall $xJxnCall) => attr()->html($xJxnCall), ['is_safe' => ['html']]));
            $this->xRenderer->addFunction(new TwigFunction('jxnShow',
                fn(JxnCall $xJxnCall) => attr()->show($xJxnCall), ['is_safe' => ['html']]));
            $this->xRenderer->addFunction(new TwigFunction('jxnOn',
                fn(string|array $on, JsExpr $xJsExpr, array $options = []) =>
                    attr()->on($on, $xJsExpr, $options), ['is_safe' => ['html']]));
            $this->xRenderer->addFunction(new TwigFunction('jxnClick',
                fn(JsExpr $xJsExpr, array $options = []) =>
                    attr()->click($xJsExpr, $options), ['is_safe' => ['html']]));
            $this->xRenderer->addFunction(new TwigFunction('jxnTarget',
                fn(string $name = '') => attr()->target($name), ['is_safe' => ['html']]));

            $this->xRenderer->addFunction(new TwigFunction('jq', fn(...$aParams) => jq(...$aParams)));
            $this->xRenderer->addFunction(new TwigFunction('js', fn(...$aParams) => js(...$aParams)));
            $this->xRenderer->addFunction(new TwigFunction('rq', fn(...$aParams) => rq(...$aParams)));
            $this->xRenderer->addFunction(new TwigFunction('pm', fn() => pm()));

            // Functions for Jaxon js and CSS codes
            $this->xRenderer->addFunction(new TwigFunction('jxnCss',
                fn() => jaxon()->css(), ['is_safe' => ['html']]));
            $this->xRenderer->addFunction(new TwigFunction('jxnJs',
                fn() => jaxon()->js(), ['is_safe' => ['html']]));
            $this->xRenderer->addFunction(new TwigFunction('jxnScript',
                fn() => jaxon()->script(), ['is_safe' => ['html']]));
        }
        return $this->xRenderer;
    }

    /**
     * @inheritDoc
     */
    public function addNamespace(string $sNamespace, string $sDirectory, string $sExtension = '')
    {
        $this->aExtensions[$sNamespace] = '.' . ltrim($sExtension, '.');
        $this->_loader()->addPath($sDirectory, $sNamespace);
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
        if(isset($this->aExtensions[$sNamespace]))
        {
            $sViewName .= $this->aExtensions[$sNamespace];
        }

        // Render the template
        return trim($this->_renderer()->render($sViewName, $store->getViewData()), " \t\n");
    }
}
