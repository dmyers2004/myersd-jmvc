/**
 * jQuery MVC Framework for Client Side Interaction
 *
 * @package jQueryMVC
 * @license Creative Commons Attribution License http://creativecommons.org/licenses/by/3.0/legalcode
 * @link
 * @version 0.0.4
 * @author Don Myers donmyers@projectorangebox.com
 * @copyright Copyright (c) 2010
 * requires jquery 1.4.2+ and jquery.cookie.js
 * 
 * Created to mimic php session functions
 * Caution This uses Cookies so it it visible on the "wire"
 * 
 */

/* setup session variables */
mvc.config.session_uuid = mvc.unique + '_user_uuid'
mvc.config.session_id = mvc.unique + '_session_id';
mvc.config.session_data = mvc.unique + '_session_data';

jQuery.session_start = function() {
  /* setup if not alreay setup or push forward 1 year if it is */
  if (!jQuery.cookie(mvc.config.session_uuid)) {
    jQuery.cookie(mvc.config.session_uuid, (new Date().getTime()) + '-' + php.uniqid('',true), { expires: 365, path: '/' });
  } else {
    jQuery.cookie(mvc.config.session_uuid, jQuery.cookie(mvc.config.session_uuid), { expires: 365, path: '/' });
  }

  /* set the browser session */
  if (!jQuery.cookie(mvc.config.session_id)) {
    jQuery.cookie(mvc.config.session_id, php.uniqid('',true), { path: '/' });
  }

  /* let's read and cache our session data */
  var jsontxt = jQuery.cookie(mvc.config.session_data);
  if (!jsontxt) {
    window.mvc_session = {};
  } else {
    window.mvc_session = jQuery.secureEvalJSON(jsontxt);
  }
  
  return jQuery.cookie(mvc.config.session_id);
};

jQuery.session_uuid = function() {
  return jQuery.cookie(mvc.config.session_uuid);
};

jQuery.session_id = function() {
  return jQuery.cookie(mvc.config.session_id);
};

jQuery.session_regenerate_id = function(delete_old_session) {
  if (delete_old_session) {
    jQuery.cookie(mvc.config.session_data, null, { path: '/' });
  }
  jQuery.cookie(mvc.config.session_id, php.uniqid('',true),{ path: '/' });
  return true;
};

jQuery.session_destroy = function() {
  jQuery.cookie(mvc.config.session_id, null,{ path: '/' });
  jQuery.cookie(mvc.config.session_data, null,{ path: '/' });
  return true;
};

jQuery.session_uuid_destroy = function() {
  jQuery.cookie(mvc.config.session_uuid, null,{ path: '/' });
  return true;
};

jQuery.session = function(name, value) {
  /* cached in window object */
  if (!name && !value) {
    return window.mvc_session;
  } else if (!value) {
    return window.mvc_session[name];
  } else {
    window.mvc_session[name] = value;
    jQuery.cookie(mvc.config.session_data,jQuery.toJSON(window.mvc_session),{ path: '/' });
    return true;
  }
};

