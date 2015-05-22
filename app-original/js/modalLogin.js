var login = function(){
	var loginFunc = $.Deferred(function(){
		$.when(    
			//Load all the stuff needed for modal window
		    $.getScript( "/bootstrap/js/bootstrap.min.js" ),
		    $.getScript("/js/hello.min.js"),
		    $.Deferred(function( deferred ){
		        $( deferred.resolve );
		})
		).done(function(){
			//Build the modal window
			//Include the styles
			$('head').append('<link rel="stylesheet" id="bootstrap" type="text/css" href="/bootstrap/css/bootstrap.css">');
			//$('head').append('<link rel="stylesheet" id="zocial" type="text/css" href="/zocial/css/zocial.css">');
			//$('head').append('<link rel="stylesheet" id="awesome" type="text/css" href="/font-awesome/css/font-awesome.min.css">');
			//Build the div
			var modalDiv = document.createElement('div');
			$(modalDiv).addClass('modal fade');
			$(modalDiv).attr('id', 'loginDialog');
			$(modalDiv).attr('tabindex', '-1');
			var modalHeader = document.createElement('div');
			$(modalHeader).addClass('modal-header');
			$(modalHeader).append('<h3><i class="fa fa-group"></i>Login</h3>');
			var modalBody = document.createElement('div');
			$(modalBody).addClass('modal-body');
			$(modalBody).css({"margin":"10px 0 0 20px"});
			var contentDiv = document.createElement('div');
			$(contentDiv).addClass('span5 pull-center');
			var errorDiv = document.createElement('div');
			$(errorDiv).attr('id', 'errorMsg');
			$(errorDiv).addClass('row alert alert-error');
			$(errorDiv).text('You need to login to perform this action.');
			var providersDiv = document.createElement('div');
			$(providersDiv).addClass('row');
			var providers = new Array("facebook", "github", "google");
			$.each( providers, function( key, value ) {
				  var button = document.createElement('button');
				  $(button).css({"margin-right":"20px","font-size":"25px"});
				  $(button).addClass('et-icon '+value);
				  $(button).attr('title','Login with '+value);
				  $(button).click(function(){
					  hello(value).login();
				  });
				  providersDiv.appendChild(button);
			});
			var modalFooter = document.createElement('div');
			$(modalFooter).addClass('modal-footer');
			$(modalFooter).append('<a href="#" class="btn" onclick="closeDialog ();">Cancel</a>');
			//Put it all together
			contentDiv.appendChild(errorDiv);
			contentDiv.appendChild(providersDiv);
			modalBody.appendChild(contentDiv);
			modalDiv.appendChild(modalHeader);
			modalDiv.appendChild(modalBody);
			modalDiv.appendChild(modalFooter);
			$(modalDiv).appendTo('body');
			$(function() { $('#loginDialog').bind('hidden', function () { cleanup();loginFunc.reject("Fail"); });} );
			//Call the handler
			$.when(loginHandler()).then(function(result){
				cleanup();
				loginFunc.resolve("Success");
			}, function(err){
				loginFunc.reject("Fail");
			});
		});
	});
	return loginFunc.promise();
}

var loginHandler = function(){
	var loginHandlr = $.Deferred(function(){
		hello.init({ 
			facebook :'222928341227986',
			github  : '9b06740c143c32dbf9a6',
			google   : '183201897579-aasuedofjtnem8aktuq528lv271u3gtq.apps.googleusercontent.com'
		});
		hello.logout();
		$("#loginDialog").modal('show');
		hello.on('auth.login', function(auth){
			// call user information, for the given network
			hello( auth.network ).api( '/me' ).success(function(r){
				//Now that the user has logged in post the credentials 
				var userData;
				userData= {
					    first :r.first_name,
					    last : r.last_name,
					    name : r.name,
					    provider :auth.network,
					    userAuthId :r.id,
					    email :r.email,
					    accessToken :auth.authResponse.access_token
					};

					$.ajax({
					  url: "/controller.php",
					  data: userData,
					  type: 'post',
					  success: function(data) {
					    $('#loginDialog').modal('hide');
					    loginHandlr.resolve("Success");
					  },
					  error: function(error){
						$("#errorMsg").text(error.responseText.replace(/"/g, ""));
						loginHandlr.reject("Failed");
					  }
				});	
			});
		});
	});
	return loginHandlr.promise();
}
var cleanup = function(){
	$('#loginDialog').remove();
	$('#bootstrap').remove();
	$('#zocial').remove();
	$('#awesome').remove();
	$('.modal-backdrop fade').remove();
	
}
function closeDialog () {
	$('#loginDialog').modal('hide'); 
}

