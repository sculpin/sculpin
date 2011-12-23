Sculpin - Static Site Generator
===============================

Sculpin is a static site generator written in PHP.

Requirements
------------

 * PHP 5.3+

Getting Started
---------------

Clone the official repository. Or fork and clone your fork. Your call.

    git clone https://github.com/dflydev/sculpin.git

Dependencies are managed by the most excellent [Composer](http://packagist.org/).
From inside your freshly cloned Sculpin, run the following commands to get
Composer and install Sculpin's dependencies.

    wget http://getcomposer.org/composer.phar
    php composer.phar install

For development, either get used to typing `/path/to/sculpin/bin/sculpin`
or create an alias. For example:

    alias sculpin=~/workspaces/sculpin/bin/sculpin

To get started quickly, create a new directory for your Sculpin project
and initialize it with the `init` command.

    mkdir mysite
    cd mysite
    sculpin init

To generate the site, issue the `generate` command.

    sculpin generate

License
-------

Sculpin is licensed under the New BSD License - see the LICENSE file for details.

Not Invented Here
-----------------

There are other fine projects that are more mature than this one.
If you are looking for a stable project with an established community,
try something on the following admittedly incomplete list:

 * [Jekyll](http://github.com/mojombo/jekyll) &mdash; Ruby
 * [Hyde](http://ringce.com/hyde) &mdash; Python
 * [Phrozn](http://phrozn.info) &mdash; PHP
 * [Octopress](http://octopress.org) &mdash; Ruby (framework on top of Jekyll)
