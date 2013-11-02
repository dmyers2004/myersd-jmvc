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
basic - add/update hidden
$("#form_id").mvcFormHidden('primary',23);
*/
jQuery.fn.mvcFormHidden = function (name, value) {
  return this.each(function () {
    if (jQuery('#' + name).length > 0) {
      jQuery('#' + name).attr('value', value);
    } else {
      jQuery('<input />').attr('type', 'hidden').attr('id', name).attr('name', name).val(value).appendTo(this);
    }
  });
};

/*
Convert Form to JSON Object
basic
$("#form_id").mvcForm2Obj();
advanced - add additional payload
$("#form_id").mvcForm2Obj({'extra':'abc123'});
*/
jQuery.fn.mvcForm2Obj = function(obj) {
  obj = obj || {};

  /* convert form to json object */
  jQuery.each(jQuery(this).serializeArray(), function () {
    if (obj[this.name]) {
      if (!obj[this.name].push) {
        obj[this.name] = [obj[this.name]];
      }
      obj[this.name].push(this.value || '');
    } else {
      obj[this.name] = this.value || '';
    }
  });

  obj.mvcForm2Obj = {};

  obj.mvcForm2Obj.mvc_post_selector = this.selector;
  obj.mvcForm2Obj.mvc_url = mvc.self;
  obj.mvcForm2Obj.mvc_application_folder = mvc.folders.application;

  return jQuery.mvcClone(obj);
};

/*
Send form to url from form's action attribute + validation_url setting ie action="/controller/submit" url = "/controller/submit_validate"
$("#form_id").mvcFormValidate();

Send the form id as json to given url
$("#form_id").mvcFormValidate('url');

Send the form id as json to given url submit the form on true (if mvc_model_valid = true)
$("#form_id").mvcFormValidate('url',true);

Send the form id as json to given url submit the form on true (if mvc_model_valid = true) with extra payload
$("#form_id").mvcFormValidate('url',true,{'extra':'abc123'});

Send the form id as json to given url submit the form on true (if mvc_model_valid = true) with extra payload
run merge when complete
$("#form_id").mvcFormValidate('url',true,{'extra':'abc123'},true);
*/

jQuery.fn.mvcFormValidate = function (url, submit, json, merge) {
/*
validate this against some back end php via ajax
pass back a json object with and array for the view [key] = value
and variable mvc_model_valid = true/false
if the json is not returned or invalid the form is considered invalid (valid = false)
*/
  submit = submit || mvc.validation.autosubmit;
  url = url || jQuery(this).attr('action') + mvc.validation.append2url;
  json = json || {};
  merge = merge || mvc.validation.merge;

  //mvc.ajax_responds = jQuery.mvcAjax(url,jQuery(this).mvcForm2Obj(json),'json',true);
  mvc.vars.data = jQuery(this).mvcForm2Obj(json);

  mvc.ajax_responds = jQuery.mvcAjax({'url': url,'data': mvc.vars.data });

  if (mvc.ajax_responds !== null) {
    jQuery.exec(mvc.ajax_responds.mvc_post_ajax);
    if (merge) {
      jQuery.mvcMerge(mvc.ajax_responds);
    }
    jQuery.exec(mvc.ajax_responds.mvc_post_merge);
    if (mvc.ajax_responds.mvc_model_valid === true) {
      jQuery.exec(mvc.ajax_responds.mvc_post_valid);
      if (submit) {
        jQuery.exec(mvc.ajax_responds.mvc_pre_submit);
        jQuery(this).unbind('submit').submit(); /* if returned false (no errors) then submit the form */
      }
    }
    return (mvc.ajax_responds.mvc_model_valid === true) ? true : false;
  } else {
    return false;
  }
};

/*
basic - change the url of the form action
$("#form_id").mvcFormAction('new url');
*/
jQuery.fn.mvcFormAction = function (url) {
  return this.each(function () {
    jQuery(this).attr('action', url);
  });
};
