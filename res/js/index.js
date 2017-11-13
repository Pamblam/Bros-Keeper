$(()=>{
	
	const fp = new app();
	fp.check_session().then(()=>{
		
		
		alert("logged in");
	});
	
});
