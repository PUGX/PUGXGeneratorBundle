PUGXGeneratorBundle Documentation
=================================

This version of the bundle requires Symfony 2.1 or higher.

## Installation

1. Download PUGXGeneratorBundle
2. Enable the Bundle
3. Usage
4. Additional stuff

### 1. Download PUGXGeneratorBundle

**Using composer**

Add the following lines in your composer.json:

```
{
    "require": {
        "pugx/generator-bundle": "dev-master"
    }
}

```

Now, run the composer to download the bundle:

``` bash
$ php composer.phar update pugx/generator-bundle
```

### 2. Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new PUGX\GeneratorBundle\PUGXGeneratorBundle(),
    );
}
```

### 3. Usage

This bundle brings a new command, ``pugx:generate:crud``, that is similar to ``doctrine:generate:crud``.
You can get help, like any other Symfony command, just typing

``` bash
$ php app/console pugx:generate:crud --help
```

### 4. Additional stuff

This bundle is ready to be used with [Bootstrap](http://twitter.github.com/bootstrap/) and with [Font Awesome](http://fortawesome.github.com/Font-Awesome/)

So, you can download Bootstrap (and, optionally, Font Awesome) and put it in your bundle.
Then, you can use a simple layout, like this:

``` html+php
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>{% block title %}{% endblock %}</title>
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        {% stylesheets
            '@AcmeDemoBundle/Resources/public/css/bootstrap.css'
            '@AcmeDemoBundle/Resources/public/css/font-awesome.css'
            '@AcmeDemoBundle/Resources/public/css/bootstrap-responsive.css'
        %}
        <link rel="stylesheet" href="{{ asset_url }}">
        {% endstylesheets %}
        {% block stylesheets %}
        {% endblock %}
    </head>
    <body>
        <div class="navbar navbar-fixed-top">
            <!-- put your nav bar here -->
        </div>
        <div class="container">
            {% block body %}
            {% endblock %}
        </div>
        {% javascripts
            '@AcmeDemoBundle/Resources/public/js/jquery-1.8.3.min.js'
            '@AcmeDemoBundle/Resources/public/js/bootstrap.js'
        %}
        <script type="text/javascript" src="{{ asset_url }}"></script>
        {% endjavascripts %}
    </body>
</html>
```
