Jaxon View for Twig
===================

Render Twig templates in Jaxon applications.

Installation
------------

Install this package with Composer.

```json
"require": {
    "jaxon-php/jaxon-twig": "^5.0"
}
```

Usage
-----

For each directory containing Twig templates, add an entry to the `app.views` section in the configuration.

```php
    'app' => [
        'views' => [
            'demo' => [
                'directory' => '/path/to/demo/views',
                'extension' => '.html.twig',
                'renderer' => 'twig',
            ],
        ],
    ],
```

In the following example, the DOM element with id `content-id` is assigned the value of the `/path/to/demo/views/sub/dir/file.html.twig` template.

```php
class MyClass extends \Jaxon\App\FuncComponent
{
    public function action()
    {
        $this->response->html('content-id', $this->view()->render('demo::/sub/dir/file'));
    }
}
```

Twig functions
--------------

This extension provides the following Twig functions to insert Jaxon js and css codes in the pages that need to show Jaxon related content.

```php
// /path/to/demo/views/sub/dir/file.html.twig

<!-- In page header -->
{{ jxnCss() }}
</head>

<body>

<!-- Page content here -->

</body>

<!-- In page footer -->
{{ jxnJs() }}

{{ jxnScript() }}
```

Call factories
--------------

This extension registers the following Twig functions for Jaxon [call factories](https://www.jaxon-php.org/docs/v5x/ui-features/call-factories.html) functions.

> [!NOTE]
> In the following examples, the `rqAppTest` template variable is set to the value `rq(Demo\Ajax\App\AppTest::class)`.

The `jxnBind` function attaches a UI component to a DOM element, while the `jxnHtml` function displays a component HTML code in a view.

```php
    <div class="col-md-12" {{ jxnBind(rqAppTest) }}>
        {{ jxnHtml(rqAppTest) }}
    </div>
```

The `jxnPagination` function displays pagination links in a view.

```php
    <div class="col-md-12" {{ jxnPagination(rqAppTest) }}>
    </div>
```

The `jxnOn` function binds an event on a DOM element to a Javascript call defined with a `call factory`.

```php
    <select class="form-select"
        {{ jxnOn('change', rqAppTest.setColor(jq().val())) }}>
        <option value="black" selected="selected">Black</option>
        <option value="red">Red</option>
        <option value="green">Green</option>
        <option value="blue">Blue</option>
    </select>
```

The `jxnClick` function is a shortcut to define a handler for the `click` event.

```php
    <button type="button" class="btn btn-primary"
        {{ jxnClick(rqAppTest.sayHello(true)) }}>Click me</button>
```

The `jxnEvent` function defines a set of events handlers on the children of a DOM element, using `jQuery` selectors.

```php
    <div class="row" {{ jxnEvent([
        ['.app-color-choice', 'change', rqAppTest.setColor(jq().val())]
        ['.ext-color-choice', 'change', rqExtTest.setColor(jq().val())]
    ]) }}>
        <div class="col-md-12">
            <select class="form-control app-color-choice">
                <option value="black" selected="selected">Black</option>
                <option value="red">Red</option>
                <option value="green">Green</option>
                <option value="blue">Blue</option>
            </select>
        </div>
        <div class="col-md-12">
            <select class="form-control ext-color-choice">
                <option value="black" selected="selected">Black</option>
                <option value="red">Red</option>
                <option value="green">Green</option>
                <option value="blue">Blue</option>
            </select>
        </div>
    </div>
```

The `jxnEvent` function takes as parameter an array in which each entry is an array with a `jQuery` selector, an event and a `call factory`.

Contribute
----------

- Issue Tracker: github.com/jaxon-php/jaxon-twig/issues
- Source Code: github.com/jaxon-php/jaxon-twig

License
-------

The package is licensed under the BSD license.
