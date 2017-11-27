
(()=>{
	
	app.prototype['to-do'] = function(app){
		this.app = app;
		this.editor = null;
		let _this = this;
		this.drawItems().then(()=>{
			_this.setEvents();
		});
	};
	
	let p = app.prototype['to-do'].prototype;
	
	p.setEvents = function(){
		this.setAddItemHandler();
		this.setCloseTodoModalHandler();
		this.setSaveItemHandler();
	};
	
	p.setCloseTodoModalHandler = function(){
		let _this = this;
		$(document).on('click', '.close-todo-modal', function(){
			_this.app.closeModals();
			_this.removeHTML('modal', 'to-do');
		});
	};
	
	p.setSaveItemHandler = function(){
		let _this = this;
		$(document).on('click', '.saveTodoItem', function(){
			let parent = null,
				title = $("#todo-title-input").val(),
				details_md = _this.editor.getValue(),
				due_date = $("#due-date-picker").val(),
				completed = $("#to-do-detail-completed-input").is(":checked"),
				tags = $("#todo-item-tags-input").tagsinput('items');
			if(title.trim() == '') title = 'Untitled';
			_this.app.bk.addTodo(parent, title, details_md, due_date, completed, tags).then(()=>{
				_this.app.closeModals();
				_this.removeHTML('modal', 'to-do');
				_this.drawItems();
			});
		});
	};
	
	p.drawItems = function(){
		return new Promise(done=>{
			// do stuff to draw the items on the to-do list
			done();
		});
	};
	
	p.setAddItemHandler = function(){
		let _this = this;
		$(document).on("click", ".add-todo-item", function(e){
			e.preventDefault();
			_this.app.loadHTML('modal', 'to-do').then($d=>{
				$d.modal({
					show: true,
					backdrop: false,
				});
				$('a[href="#edit-todo-item-tab"]').tab('show');
				_this.editor = CodeMirror.fromTextArea($("#edit-todo-details-input")[0], {
					lineNumbers: false,
					theme: "elegant"
				});
				$('#due-date-picker').datepicker();
				$('#to-do-detail-completed-input').bootstrapToggle({
					on: '<i class="fa fa-check" aria-hidden="true"></i> Completed',
					off: '<i class="fa fa-cogs" aria-hidden="true"></i> Pending',
					size: "small"
				});
				$('#todo-item-tags-input').tagsinput();
				_this.setAddImageHandler();
				_this.setPreviewHandler();
			});
		});
	};
	
	p.setPreviewHandler = function(){
		let _this = this;
		$('a[href="#preview-todo-item-tab"]').on('show.bs.tab', function(e){
			let title = $("#todo-title-input").val(),
				details_md = _this.editor.getValue(),
				completed = $("#to-do-detail-completed-input").is(":checked"),
				due_date = $("#due-date-picker").val(),
				tags = $("#todo-item-tags-input").tagsinput('items');
			if(title.trim() == '') title = 'Untitled';
			let subtitle = completed ? "<span style=color:green>(Completed "+formatDate(new Date(), "m/d/y")+")</span>" : "<span style=color:red>(Pending)</span>";
			if(!completed && due_date !== "") subtitle = "(Pending - Due "+due_date+")";
			let html = new showdown.Converter().makeHtml(details_md);
			tags = '<span class="badge badge-info">'+tags.join('</span> <span class="badge badge-info">')+'</span>';
			let template = `<div class="card" style=padding:3px;margin-top:3px>
				<div class="card-block">
					<h4 class="card-title">${title}</h4>
					<h6 class="card-subtitle mb-2 text-muted">${subtitle}</h6>
					${html}
					<div>${tags}</div>
				</div>
			</div>`;
			template = $(template);
			template.find("img").addClass("img-fluid");
			$("#preview-todo-item-tab").html(template);
		});
	};
	
	p.setAddImageHandler = function(){
		let _this = this;
		$("#todo-add-image").fileUpload({
			multi: true,
			url: "./app/api.php?action=upload_img",
			uploaded: resp=>{
				$("#todo-add-image").fileUpload("clearFiles");
				val = _this.editor.getValue();
				for(let i=0; i<resp.data.length; i++)
					val += "\n![enter image description here]("+resp.data[i]+")";
				_this.editor.getDoc().setValue(val);
			},
			change: ()=>$("#todo-add-image").fileUpload('upload')
		});
	};
	
})();