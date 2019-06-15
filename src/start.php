<?php

jaxon()->di()->getViewManager()->addRenderer('twig', function () {
    return new Jaxon\Twig\View();
});
