<?php

namespace Jaxon\Twig;

use function Jaxon\jaxon;

jaxon()->di()->getViewRenderer()
    ->addRenderer('twig', fn() => new View());
