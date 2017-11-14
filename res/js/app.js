
function app(){
	this.bk = new BrosKeeper();
	this.setGlobalEventHandlers();
}

app.prototype.check_session = function(){
	let _this = this;
	return new Promise(done=>{
		_this.bk.api({action:"check_session"}).then((resp)=>{
			if(!resp.success) _this.login().then(done);
			else done();
		});
	});
};

app.prototype.logout = function(){
	let _this = this;
	return new Promise(done=>{
		_this.bk.api({action:"logout"}).then(resp=>location.reload());
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
				_this.loginModalSubmit().then(done);
			});
		});
	});
};

app.prototype.loginModalSubmit = function(){
	let _this = this;
	return new Promise(done=>
		_this.bk.login($("#login-email-input").val(), $("#login-password-input").val())
			.then(resp=>{
				if(!resp.success) $("#login-error-alert").html(resp.response).slideDown();
				else {
					_this.closeModals();
					_this.removeHTML('modal', 'login');
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
			url: "./res/html/"+type+"s/"+view+".html",
			data: {_: new Date().getTime()}
		}).done(html=>{
			let $d = $(html).prop("id", type+"-"+view);
			switch(type){
				case "modal": $d.appendTo("body"); break;
				case "page": $("#page-container").html($d); break;
			}
			done($d);
		});
	});
};

app.prototype.closeModals = function(){
	$('.modal').modal('hide');
	$('body').removeClass('modal-open');
	$('.modal-backdrop').remove();
	$('.modal').remove();
};


////////////////////////////////////////////////////////////////////////////////
// Events //////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

app.prototype.setGlobalEventHandlers = function(){
	this.setLogoutHandler();
	this.setPageLinkHandler();
};

app.prototype.setLogoutHandler = function(){
	let _this = this;
	$(document).on("click", ".log-out", function(){
		_this.logout();
	});
};

app.prototype.setPageLinkHandler = function(){
	let _this = this;
	$(document).on("click", "[data-page-link]", function(e){
		e.preventDefault();
		$(".navbar-collapse").collapse('hide');
		let page = $(this).data("page-link");
		_this.loadHTML('page', 'to-do').then(()=>{
			if(_this[page] !== undefined) new _this[page](_this);
		});
	});
};