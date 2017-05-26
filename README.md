Jaxon View for Twig
===================

Render Twig templates in Jaxon applications.

Installation
------------

Install this package with Composer.

```json
"require": {
    "jaxon-php/jaxon-twig": "~2.0"
}
```

Usage
-----

Foreach directory containing Twig templates, add an entry to the `app.views` section in the configuration.

```php
    'app' => array(
        'views' => array(
            'demo' => array(
                'directory' => '/path/to/demo/views',
                'extension' => '.html.twig',
                'renderer' => 'twig',
            ),
        ),
    ),
```

In the application classes, this is how to render a view in this directory.

```php
    $this->view()->render('/sub/dir/file');
```

Read the [views documentation](https://www.jaxon-php.org/docs/armada/views.html) to learn more about views.
