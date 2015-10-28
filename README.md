tud_newsscraper
===============

A tool to extract data from websites of TU Dresden. This is currently in use on [fsr-verkehr.de](http://www.fsr-verkehr.de/) as a ticker on its homepage.


Usage
-----

Basically there are two php files `view/news.php` and `view/calendar.php` which both emit HTML-snippets that contain a rendered version of all the feeds defined in `model/news/faculties/*`, respectively `model/calendar/faculties/*`. For the case of [fsr-verkehr.de](http://www.fsr-verkehr.de/) these snippets are then dynamically added to the DOM via AJAX (jQuery).

(Partial) documentation of the individual functions is available through PHPDoc comments in the code.


Requirements
------------

* PHP 5.3+
* PHP cURL
* non-Windows OS due to the use of `strptime()` 
