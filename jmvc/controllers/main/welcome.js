mvc.controller_main_method_welcome = {

  __construct: function () {
  	mvc.log('mvc.controller_main_method_welcome');
  },

	aesbutton: {
		click: function() {
			var post = {};
			var aes = $('#aes').val();
			var aeskey = mvc.user.password;
			post.aes = Aes.Ctr.encrypt(aes,aeskey,128);
			jQuery.post(mvc.base_url + 'main/aes' ,post , function(data, textStatus, jqXHR){
				$('#output').html(data.reply);
			}, 'json');
		}
	}

};
