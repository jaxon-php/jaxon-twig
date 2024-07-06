<?php

Jaxon\jaxon()->di()->getViewRenderer()->addRenderer('twig', function () {
    return new Jaxon\Twig\View();
});
