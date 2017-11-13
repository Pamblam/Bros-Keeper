
function app(){}

app.prototype.check_session = function(){
	let _this = this;
	return new Promise(done=>{
		this.api({action:"check_session"}).then((resp)=>{
			if(!resp.success) _this.login().then(done);
			else done();
		});
	});
};

app.prototype.login = function(){
	let _this = this;
	this.closeModals();
	return new Promise(done=>{
		_this.loadHTML('modal', 'login').then($d=>{
			$d.modal({
				show: true,
				backdrop: 'static',
				keyboard: false
			});
			$("#login-submit").click(()=>{
				_this.loginModalSubmit();
			});
		});
	});
};

app.prototype.loginModalSubmit = function(){
	let _this = this;
	return new Promise(done=>
		_this.api({
			action: "check_login",
			email: $("#login-email-input").val(),
			pass: $("#login-password-input").val()
		}).then(resp=>{
			if(!resp.success) $("#login-error-alert").html(resp.response).slideDown();
			else {
				_this.closeModals();
				//_this.removeHTML('modal', 'login');
				done();
			}
		}));
};

app.prototype.removeHTML = function(type, view){
	$("#"+type+"-"+view).remove();
};

app.prototype.loadHTML = function(type, view){
	return new Promise(done=>{
		$.ajax({
			url: "./res/html/"+type+"s/"+view+".html"
		}).done(html=>{
			let $d = $(html).prop("id", type+"-"+view).appendTo("body");
			done($d);
		});
	});
};

app.prototype.api = function(data){
	return new Promise(done=>{
		$.ajax({
			url: "./app/api.php",
			data: data
		}).done(done);
	});
};

app.prototype.closeModals = function(){
	$('.modal').modal('hide');
	$('body').removeClass('modal-open');
	$('.modal-backdrop').remove();
	$('.modal').remove();
};