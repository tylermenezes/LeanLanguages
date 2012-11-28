LeanLanguages
==============

A teeny internationalization (i18n) library.

Introduction
------------
> I18n is a simple concept, and it should be simple to set up and use.

Requirements
------------
 * PHP &ge; 5.3.5

Use
===

Getting Started
---------------

Call `LeanLanguages\Language::start($language_path, $language, $cache_dir)`, where `$language_path` is the path to
your languages directory, `$language` is the language name (e.g. `en_us`), and `$cache_dir` is an optional path to
a writable directory to use as a cache.

Creating Language Files
-----------------------

Languages are stored in folders based on their name, e.g. `en_us` = '/en/us', and saved in .lng files. All files inside
the directory are processed (including those in subdirectories), and namespaced (e.g. a file called `pages/test` will
have all its string identifiers prefixed with `pages test`).

The one exception to namespacing, is that a file called `main.lng` will be unnamespaced.

A language file looks as such:

    title: Cats on Parade!
    message: Result
        success: Success!
        fail: Failure!
    cats:
        tabby: A tabby cat!
        mystery: A mystery cat!

(Remember: because of the prefixing, if this were stored in a 'page cats.lng' file, to access the title you'd have to
call `page cats title`.)

Printing Internationalized Strings
----------------------------------

Call `__g($identifier)` to get the string, or `__e($identifier)` to echo it directly.

Fancy Stuff
-----------

Internationalized strings can reference other internationalized strings using double brackets, e.g:

    tm
        html: &#153;
        text: (tm)

    name: {{name html}}
        html: MySite{{tm html}}
        text: MySite{{tm text}}

You must use the fully namsepaced name when referring to these strings, even in the same file.

You can also pass arguments into the strings using standard sprintf formatting, and passing information into `__g`/`__e`
paramater-wise. You can pass arguments in translations using the pipe character, e.g. `{{demos ex1|test}}`.

Planned Features
================

 * Language inheritance
