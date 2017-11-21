
function BrosKeeper(){}

BrosKeeper.prototype.login = function(email, password){
	return this.api({
		action: "check_login",
		email: email,
		pass: password
	});
};

BrosKeeper.prototype.addTodo = function(parent, title, desc, due, completed, tags){
	return this.api({
		action: "add_todo",
		parent: parent,
		title: title,
		desc: desc,
		due: due,
		completed: completed,
		tags: tags
	});
};

BrosKeeper.prototype.api = function(data){
	return new Promise(done=>{
		let body = new FormData();
		for (let n in data) if (data.hasOwnProperty(n)) body.append(n, data[n]);
		fetch('./app/api.php', {
			method: 'post',
			body: body,
			credentials: 'include'
		})
		.then(resp => resp.json())
		.then(done);
	});
};