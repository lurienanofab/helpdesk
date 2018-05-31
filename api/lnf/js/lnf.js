var lnf = {
	"root": '/helpdesk/api/lnf/',
	
	"isOnLoginPage": function(args){
		var a = $.extend({}, {'yes': function(body){}, 'no': function(){}}, args);
		var body = $('body');
		if (body.length > 0 && body.attr('id') == 'loginBody'){
			a.yes(body);
			return true;
		}
		else{
			a.no();
			return false;
		}
	},

	"ajax": function(data, callback){
		$.ajax({
			url: lnf.root+'ajax.php',
			type: 'POST',
			data: data,
			dataType: 'json',
			success: function(data){
				if (typeof callback == 'function')
					callback(data);
			}
		});
	},
	
	"loginCheck": function(callback){
		if (typeof callback != 'function')
			callback = lnf.onLoginCheck;
		lnf.ajax({'command': 'user-check'}, function(data){
			callback(data);
		});
	},
	
	"loginRedirect": function(logout){
		window.location = lnf.root+'login.php'+((logout)?'?logout=1':'');
	},
	
	"onLoginCheck": function(data){
		if (!data.authenticated){
			alert('You are no longer logged into LNF Online Services. Please log in again to continue.');
			lnf.loginRedirect(true);
		}
	},
	
	"tableSearch": function(args){
		$('.dtable th').each(function(){
			var th = $(this);
			$.each(args, function(x, search){
				var condition = search['if'];
				var callback = search['then'];				
				if (condition(th)){
					callback(th.closest('table'), th.index());
					return true;
				}
				else
					return false;
			});
		});
	}
}