PUGXGeneratorBundle
===================

[![Total Downloads](https://poser.pugx.org/pugx/generator-bundle/downloads.png)](https://packagist.org/packages/pugx/generator-bundle)

[![knpbundles.com](http://knpbundles.com/PUGX/PUGXGeneratorBundle/badge-short)](http://knpbundles.com/PUGX/PUGXGeneratorBundle)


This bundle is an extension of [SensioGeneratorBundle](https://github.com/sensio/SensioGeneratorBundle).

It adds many functionality on top of it, and corrects some minor issues:
* pages with layout (main block name is customizable)
* forms in correct namespace (under Type, not under Form)
* @ParamConverter in actions
* different format for dates/times/datetimes
* include relation fields in show and index templates
* shorter form names
* real entity names instead of "$entity" in actions and templates
* translated texts
* support for form themes (customizable)
* default templates suitable with Boostrap and Font Awesome
* nice "check" icons for boolean fields (when using Font Awesome)
* support for pagination (requires [KnpPaginatorBundle](https://github.com/KnpLabs/KnpPaginatorBundle))
* (still experimental) support for filters (requires [LexikFormFilterBundle](https://github.com/lexik/LexikFormFilterBundle))
* (still experimental) support for sorting

Documentation
-------------

[Read the documentation](Resources/doc/index.md)

License
-------

This bundle is released under the LGPL license. See the [complete license text](Resources/meta/LICENSE).

About
-----

PUGXGeneratorBundle is a [PUGX](https://github.com/PUGX) initiative.


Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/PUGX/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
with PUGXGeneratorBundle installed, to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.
