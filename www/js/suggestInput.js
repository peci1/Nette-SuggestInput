$(function() {
	$('.suggestInput').each(function() {
		var suggestLink = this.getAttribute('data-nette-suggestLink');
		var jsonOptions = this.getAttribute('data-nette-suggestInput-options');
		var options = jsonOptions ? eval('(' + jsonOptions + ')') : null;
		$(this).suggest(suggestLink, options);
	});
});

Nette.validators.suggestedOnly = function(elem, arg, val) {
	var suggestLink = elem.getAttribute('data-nette-suggestLink');
	var suggestions = null;
	$.ajax({url: suggestLink, data: {typedText: val}, async: false, success: function(response) {
		suggestions = eval(response);
	}});

	for (var i in suggestions) {
		var suggestion = suggestions[i];
		if ($.trim(suggestion) == val)
			return true;
	}

	return false;
}
