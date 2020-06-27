---
title: testing webpack manifest
---

Testing CSS {{ webpack_manifest['build/css/app.css']|default('/build/css/app.css') }}
Testing JS {{ webpack_manifest['build/js/app.js']|default('/build/js/app.js') }}

