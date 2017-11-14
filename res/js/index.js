$(()=>{
	const fp = new app();
	fp.check_session().then(()=>{
		// User is logged in
	});
});
