<?php

namespace Jaxon\Twig;

use Jaxon\Sentry\Interfaces\View as ViewInterface;
use Jaxon\Sentry\View\Store;

class View implements ViewInterface
{
    use \Jaxon\Sentry\View\Namespaces;

    /**
     * Render a view
     * 
     * @param Store         $store        A store populated with the view data
     * 
     * @return string        The string representation of the view
     */
    public function render(Store $store)
    {
        $sViewName = $store->getViewName();
        $sNamespace = $store->getNamespace();
        // For this view renderer, the view name doesn't need to be prepended with the namespace.
        $nNsLen = strlen($sNamespace) + 2;
        if(substr($sViewName, 0, $nNsLen) == $sNamespace . '::')
        {
            $sViewName = substr($sViewName, $nNsLen);
        }

        // View namespace
        $this->setCurrentNamespace($sNamespace);

        // Render the template
        $xRenderer = new \Twig_Environment(new \Twig_Loader_Filesystem($this->sDirectory), array(
            'cache' => __DIR__ . '/../cache',
        ));
        return trim($xRenderer->render($sViewName . $this->sExtension, $store->getViewData()), " \t\n");
    }
}
