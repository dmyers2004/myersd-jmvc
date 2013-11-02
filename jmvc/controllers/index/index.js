mvc.controller_index_method_index = {

  __construct: function () {
  	mvc.log('controller_index_method_index __construct');
  },
  
  login: {
  	click: function() {
			mvc.user.name = $('#name').val();
			var password = $('#password').val();
			mvc.user.password = sha256(password);

  		var prepped = mvcUserPrep(mvc.user.name,password);		
  		jQuery.post(mvc.base_url + 'main/login' ,prepped , function(data, textStatus, jqXHR){
  			if (data.status == 200) {
  				/* transfer the key to the next page not using cookies! */
  				mvcUserSave();
  				/*
  				store the PHP session variable so the backend knows who we are
  				setting it this was isn't a security issue be it's really no different then
  				PHP setting the value and passing it. It's just sending it and we are setting it manually
  				if there is no matching session on the backend it will send you to the login page anyway
  				ie. no session = not logged in
  				*/
					$.cookie('PHPSESSID', data.session);
					mvc.user.valid = true;
					mvc.redirect('main/welcome');
  			} else {
					mvc.user.valid = false;
  			}
  		}, 'json');
  		
  	}
  }
};
