PUGXGeneratorBundle
===================

[![Total Downloads](https://poser.pugx.org/pugx/generator-bundle/downloads.png)](https://packagist.org/packages/pugx/generator-bundle)

[![knpbundles.com](http://knpbundles.com/PUGX/PUGXGeneratorBundle/badge-short)](http://knpbundles.com/PUGX/PUGXGeneratorBundle)


This bundle is an extension of [SensioGeneratorBundle](https://github.com/sensio/SensioGeneratorBundle).

> ⚠  This bundle is abandoned and no longer maintained. Use [MakerBundle](https://github.com/symfony/maker-bundle) instead.

It adds many functionality on top of it, and corrects some minor issues:

* main block name customizable in layout
* `@ParamConverter` in actions
* different format for dates/times/datetimes
* include relation fields in show and index templates
* shorter form names
* real entity names instead of "$entity" in actions and templates
* translated texts
* support for form themes (customizable)
* default templates suitable with Boostrap and Font Awesome
* nice "check" icons for boolean fields (when using Font Awesome)
* support for pagination (requires [KnpPaginatorBundle](https://github.com/KnpLabs/KnpPaginatorBundle))
* support for filters (requires [LexikFormFilterBundle](https://github.com/lexik/LexikFormFilterBundle))
* support for sorting
* better generated tests
* fixtures generation

Documentation
-------------

[Read the documentation](Resources/doc/index.md)

License
-------

This bundle is released under the LGPL license. See the [complete license text](Resources/meta/LICENSE).

About
-----

PUGXGeneratorBundle is a [PUGX](https://github.com/PUGX) initiative.

See also
--------

For screenshots examples, see [PUGXGeneratorBundleSandbox](https://github.com/garak/PUGXGeneratorBundleSandbox).
