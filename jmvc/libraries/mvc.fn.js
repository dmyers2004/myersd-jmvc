/**
 * jQuery MVC Framework for Client Side Interaction
 *
 * @package jQueryMVC
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/GPL-2.0
 * @link
 * @version 0.0.4
 * @author Don Myers donmyers@projectorangebox.com
 * @copyright Copyright (c) 2010
 * requires jQuery 1.7
*/
/* jQuery Extend */
/* create a wrapper for $.postJSON(); - uses post instead of get as in $.getJSON(); */
jQuery.extend({
  postJSON: function (url, data, callback) {
    return jQuery.post(url, data, callback, 'json');
  }
});

/* jQuery Functions chainable where appropriate */
jQuery.fn.extend({
	/*
	Does this object exist in the DOM?
	if ($("#selector).exists()) {
	  // do something
	}
	*/
	exists: function () {
	  return (jQuery(this).length > 0);
	},

	/*
	attach a event and data to a item in 1 step
	$("#id").mvcAction('click',function() { alert('welcome'); }, {});
	event = click,mouseover,change,keyup
	func = indexController.action1.click() or func = function() { alert('welcome'); };
	optional
	data = json object
	*/
	mvcAction: function (event, func, data) {
	  if (data) {
	    jQuery(this).mvcData(data);
	  }
	  jQuery(this).mvcEvent(event,func);
	},

	/*
	replace html in selector with template loaded if nessary
	jQuery('#movieList2').mvcView('template',data);
	*/
	mvcView: function (template,data) {
	  // phrase and render the template
	  jQuery(this).html(mvc.load.view(template,data));
	},

	/*
	Get everything
	var object = $("#selector").mvcData();
	
	Get value by name
	var value = $("#selector").mvcData("name");
	
	Set Value by name
	$("#selector").mvcData("name","value");
	*/
	mvcData: function(name, value) {
	  /* GET return Object if both empty */
	  if (!name && !value) {
	    return jQuery(this).data(); /* jQuery data */
	  }
	  
	  /* GET if value is empty then they are asking for a property by name */
	  if (!value) {
	    return jQuery(this).data(name);
	  }
	
	  /* SET if name & value set */
	  if (name && value) {
			jQuery(this).data(name,value);
			return this;
	  } 
	},
	
	/*
	var events = $("#element").mvcEvents(); - returns object with variables as the event names
	$("#mvcClick").mvcEvent(true); - clear all events chainable
	*/
	mvcEvents: function(clear) {
		if (clear && exists) {
  		jQuery(this).off().css('cursor', '');
  		return this;
		} else if (exists && (jQuery(this).length != 0)) {
			if (jQuery().jquery > 1.8) {
				return jQuery._data(jQuery(this)[0],'events');
			} else {
				return jQuery(document).data('events');
			}
		}
		
		return {};
	},
	
	/*
	Generic Event Set/Get
	
	var bol = $("#mvcClick").mvcEvent('click'); - does it have this event?
	
	$("#mvcClick").mvcEvent('click',null); - clear click even
	
	var func = function() { alert("Attached a new event"); };
	$("#mvcClick").mvcEvent('mouseover',func); - attach a function
	
	$('#mvcClick").mvcEvent('click',function() { alert('event') });
	
	*/
	mvcEvent: function(event, func) {
		/* if event is valid and function is empty return if event exists */
		if (event && !func) {
		  //var events = jQuery(this).mvcEvents();
		  var mvchasevent = jQuery(this).mvcEvents();
		  return (!!mvchasevent[event]);
		}

	  /* if event is valid and function is an object clear event */		
		if (event && func === {}) {
		  jQuery(this).off(event);
		  return this;
		}

		/* if event is valid and function is valid set the event and function */
		if (event && func) {
		  /* SET event and function */
		  jQuery(this).on(event,function(evnt) {
		    if (mvc.config.preventDefault) {
		      evnt.preventDefault();
		    }
		    mvc.trigger = jQuery(this);
		    mvc.triggerObject = evnt;
		    mvc.data = (jQuery(this).data()) || {}; /* jQuery data */
		    mvc.exec(func);
		  }).css('cursor', mvc.config.cursor);
		  return this;
		}

	}
	
}); /* end mvc jQuery Functions */