/*
 *	jquery.suggest 1.3 - 2010-09-21
 *	Download the newest version from http://github.com/peci1/Nette-SuggestInput/blob/master/SuggestInput.zip
 *
 *	JavaScript&JQuery side of support of text input suggesting some items
 *	
 *	Uses code and techniques from following libraries:
 *	1. http://www.dyve.net/jquery/?autocomplete
 *	2. http://dev.jquery.com/browser/trunk/plugins/interface/iautocompleter.js
 *	3. http://www.vulgarisoip.com/files/jquery.suggest.js
 *
 *	The basestuff written by Peter Vulgaris (www.vulgarisoip.com)	
 *	Feel free to do whatever you want with this file
 *
 *	Edited by Martin Pecka for Nette Framework
 *
 *	Tested in FF3.5, Chrome3, IE6+, Opera
 *	Still has some general bugs when multiple inputs are displayed (showing
 *	suggestlist of the previously selected input until you type minchars chars
 *	to the newly selected - but only sometimes)
 *
 *	In Opera, selecting an item by pressing Enter key sends the form...
 *
 */

(function($) {

	$.suggest = function(input, options) {

        //we must hold separate options for each input on the page, so we 
        //initialize the properties as arrays, where the first index is holding
        //id of the input it belongs to

        if (initialized == undefined) {
            var initialized = true;

            var $results = [];
            var timeout = [];
            var prevLength = [];
            var cache = [];
            var cacheSize = [];
            var componentName = []; //FIX from http://forum.nette.org/cs/viewtopic.php?pid=23031#p23031

            var items = [];

            var itemsPerPage = [];
            var currentPage = [];
            var currentItem = [];

            var loadingText = 'Načítání...';
            var loading = [];
            loading[0] = loadingText;
        }        
        
        var id = input.id;

		var $input = $(input).attr("autocomplete", "off");

		$results[id] = $(document.createElement("ul"));
        $results[id].attr("id", 'results_' + id);

		timeout[id] = false;		// hold timeout ID for suggestion results to appear	
		prevLength[id] = 0;			// last recorded length of $input.val()
		cache[id] = [];				// cache MRU list
		cacheSize[id] = 0;			// size of cache in chars (bytes?)

        items[id] = [];             // the items that are to be displayed

        itemsPerPage[id] = options.itemsPerPage; // count of items displayed in one page
        currentPage[id] = 0;        //number of current page
        currentItem[id] = 0;        //index of currently selected item (can be inaccurate)
		
		$results[id].addClass(options.resultsClass).appendTo('body');

		resetPosition();
		$(window)
			.load(resetPosition)		// just in case user is changing size of page while loading
			.resize(resetPosition);

		$input.blur(function() {
			setTimeout(function() { $results[id].hide() }, 200);
		});
		
		// help IE users if possible
		try {
			$results[id].bgiframe();
		} catch(e) { }


		// I really hate browser detection, but I don't see any other way
		if ($.browser.mozilla)
			$input.keypress(processKey);	// onkeypress repeats arrow keys in Mozilla/Opera
		else
			$input.keydown(processKey);		// onkeydown repeats arrow keys in IE/Safari
		
        $input.focus(function() {
			suggest();
		});




        /**
         * Recalculate position of the suggest list
         * 
         * @return void
         */
		function resetPosition() {
			// requires jquery.dimension plugin
			var offset = $input.offset();
			$results[id].css({
				top: (offset.top + input.offsetHeight) + 'px',
				left: offset.left + 'px'
			});
		}
		
		
        /**
         * Process keyboard controls
         * 
         * @param e $e The event that occured
         * @return void
         */
		function processKey(e) {

			// handling up/down/escape/pgUp/pgDn requires results to be visible
			// handling enter/tab requires that AND a result to be selected

			if ((/27$|38$|40|33|34$/.test(e.keyCode) && $results[id].is(':visible')) ||
				(/^13$|^9$/.test(e.keyCode) && getCurrentResult())) {
	            
                //we do not want the list to be controllable
                if (options.noControl)
                    return;

                //prevent from doing default key actions
	            if (e.preventDefault)
	                e.preventDefault();
				if (e.stopPropagation)
	                e.stopPropagation();

                e.cancelBubble = true;
                e.returnValue = false;
			
				switch(e.keyCode) {

					case 38: // up			
                        //handle paging
                        if (currentItem[id] % itemsPerPage[id] == 0) {
                            if (currentPage[id] > 0) {
                                currentPage[id]--;
                                currentItem[id]--;
                                displayItems(items[id]);
                            } else {
                                currentItem[id] = itemsPerPage[id] - 1;
                            }
                        } else {
                            currentItem[id]--;
                        }

                        //select the result
                        prevResult();
						break;
			
					case 40: // down
                        //handle paging
                        if (currentItem[id] % itemsPerPage[id] == itemsPerPage[id] - 1 || currentItem[id] == items[id].length - 1) {
                            var numOfPages = Math.ceil(items[id].length / itemsPerPage[id]);
                            if (currentPage[id] < numOfPages - 1) {
                                currentPage[id]++;
                                currentItem[id]++;
                                displayItems(items[id]);
                            } else {
                                currentItem[id] = currentPage[id]*itemsPerPage[id];
                            }
                        } else {
                            currentItem[id]++;
                        }

                        //select the result
						nextResult();                        
						break;

					case 9:  // tab
					case 13: // return
						selectCurrentResult();
						break;
						
					case 27: //	escape
						$results.hide();
						break;

                    case 33: // page up
                        //handle paging - one page backwards
                        if (currentPage[id] > 0) {
                            currentPage[id]--;
                            currentItem[id] = itemsPerPage[id]*currentPage[id];
                            displayItems(items[id]);
                        }                        
                        break;

                    case 34: // page down
                        //handle paging - one page forwards
                        var numOfPages = Math.ceil(items[id].length / itemsPerPage[id]);
                        if (currentPage[id] < numOfPages - 1) {
                            currentPage[id]++;
                            currentItem[id] = itemsPerPage[id]*currentPage[id];
                            displayItems(items[id]);
                        }                        
                        break;

				}
				
            //handle other kepresses (eg. characters...) that changed the 
            //length of the input's value
			} else if ($input.val().length != prevLength[id]) {

                //if the suggester is constant and we have loaded the items
                //yet, do not load them again
                if (options.constant && items[id].length > 0)
                    return;

                //setup a check for the new matches
				if (timeout[id]) 
					clearTimeout(timeout[id]);
				timeout[id] = setTimeout(suggest, options.delay);
				prevLength[id] = $input.val().length;
				
			}			
				
			
		}
		
		
        /**
         * Perform the logic for getting the right matches into items[id] and
         * displaying them
         * 
         * @return void
         */
		function suggest() {

			var typedText = $.trim($input.val());

			if (typedText.length >= options.minchars) {
				
				cached = checkCache(typedText);
				
				if (cached) {
				
                    currentPage[id] = 0;
                    currentItem[id] = 0;
                    items[id] = cached['items'];
					displayItems(items[id]);
					
				} else {

                    displayItems(loading);
				
                    //FIX from http://forum.nette.org/cs/viewtopic.php?pid=23031#p23031
                    var typedTextCaption = {};
                    if (options.componentName)
                        typedTextCaption=options.componentName+"-typedText";
                    else
                        typedTextCaption="typedText";

                    var getData = {}

                    getData[typedTextCaption]=typedText;

                    //AJAX(J) GET
					$.get(options.source, getData, function(response) {

						$results[id].hide();
                        currentPage[id] = 0;
                        currentItem[id] = 0;
						
						items[id] = parseResponse(response, typedText);
						
						displayItems(items[id]);
						addToCache(typedText, items[id], response.length);
						
					});
					
				}
				
			} else {
			
				$results[id].hide();
				
			}
				
		}
		
		
        /**
         * Search cache for given typed text and return the associated items
         * 
         * @param string $typedText The text the user has typed
         * @return array|false
         */
		function checkCache(typedText) {

			for (var i = 0; i < cache[id].length; i++) {
				if (cache[id][i]['typedText'] == typedText) {
                    //move the found result to the beginning of cache
					cache[id].unshift(cache[id].splice(i, 1)[0]);
					return cache[id][0];
				}
            }

			return false;
		
		}
		
        /**
         * Add new data to cache 
         * 
         * @param string $typedText The text the user has typed
         * @param array $it Items found for the typedText
         * @param int $size Size of it in characters
         *
         * @return void
         */
		function addToCache(typedText, it, size) {

            //if the cache si full, erase it until we have enough free space
			while (cache[id].length && (cacheSize[id] + size > options.maxCacheSize)) {
				var cached = cache[id].pop();
				cacheSize[id] -= cached['size'];
			}
			
            //insert it into cache
			cache[id].push({
				typedText: typedText,
				size: size,
				items: it
				});
				
			cacheSize[id] += size;
		
		}
		
        /**
         * Create the results list items, using paging
         * 
         * @param array $it The items to display
         * @return void
         */
		function displayItems(it) {
	
			if (!it)
				return;
				
			if (!it.length) {
				$results[id].hide();
				return;
			}

            //hide all other suggestings from other inputs
            //this is IMPORTANT, if missing, some weird things happen
            $(".ac_results").hide();
			
            var firstItem = currentPage[id]*itemsPerPage[id];
            var lastItem = Math.min(
                (currentPage[id]+1)*itemsPerPage[id]-1,
                it.length-1
            );
			var html = '';

            //removing active class disables click and hover events
            liClass = "";
            if (!options.noControl)
                liClass = "active";

			for (var i = firstItem; i <= lastItem; i++)
				html += '<li class="' + liClass + '">' + it[i] + '</li>';

            //if we have more items than we want to be on a single page, 
            //display the paging toolbar
            if (it.length > itemsPerPage[id])
                html += 
                    '<li class="ac_tooltip">' + 
                        (firstItem + 1) + '-' + (lastItem + 1) + 
                        ' z ' + it.length + '; Další výsledky: PgUp, PgDown' +
                    '</li>';

			$results[id].html(html).show();
			
			$results[id]
				.children('li.active')
                .click(function(e) {
					e.preventDefault(); 
					e.stopPropagation();
					selectCurrentResult();
				})
				.mouseover(function() {
					$results[id].children('li').removeClass(options.selectClass);
					$(this).addClass(options.selectClass);
				});
						
		}
	
        /**
         * Parses the JSON response from the server
         *
         * @param string $response The response from the server
         * @param string $typedText The text the user has typed
         *
         * return array
         */
		function parseResponse(response, typedText) {			
			var items = [];
            //response is in JSON; if you prefer other format, you can modify this
			var tokens = eval(response);

			// parse returned data for non-empty items
			for (var i in tokens) {
				var token = $.trim(tokens[i]);
				if (token) {
                    //perform highlighting of the matched part
					token = token.replace(
						new RegExp(typedText, 'ig'), 
						function(typedText) { return '<span class="' + options.matchClass + '">' + typedText + '</span>' }
						);
					items[items.length] = token;
				}
			}
			
			return items;
		}
		
        /**
         * Returns the currently selected li
         * 
         * @return jQuery
         */
		function getCurrentResult() {
		            
			if (!$results[id].is(':visible'))
				return false;
		
			var $currentResult = $results[id].children('li.' + options.selectClass);
			
			if (!$currentResult.length)
				$currentResult = false;
				
			return $currentResult;

		}
		
        /**
         * Sets input's value to the value of selected item 
         * 
         * @return void
         */
		function selectCurrentResult() {

			$currentResult = getCurrentResult();
		
			if ($currentResult) {
				$input.val($currentResult.text());
				$results[id].hide();
				
				if (options.onSelect)
					window[options.onSelect].apply($input[0]); //FIX from http://forum.nette.org/cs/2769-suggestinput-aneb-naseptavac?pid=29400#p29400
			}
		
		}
		
        /**
         * Moves cursor to next item in list 
         * 
         * @return void
         */
		function nextResult() {

			$currentResult = getCurrentResult();
		
			if ($currentResult)
				$currentResult
					.removeClass(options.selectClass)
					.next('li.active')
						.addClass(options.selectClass);
			else
				$results[id].children('li:first-child').addClass(options.selectClass);
		
		}
		
        /**
         * Moves cursor to previous item in list 
         * 
         * @return void
         */
		function prevResult() {
		
			$currentResult = getCurrentResult();
		
			if ($currentResult)
				$currentResult
					.removeClass(options.selectClass)
					.prev('li.active')
						.addClass(options.selectClass);
			else {
                if (items[id].length > itemsPerPage[id])
				    $results[id].children("li:last-child").prev('li.active').addClass(options.selectClass);
                else
                    $results[id].children("li:last-child").addClass(options.selectClass);
            }
		
		}

	}
	
	$.fn.suggest = function(source, options) {
	
		if (!source)
			return;
	
		options = options || {};
		options.source = source; //URL we fetch suggestings from
		options.delay = options.delay || 100; //delay after keypress, in which we start the search
		options.resultsClass = options.resultsClass || 'ac_results'; //class for the whole suggest list
		options.selectClass = options.selectClass || 'ac_over'; //class for the selected item
		options.matchClass = options.matchClass || 'ac_match'; //class for the matched text
		if (options.minchars == undefined) { options.minchars = 3; } //minimum # of chars to invoke the search
        options.itemsPerPage = options.itemsPerPage || 5; //# of items per page
		options.onSelect = options.onSelect || false; //callback; is run after the user selects an item
        //max size of cache (per input) in characters
		if (options.maxCacheSize == undefined) { options.maxCacheSize = 65536; }
        options.noControl = options.noControl || false; //if true, user can't select items
        //if true, the first loaded item set is used for all other typed texts
        options.constant = options.constant || false; 

		this.each(function() {
			new $.suggest(this, options);
		});

		return this;
		
	};
	
})(jQuery);

/*
setup in page (done automatically if you use Nette\Forms\SuggestInput):

jQuery(function() {
    jQuery("#suggestId").suggest("{plink presenter:action} or URL",{options...});
});
*/
