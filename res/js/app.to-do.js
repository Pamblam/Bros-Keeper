
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
				_this.app.removeHTML('modal', 'to-do');
				_this.drawItems();
			});
		});
	};
	
	p.appendTDItem = function($list, item){
		let parent_id = $list.prop('id');
		let subtitle = item.completed ? "<span style=color:green>(Completed "+item.completed+")</span>" : "<span style=color:red>(Pending)</span>";
		let html = new showdown.Converter().makeHtml(item.desc);
		let tags = '<span class="badge badge-info">'+item.tags.join('</span> <span class="badge badge-info">')+'</span>';
		let template = `<div class="card" style=padding:3px;margin-top:3px>
				<div class="card-header" id="todo_header_${item.id}">
					<h5 class="mb-0">
						<a class=collapsed data-toggle="collapse" data-parent="#${parent_id}" href="#collapse_td_${item.id}" aria-expanded="true" aria-controls="collapse_td_${item.id}">
							${item.name}
						</a>
					</h5>
					<div style='font-size:0.6em' class=float-right>${subtitle}</div>
				</div>
				<div id="collapse_td_${item.id}" class="collapse show" role="tabpanel" aria-labelledby="todo_header_${item.id}">
					<div class="card-block">
						${html}
						<div>${tags}</div>
						<div class=children></div>
					</div>
				</div>
			</div>`;
		template = $(template);
		template.find("img").addClass("img-fluid");
		if(item.children.length){
			$childList = $("<ul style=list-style-type:none>");
			this.appendTDItem($childList, item.children);
			template.find(".children").append($childList);
		}
		$list.append(template);
	};
	
	p.drawItems = function(){
		let _this = this;
		return new Promise(done=>{
			_this.app.bk.getTodos().then(todos=>{
				if(!todos.data.length){
					$("#to-do-list-items").html('<i>Nothing here...</i>');
					done();
					return;
				}
				let $list = $('<div id="accordion-top-level" role="tablist" aria-multiselectable="true">');
				for(let i = 0; i < todos.data.length; i++)
					_this.appendTDItem($list, todos.data[i]);
				$("#to-do-list-items").empty().append($list);
				done();
			});
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
			let subtitle = completed ? "<span style=color:green>(Completed "+formatDate(new Date(), "Y-m-d")+")</span>" : "<span style=color:red>(Pending)</span>";
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