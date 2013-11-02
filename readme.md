Basic Setup Includes 

setting up host i.e. http://localhost
settings up folder /jmvc-example/

All of the other config optional

/*
console logging function if exists and debug is on
IE (no console) safe
Load it here this way it's available before the includes are loaded incase we want to log something
*/
mvc.log = function () {

/*
mvc.route(controller,method);
load controller based on controller/method
*/
mvc.route = function (controller, method) {

/*
attach a even and data to a item
$("#id").mvcAction('click',function() { alert('welcome'); }, {});
event = click,mouseover,change,keyup
func = indexController.action1.click() or func = function() { alert('welcome'); };
optional
data = json object
*/
jQuery.fn.mvcAction = function (event, func, data) {

/*
var output = mvc.view('template',movies);

Get view template, compile it, and phrase it.
name = name of the template file to load - also used as the name of the compiled template
data = phrase into the template
*/
mvc.view = function (name,data) {

/*
replace
jQuery('#movieList2').mvcView('logic',movies);
*/
jQuery.fn.mvcView = function (name,data) {
  // phrase and render the template
  jQuery(this).html(mvc.view(name,data));
};

/*
load json properties into html based on matching selectors
matches on id,class,form element name
will also run scripts mvc_pre_merge and mvc_post_merge
*/
mvc.merge = function (json) {

/*
Getters
return complete mvc data object
var value = $("#selector").mvcData(); (returns object)

return specific value
var value = $("#selector").mvcData("age"); (return value or undefined)

Setters
$("#selector").mvcData({}); (clears it out)

$("#selector").mvcData("name","value");
*/
jQuery.fn.mvcData = function (name, value) {

/*
Generic Event Set/Get


var events = $("#mvcClick").mvcEvent(); - get all the events

var bol = $("#mvcClick").mvcEvent('click'); - does it have this event?

$("#mvcClick").mvcEvent('click',{}); - clear click even

$("#mvcClick").mvcEvent({}); - clear all events

var func = function() { alert("Attached a new event"); };
$("#mvcClick").mvcEvent('mouseover',func); - attach a function

$('#mvcClick").mvcEvent('click',function() { alert('event') });

*/
jQuery.fn.mvcEvent = function (event, func) {

/*
execute code
function or string
*/
mvc.exec = function (code) {

/*
client based redirect
*/
mvc.redirect = function (url) {

/*
Does this object exist in the DOM?
if ($("#selector).exists) {
  do something
}
*/
jQuery.fn.exists = function() {

/*
create a wrapper for $.postJSON(); - uses post instead of get as in $.getJSON();
*/
jQuery.extend({

/*
More complete Ajax
$.mvcAjax({});
*/
mvc.request = function(settings) {

/*
load a external file
*/
mvc.load = function(file) {

/* external load a mvc model */
mvc.model = function(file) {

/*
this will make a copy of a object without the methods
which jack up some ajax calls and other stuff
*/
mvc.clone = function(obj) {

/* create unique id */
mvc.uuid = function () {

