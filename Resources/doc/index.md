PUGXGeneratorBundle Documentation
=================================

This version of the bundle requires Symfony 2.3 or newer.
For Symfony 2.2, please switch to 2.2 branch.

A small note on branches and tags: version numbers are not necessarily consistent
with Symfony ones. We know we should have started versioning by something like ``0.1`` or
``1.1``, but we cannot change that decision now, for compatibility issues.


## Table of contents

1. [Download PUGXGeneratorBundle](#1-download-pugxgeneratorbundle)
2. [Enable the Bundle](#2-enable-the-bundle)
3. [Usage](#3-usage)
4. [Layout](#4-layout)
5. [Pagination](#5-pagination)
6. [I18n](#6-i18n)
7. [Filters](#7-filters)
8. [Sorting](#8-sorting)
9. [Fixtures](#9-fixtures)
10. [Target bundle](#11-target-bundle)
11. [Cleanup](#11-cleanup)

### 1. Download PUGXGeneratorBundle

**Using composer**

Run composer to download the bundle:

``` bash
$ php composer.phar require pugx/generator-bundle:2.4.* --dev
```

Notice that if your composer.json requires "sensio/generator-bundle", you can delete it (since
it is already required by "pugx/generator-bundle").

### 2. Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    // ...
    if (in_array($this->getEnvironment(), array('dev', 'test'))) {
        // ...
        $bundles[] = new PUGX\GeneratorBundle\PUGXGeneratorBundle();
    }
}
```

### 3. Usage

This bundle brings a new command, ``pugx:generate:crud``, that is similar to ``doctrine:generate:crud``.
You can get help, like any other Symfony command, just typing

``` bash
$ php app/console pugx:generate:crud --help
```

### 4. Layout

This bundle is ready to be used with [Bootstrap](http://getbootstrap.com/) and
with [Font Awesome](http://fortawesome.github.com/Font-Awesome/).
Please note that current supported versions are Boostrap 3 and Font Awesome 4. If you use
older versions, please use branch 2.3 of PUGXGeneratorBundle.

So, you can download Bootstrap and Font Awesome in your bundle, or use a CDN.
Then, you can use a simple layout, like this one:

``` html+php
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>{% block title 'My admin' %}</title>
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        {% block stylesheets %}
            <link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
            <link href="//netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.css" rel="stylesheet">
        {% endblock %}
    </head>
    <body>
        <nav class="navbar navbar-fixed-top">
            <!-- put your nav bar here -->
        </nav>
        <div class="container">
            {% block body '' %}
        </div>
        {% block javascripts %}
            <script src="//code.jquery.com/jquery-2.1.1.js"></script>
            <script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
        {% endblock %}
    </body>
</html>
```

If you want the confirm delete functionality, you can add the following JavaScript code,
based on jQuery, in one of you files (e.g. ``acme.js``):

``` js
$(document).ready(function() {
    'use strict';
    /* delete confirm */
    $('form#delete').submit(function (e) {
        var $form = $(this), $hidden = $form.find('input[name="modal"]');
        if ($hidden.val() === '0') {
            e.preventDefault();
            $('#delete_confirm').modal('show');
            $('#delete_confirm').find('button.btn-danger').click(function () {
                $('#delete_confirm').modal('hide');
                $hidden.val('1');
                $form.submit();
            });
        }
    });
});
```

If you want more consistent boostrap forms, you can use a theme, like the one provided
in [Symfony 2.6](https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/bootstrap_3_layout.html.twig)

If you're using a previous Symfony version, you can copy the theme file in a location
like ``src/Acme/DemoBundle/Resources/views/Form/theme.html.twig``, then
you can use the ``--theme`` option of ``pugx:generate:crud`` command, like in this example:

``` bash
$ php app/console pugx:generate:crud \
    --entity=AcmeDemoBundle:Entity \
    --layout=AcmeDemoBundle::layout.html.twig \
    --theme=AcmeDemoBundle:Form:theme.html.twig \
    --with-write
```

If you prefer to use such theme for all your forms, you can instead change your
configuration:

```yaml
# app/config.yml
twig:
    form:
        resources: ['src/Acme/DemoBundle/Resources/views/Form/theme.html.twig']
```

### 5. Pagination

You likely want to use pagination in your modules.
If so, add [KnpPaginatorBundle](https://github.com/KnpLabs/KnpPaginatorBundle)
to your bundles and use ``--use-paginator`` flag in ``pugx:generate:crud`` command.

### 6. I18n

Generated templates support I18n. If you want to translate texts, you should enable
translation in your configuration:

```yaml
# app/config.yml
framework:
    # ...
    translator:      { fallback: "%locale%" }
```

Then you should create a translation file, in your preferred format.
Messages catalogue is named "admin". Here is an example of a working translation file
in YAML format, for Italian language:

```yaml
# src/Acme/DemoBundle/Resources/translations/admin.it.yml
"%entity% creation":             "Creazione %entity%"
"%entity% edit":                 "Modifica %entity%"
"%entity% list":                 "Elenco %entity%"
Actions:                         Azioni
Back to the list:                Torna alla lista
Confirm delete:                  Conferma eliminazione
Create:                          Crea
Create a new entry:              Crea nuovo
Delete:                          Elimina
"Do you want to proceed?":       "Procedere?"
Edit:                            Modifica
edit:                            modifica
Filter:                          Filtra
No:                              No
Reset filters:                   Azzera filtri
show:                            vedi
"Show/hide filters":             "Mostra/nascondi filtri"
this procedure is irreversible:  questa procedura è irreversibile
Yes:                             Sì
You are about to delete an item: Si sta per eliminare un elemento
```

### 7. Filters

If you want to use filters (like the ones in the old symfony 1 admin generator), add
[LexikFormFilterBundle](https://github.com/lexik/LexikFormFilterBundle) to your bundles.
Then, use the ``--with-filter`` flag in ``pugx:generate:crud`` command.

Since filters require some additional methods in generated controllers, moving them to
a generic ``Controller`` class (and extending it instead of Symfony default one)
could be a good idea.

To enable automatic opening/closing of filters, based on session, you can add following
code to your Javascript:

``` js
$(document).ready(function () {
    'use strict';
    /* filter icon */
    $('button.filter').click(function () {
        var $icon = $(this).find('i'), target = $(this).attr('data-target');
        if ($icon.length) {
            if ($(target).height() > 0) {
                $icon.attr('class', 'fa fa-angle-down');
            } else {
                $icon.attr('class', 'fa fa-angle-right');
            }
        }
    });
});
```

There is a known limitation for generation of relations in filter form class, so you
need to adapt field configuration by hand.

### 8. Sorting

You can add sorting in columns, by using ``--with-sort`` flag in ``pugx:generate:crud`` command.
If you do so, instead of simple labels, table headers will contain links to toggle sorting
ascending and descending.

### 9. Fixtures

You can generate some fixtures by using something like ``--fixtures=2``, when ``2`` is the
number of objects that will be generated in fixtures class (can be any number greater than 0).
If your entity has some relations, references need to be adapted.
For now, there is no support for ``DependentFixtureInterface``.

### 10. Target bundle

If you want to generate your CRUD inside a bundle that is not the same bundle as your
entity, you can use the ``--dest`` option:

``` bash
$ php app/console pugx:generate:crud --entity=AcmeDemoBundle:Foo --dest=AcmeAnotherBundle
```

### 11. Cleanup

As already mentioned in [filters section](#7-filters), if you run more than one generation, it
could be a good idea to refactor procteted methods in controllers to an abstract class, to avoid
duplicate code.
If you find yourself repeating generating many CRUDs, you can also copy templates from
``skeleton`` directory (inside this bundle) to ``app\Resources\PUGXGeneratorBundle\skeleton``
(in your project).

Also, since it's not easy to always generate correct spaces, because they depend on dynamic names,
another good idea could be running a coding standard fixer, like the
[SensioLabs one](http://cs.sensiolabs.org/).
