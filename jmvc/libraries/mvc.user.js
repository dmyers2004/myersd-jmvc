mvcUserSave = function() {
  jQuery.jStorage.set('mvc.user',mvc.user);
}

mvcUserLoad = function() {
	mvc.user = jQuery.jStorage.get('mvc.user',{});
}

/* create secure post */
mvcUserPrep = function(name,password) {
	var key = RSA.getPublicKey(public_key);
	var ts = Math.floor((new Date()).getTime() / 1000); // UTC timestamp 
	var hmac = sha256(name + password + ts);
	var epassword = RSA.encrypt(password,key);

	var post = {};
	post.name = name;
	post.ts = ts;
	post.hmac = hmac;
	post.epassword = epassword;
	
	return post;
};