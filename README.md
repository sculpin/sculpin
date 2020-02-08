Sculpin - PHP Static Site Generator
===================================

[![License](https://poser.pugx.org/sculpin/sculpin/license.svg)](https://packagist.org/packages/sculpin/sculpin)
[![Build Status](https://travis-ci.org/sculpin/sculpin.svg?branch=develop)](https://travis-ci.org/sculpin/sculpin)
[![Total Downloads](https://poser.pugx.org/sculpin/sculpin/downloads.svg)](https://packagist.org/packages/sculpin/sculpin)

Sculpin takes data sources such as text files (Markdown, Textile, etc.) and
transforms them using Twig templates to produce a set of static HTML files that
can be deployed to almost any hosting platform.

Visit [sculpin.io](https://sculpin.io) for more information.

Documentation
-------------

Sculpin documentation can be found at [https://sculpin.io/documentation](https://sculpin.io/documentation/),
and documentation for the Twig template  language can be found at [https://twig.symfony.com](https://twig.symfony.com/)

There is also a collection of Sculpin "skeletons" to help you hit the ground 
running with your next Sculpin website.

Sculpin Skeletons
-----------------

Skeletons are starting points for a new Sculpin based site. They include the 
basic structure of a site, such as Twig-based layout files and the supporting 
templates for generating pagination interfaces and listing your content by 
custom-defined categories.

In Sculpin, a metadata property such as a category or tag is called a "taxonomy",
and the Blog skeletons below also provide taxonomy layouts to help you organize 
and categorize your content in multiple ways.

While you can always start a site from scratch, using a skeleton is a good way
to get a bunch of structure in place with little or no effort.

Unless otherwise noted, the following skeletons are **barebones**. They have
minimal styling and design, and are intended to help you get started with adding 
all of your own style and flavor.

 * [Blog Skeleton](https://github.com/sculpin/sculpin-blog-skeleton)
   A minimal Sculpin based blog, based on an older version of Bootstrap CSS.
 * [Tailwind Blog Skeleton](https://github.com/beryllium/sculpin-tailwind-blog-skeleton)
   A basic Sculpin-based blog, with updated styling and configuration in place
   based on the Tailwind utility-first CSS framework.
 * [Tailwind Landing Page Skeleton](https://github.com/beryllium/sculpin-tailwind-landing-skeleton)
   Ideal for a Company or Product website, this skeleton focuses on a single
   product page as an example - no blog in sight. The design is responsive and 
   mobile-friendly, thanks to TailwindCSS.

Skeletons can be combined with the `composer create-project` command to clone
Sculpin and install dependencies at the same time:

```
# Bootstrap Blog

composer create-project sculpin/blog-skeleton my-blog

# Tailwind Blog

composer create-project beryllium/sculpin-tailwind-blog-skeleton my-blog

# Tailwind Product Landing Page

composer create-project beryllium/sculpin-tailwind-landing-skeleton my-company
```

License
-------

MIT, see [LICENSE](/LICENSE).


Community
---------

Want to get involved? Here are a few ways:

* Find us in the **#sculpin** IRC channel on **irc.freenode.org**.
* Join the [Sculpin Users](http://groups.google.com/group/sculpin-users)
  mailing list.
* Mention [@getsculpin](http://twitter.com/getsculpin) on Twitter.


Not Invented Here
-----------------

There are other fine projects from which Sculpin has been inspired. Many are
more mature than this one. If you are looking for a stable project with an
established community, try something on the following admittedly incomplete
list:

 * [Jekyll](http://jekyllrb.com/) &mdash; Ruby
 * [Octopress](http://octopress.org) &mdash; Ruby (framework on top of Jekyll)
 * [Hyde](http://hyde.github.io/) &mdash; Python
 * [Phrozn](https://github.com/Pawka/phrozn) &mdash; PHP
 * [Pie Crust](https://github.com/ludovicchabant/PieCrust) &mdash; PHP
 * [Pie Crust 2](http://bolt80.com/piecrust) &mdash; Python
