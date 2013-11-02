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

/*
request all of the includes via async
and setup a handler to continue once all of the libs are loaded
*/
mvc.bootstrap = function() {
	mvc.log('bootstrap');

  /*
  create a unique application key based off the url
  i realize this isn't super secret but it's javascript what is secret?
  */
  for (mvci = 16; mvci >= 0; mvci--) {
    mvc.appid += (mvc.base_url + mvc.base_url).charCodeAt(mvci).toString(16);
  }

  /*
  set a uuid (user id) using jStorage
  this is permanent once it's set
  */
  if (mvc.config.uuid && typeof(jQuery.jStorage) == 'object') {
    mvc.uuid = jQuery.jStorage.get('uuid');
    if (!mvc.uuid) {
      mvc.uuid = mvc.create.uuid();
      jQuery.jStorage.set('uuid', mvc.uuid);
    }
  }

  /* auto route? */
  if (mvc.config.route) {
  	/* load the controller */
  	mvc.load.controller(mvc.folders.current_controller,mvc.controller,mvc.method);
  }

  /* send out the mvc object to the console */
  mvc.log(mvc);

	/* fire off the finished event */
  jQuery.holdReady(false);

};

/*
load json properties into html/DOM based on matching selectors
matches on id,class,form element name
will also run scripts mvc_pre_merge and mvc_post_merge
if sent in
*/
mvc.merge = function (json) {
  mvc.event('mvc.merge.init');
  if (json) {
    mvc.event('mvc.merge.pre');
    mvc.exec(json.mvc_pre_merge);
    for (property in json) { /* we are only using strings or numbers */
      if (typeof(json[property]) === 'string' || typeof(json[property]) === 'number' || typeof(json[property]) === 'boolean') {
        value = json[property];

        /* match classes & ids */
        jQuery('.' + property + ',#' + property).html(value);

        /* match any form element names */
        /* hidden field */
        if (jQuery('[name=' + property + ']').is('input:hidden')) {
          jQuery('input[name=' + property + ']').val(value);
        }
        /* input text */
        if (jQuery('[name=' + property + ']').is('input:text')) {
          jQuery('input[name=' + property + ']').val(value);
        }
        /* input textarea */
        if (jQuery('[name=' + property + ']').is('textarea')) {
          jQuery('textarea[name=' + property + ']').val(value);
        }
        /* input radio button */
        if (jQuery('[name=' + property + ']').is('input:radio')) {
          jQuery('input[name=' + property + '][value="' + value + '"]').attr('checked', true);
        }
        /* input checkbox */
        if (jQuery('[name=' + property + ']').is('input:checkbox')) {
          jQuery('input:checkbox[name=' + property + ']').attr('checked', (value === 1 || value === true));
        }
        /* input select */
        if (jQuery('[name=' + property + ']').is('select')) {
          jQuery('select[name=' + property + ']').val(value);
        }
      }

    }
    mvc.exec(json.mvc_post_merge);
    mvc.event('mvc.merge.post');
  }
};

/*
execute code, function, string
*/
mvc.exec = function (code) {
  if (code !== '' || code !== undefined) {
    var func = (typeof(code) === 'function') ? code : new Function(code);
    try {
      func();
    } catch (err) {
      mvc.log('MVC mvc.exec ERROR',err,code);
    }
  }
};

/*
client based redirect
*/
mvc.redirect = function (url) {
  mvc.event('mvc.redirect.pre');
  window.location.replace(url);
};

/*
MVC Ajax
$.mvcAjax({});
*/
mvc.request = function(settings) {
  mvc.event('mvc.request.pre');

  settings = settings || {};

  /* clear errors an responds */
  mvc.ajax.responds = undefined;
  mvc.ajax.jqxhr = undefined;
  mvc.ajax.textstatus = undefined;
  mvc.ajax.errorthrown = undefined;

  /* setup a few defaults in here not in the config this can be overridden via settings */
  mvc.ajax.options.success = function(responds) {
    mvc.ajax.responds = responds;
  };

  mvc.ajax.options.error = function(jqXHR, textStatus, errorThrown) {
    mvc.ajax.jqxhr = jqXHR;
    mvc.ajax.textstatus = textStatus;
    mvc.ajax.errorthrown = errorThrown;
  };

  /* merge it all together */
  complete = jQuery.extend({},mvc.ajax.options,settings);
  /* make request */
  jQuery.ajax(complete);

  mvc.event('mvc.request.post');

  /* return responds */
  return mvc.ajax.responds;
};

/*
console logging function if exists and debug is on
IE (no console) safe
Load it here this way it's available before the includes are loaded incase we want to log something
*/
mvc.log = function () {
  /* unlimited arguments */
  if (mvc.config.debug) {
    if (typeof window.console === 'object' && typeof window.console.log !== 'undefined') {
      for (var mvci = 0; mvci < arguments.length; mvci++) {
        console.log(arguments[mvci]);
      }
    }
  }
};

/*
mvc.attach(classname);
parse class name binding functions to elements
*/
mvc.attach = function(class_name) {
	mvc.log('Trying to attach: ' + class_name);
  if (mvc[class_name]) {
    var ctrlr = mvc[class_name];
    /* fire off construct */
    mvc.exec(ctrlr[mvc.config.constructor]);
    for (var elementid in ctrlr) {
      if (typeof(ctrlr[elementid]) === 'object') {
        for (var eventname in ctrlr[elementid]) {
          if (typeof(ctrlr[elementid][eventname]) === 'function') {
            /* data-mvc is now automagically attached via jquery 1.4.3+ */
            /* attach any events to matching classes and/or ids */
            jQuery('#' + elementid).mvcEvent(eventname,ctrlr[elementid][eventname]);
            if (mvc.config.attach2classes) {
            	jQuery('.' + elementid).mvcEvent(eventname,ctrlr[elementid][eventname]);
            }
          }
        }
      }
    }
  }
}

/*
this will make a copy of a object without the methods
which jack up some ajax calls and other stuff
*/
mvc.clone = function(obj) {
  clone = {};
  for (property in obj) {
    if (obj.hasOwnProperty(property)) {
      if (typeof(obj[property]) === 'object') {
        clone[property] = mvc.clone(obj[property]);
      } else {
        clone[property] = obj[property];
      }
    }
  }
  return clone;
};

/*
Wrapper to call/set jQuery document triggers.
Used as a sudo MVC hook/event system
*/
mvc.event = function(trigger) {
 	jQuery(document).trigger(trigger);
};

/* add create "namespace" */
mvc.create = {
	/*
	create unique id
	generate uuid and return it - RFC4122 v4 UUID
	*/
	uuid: function (prefix) {
	  var mvcuuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
	    var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
	    return v.toString(16);
	  }).toUpperCase();
	  return (prefix || '') + mvcuuid;
	},

	/* add mvc.create.event('mvc.something',function) */
	event: function(name,func) {
		name = name + '.' + (mvc.eventid++);
	  jQuery(document).on(name, func);
	  mvc.eventstorage[mvc.eventid] = name;
	  return mvc.eventid;
	}
};

/* add remove "namespace" */
mvc.remove = {
	/* mvc.remove.event an event based on id */
	event: function(id) {
		if (typeof(id) == 'number') {
			id = mvc.eventstorage[id];
		}
		if (id) {
		  jQuery(document).off(id);
		}
	}
};