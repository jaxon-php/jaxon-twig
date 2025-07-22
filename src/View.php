<?php

namespace Jaxon\Twig;

use Jaxon\App\View\Store;
use Jaxon\App\View\ViewInterface;
use Jaxon\Script\Call\JxnCall;
use Jaxon\Script\JsExpr;
use Twig\Environment as Twig;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

use function Jaxon\attr;
use function Jaxon\jaxon;
use function Jaxon\je;
use function Jaxon\jo;
use function Jaxon\jq;
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
     * @param array $events
     *
     * @return string
     */
    private function setJxnEvent(array $events): string
    {
        return isset($events[0]) && is_array($events[0]) ?
            attr()->events($events) : attr()->event($events);
    }

    /**
     * @return void
     */
    private function createTwigFunctions(): void
    {
        // Functions for Jaxon js and CSS codes
        $this->xRenderer->addFunction(new TwigFunction('jxnCss',
            fn() => jaxon()->css(), ['is_safe' => ['html']]));
        $this->xRenderer->addFunction(new TwigFunction('jxnJs',
            fn() => jaxon()->js(), ['is_safe' => ['html']]));
        $this->xRenderer->addFunction(new TwigFunction('jxnScript',
            fn(bool $bIncludeJs = false, bool $bIncludeCss = false) =>
                jaxon()->script($bIncludeJs, $bIncludeCss), ['is_safe' => ['html']]));

        // Filters for custom Jaxon attributes
        $this->xRenderer->addFilter(new TwigFilter('jxnHtml',
            fn(JxnCall $xJxnCall) => attr()->html($xJxnCall), ['is_safe' => ['html']]));
        $this->xRenderer->addFilter(new TwigFilter('jxnBind',
            fn(JxnCall $xJxnCall, string $item = '') => attr()->bind($xJxnCall, $item), ['is_safe' => ['html']]));
        $this->xRenderer->addFilter(new TwigFilter('jxnPagination',
            fn(JxnCall $xJxnCall) => attr()->pagination($xJxnCall), ['is_safe' => ['html']]));
        $this->xRenderer->addFilter(new TwigFilter('jxnOn',
            fn(JsExpr $xJsExpr, string $event) => attr()->on($event, $xJsExpr), ['is_safe' => ['html']]));
        $this->xRenderer->addFilter(new TwigFilter('jxnClick',
            fn(JsExpr $xJsExpr) => attr()->click($xJsExpr), ['is_safe' => ['html']]));
        $this->xRenderer->addFilter(new TwigFilter('jxnEvent',
            fn(array $events) => $this->setJxnEvent($events), ['is_safe' => ['html']]));

        // Functions for custom Jaxon attributes
        $this->xRenderer->addFunction(new TwigFunction('jxnHtml',
            fn(JxnCall $xJxnCall) => attr()->html($xJxnCall), ['is_safe' => ['html']]));
        $this->xRenderer->addFunction(new TwigFunction('jxnBind',
            fn(JxnCall $xJxnCall, string $item = '') => attr()->bind($xJxnCall, $item), ['is_safe' => ['html']]));
        $this->xRenderer->addFunction(new TwigFunction('jxnPagination',
            fn(JxnCall $xJxnCall) => attr()->pagination($xJxnCall), ['is_safe' => ['html']]));
        $this->xRenderer->addFunction(new TwigFunction('jxnOn',
            fn(string $event, JsExpr $xJsExpr) => attr()->on($event, $xJsExpr), ['is_safe' => ['html']]));
        $this->xRenderer->addFunction(new TwigFunction('jxnClick',
            fn(JsExpr $xJsExpr) => attr()->click($xJsExpr), ['is_safe' => ['html']]));
        $this->xRenderer->addFunction(new TwigFunction('jxnEvent',
            fn(array $events) => $this->setJxnEvent($events), ['is_safe' => ['html']]));

        $this->xRenderer->addFunction(new TwigFunction('jq', fn(...$aParams) => jq(...$aParams)));
        $this->xRenderer->addFunction(new TwigFunction('je', fn(...$aParams) => je(...$aParams)));
        $this->xRenderer->addFunction(new TwigFunction('jo', fn(...$aParams) => jo(...$aParams)));
        $this->xRenderer->addFunction(new TwigFunction('rq', fn(...$aParams) => rq(...$aParams)));
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

            $this->createTwigFunctions();
        }
        return $this->xRenderer;
    }

    /**
     * @inheritDoc
     */
    public function addNamespace(string $sNamespace, string $sDirectory, string $sExtension = ''): void
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
