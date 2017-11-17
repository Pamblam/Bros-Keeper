
(()=>{
	
	app.prototype['to-do'] = function(app){
		this.app = app;
		this.setEvents();
		this.editor = null;
	};
	
	let p = app.prototype['to-do'].prototype;
	
	p.setEvents = function(){
		this.setAddItemHandler();
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
			});
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