Delegator.register('click','SearchRequest', function(event, el, api) {
		if ( el.get('tag') == 'button')
			event.stop();
		var form = el.form;
		form.ajaxRequest({
			evalScripts : false,
			onSuccess : function() {
				var updates = ['.search-scopes','.search-results .an-entities-wrapper'];
				var html  = this.response.html.parseHTML();
				updates.each(function(selector){
					var newEl = html.getElement(selector);
					var oldEl = document.getElement(selector);
					if ( oldEl ) {
						newEl ? newEl.replaces(oldEl) : oldEl.remove();
					}
				});
			}
		}).send();
});
Delegator.register('click','ChangeScope', function(event, el, api) {
		event.stop();
		el.getParent('ul').getElements('li').removeClass('active');
		el.getParent('li').addClass('active');
		el.ajaxRequest({
			evalScripts : false,
			onSuccess : function() {				
				document.getElement('.search-results')
					.set('html', this.response.html)
			}
		}).send();
});	