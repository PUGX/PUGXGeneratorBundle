PUGXGeneratorBundle Documentation
=================================

This version of the bundle requires Symfony 2.1 or higher.

## Installation

1. [Download PUGXGeneratorBundle](#1-download-pugxgeneratorbundle)
2. [Enable the Bundle](#2-enable-the-bundle)
3. [Usage](#3-usage)
4. [Layout](#4-layout)
5. [Pagination](#5-pagination)
6. [I18n](#6-i18n)

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

This bundle is ready to be used with [Bootstrap](http://twitter.github.com/bootstrap/) and with [Font Awesome](http://fortawesome.github.com/Font-Awesome/)

So, you can download Bootstrap (and, optionally, Font Awesome) and put it in your bundle.
Then, you can use a simple layout, like this one:

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
        <script src="http://code.jquery.com/jquery.min.js"></script>
        {% javascripts
            '@AcmeDemoBundle/Resources/public/js/bootstrap.js'
            '@AcmeDemoBundle/Resources/public/js/acme.js'
        %}
        <script type="text/javascript" src="{{ asset_url }}"></script>
        {% endjavascripts %}
    </body>
</html>
```

If you want the confirm delete functionality, you can add the following Javascript code
in one of you files (e.g. ``acme.js`` in layout above):

``` js+php
$().ready(function() {
    /* delete confirm */
    $('form#delete').submit(function(e) {
        var $form = $(this);
        var $hidden = $form.find('input[name="modal"]');
        if ($hidden.val() != 1) {
            e.preventDefault();
            $('#delete_confirm').modal('show');
            $('#delete_confirm').find('button.btn-danger').click(function() {
                $('#delete_confirm').modal('hide');
                $form.find('input[name="modal"]').val(1);
                $form.submit();
            });
        }
    });
});
```

If you want more consistent boostrap forms, you can use a theme like this one:

```jinja
{% block form_row %}
{% spaceless %}
    <div class="control-group{% if errors|length > 0 %} error{% endif %}{% if form.get('type') == 'time' %} input-append bootstrap-timepicker-componen{% endif %}">
        {{ form_label(form) }}
        {{ form_widget(form, {'attr': {'class': form.get('type') == 'time' ? 'timepicker-default input-small' : ''}}) }}
        {{ form_errors(form) }}
        {% if form.get('type') == 'time' %}
            <span class="add-on"><i class="icon-time"></i></span>
        {% endif %}
    </div>
{% endspaceless %}
{% endblock form_row %}

{% block form_errors %}
{% spaceless %}
    {% if errors|length > 0 %}
        {% for error in errors %}
            <span class="help-inline">{{
                error.messagePluralization is null
                    ? error.messageTemplate|trans(error.messageParameters, 'validators')
                    : error.messageTemplate|transchoice(error.messagePluralization, error.messageParameters, 'validators')
            }}</span>
        {% endfor %}
    {% endif %}
{% endspaceless %}
{% endblock form_errors %}
```

If you put such theme file in ``src/Acme/DemoBundle/Resources/views/Form/form_errors.html.twig``,
you can use the ``--theme`` option of ``pugx:generate:crud`` command, like in this example:

``` bash
$ php app/console pugx:generate:crud --entity=AcmeDemoBundle:Entity --with-write --layout=AcmeDemoBundle::layout.html.twig --theme=AcmeDemoBundle:Form:form_errors.html.twig
```

### 5. Pagination

You likely want to use pagination in your modules.
If so, add [KnpPaginatorBundle](https://github.com/KnpLabs/KnpPaginatorBundle)
to your bundles and use ``--use-pagination`` flag in ``pugx:generate:crud`` command.

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
Back to the list:                Torna alla lista
Confirm delete:                  Conferma eliminazione
Create:                          Crea
Create a new entry:              Crea nuovo
Delete:                          Elimina
"Do you want to proceed?":       "Procedere?"
Edit:                            Modifica
edit:                            modifica
No:                              No
show:                            vedi
this procedure is irreversible:  questa procedura è irreversibile
Yes:                             Sì
You are about to delete an item: Si sta per eliminare un elemento
```
