
function BrosKeeper(){}

BrosKeeper.prototype.login = function(email, password){
	return this.api({
		action: "check_login",
		email: email,
		pass: password
	});
};

BrosKeeper.prototype.api = function(data){
	return new Promise(done=>{
		let body = new FormData();
		for (let n in data) if (data.hasOwnProperty(n)) body.append(n, data[n]);
		fetch('./app/api.php', {
			method: 'post',
			body: body
		})
		.then(resp => resp.json())
		.then(done);
	});
};