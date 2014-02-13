SUGGEST INPUT NETTE TUTORIAL
============================
This tutorial is for Nette framework users, the others must adjust it for 
their needs.

This version works with Nette 2.1. There are older version working with Nette 2.0 and 0.9, too. Just check https://github.com/peci1/Nette-SuggestInput/releases .



INSTALLATION
============
Just copy /libs/SuggestInput, /www/js and /www/css to your nette project

File list:
```
/app
     /presenters
         SuggesterTestPresenter.php - an example presenter showing the usage
     /templates
         /SuggesterTest
             default.phtml
/libs       //You can copy this libs dir everywhere in your project
    /SuggestInput
        SuggestInput.php
        license.txt
        /Suggesters
            ISuggester.php
            ArraySuggester.php
            DbSuggester.php
            ConstantSuggester.php
        /docs - this help :) and other help files - you don't need to copy them
/www
     /js
         jquery.suggest.js
         jquery.bgiframe.js
         suggestInput.js
     /css
         suggest.css
```

If you do not use RobotLoader, you need to include SuggestInput.php to your 
program. 

Don't forget to call 

    Nette\Forms\Form::extensionMethod('Nette\Forms\Form::addSuggestInput', 'Nette\Addons\SuggestInput::addSuggestInput');

from your bootstrap or `BasePresenter::__construct()`.

Include the following JS libraries in the page layout (in the order as follows):
 *  jQuery 1.10
 *  jQuery.bgiframe 3.0 (included in package)
 *  jQuery.suggest 1.2 modified for SuggestInput (included in package)
 *  netteForms.js (part of Nette 2.0)
 *  suggestInput.js (included in package)
And include the sample css file in the page layout
 * suggest.css



USING SUGGEST INPUT
===================
You will need a presenter's action returning the suggested items as JSON array
(they are loaded via AJAX (or, better AJAJ - Asynchonous Javascript And JSON))

So you will need a suggester object - the object that searches some "blackbox"
and returns the matching items.
The same suggester should be used in the action
and should be given to the control (if you need to give one).

You can choose from 3 predefined suggesters:
 * ArraySuggester - you provide an array of items and the suggester searches
    the array for matching items
 * DbSuggester - searches a database table for matching items 
    (uses Nette\Database)
 * ConstantSuggester - always suggests the same items (provided as array)
Or you can write your own suggester, but then please implement ISuggester



THE SIMPLEST CASE
=================
In the simplest case you just call

    ->addSuggestInput('uniqueId', 'Label', $this->link('suggest'))

on a Form you create.

This supposes you create an action called `actionSuggest()`. The action uses
a suggester, which you can create in the component factory 
`createComponentSuggester()`.

See the example presenter for more examples.



LIVE DEMO
=========
You can try out a live demo at http://suggest-input.php5.cz/suggester-test/
