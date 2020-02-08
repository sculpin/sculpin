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

* Mention [@getsculpin](https://twitter.com/getsculpin) on Twitter
* Participate in [Sculpin's GitHub Project](https://github.com/sculpin/sculpin)
  * A great way to get started in helping with the Sculpin project is by 
    contributing to the documentation in the [sculpin.io repository](https://github.com/sculpin/sculpin.io/)
  * For support questions, please add issues to the github repository's
    [issues page](https://github.com/sculpin/sculpin/issues)

Not Invented Here
-----------------

There are other fine projects from which Sculpin has been inspired. If you are 
looking for a stable project with an established community, try something from 
the following list:

 * [Jigsaw](https://jigsaw.tighten.co/) &mdash; PHP & Laravel-based static site generator
 * [Jekyll](https://jekyllrb.com/) &mdash; Ruby
 * [Hugo](https://gohugo.io/) &mdash; Go-based static site generator
 * [GatsbyJS](https://www.gatsbyjs.org/) &mdash; JS-based static site generator and JAMstack orchestration tool
