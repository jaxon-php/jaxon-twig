<?php

jaxon()->sentry()->addViewRenderer('twig', function () {
    return new Jaxon\Twig\View();
});
