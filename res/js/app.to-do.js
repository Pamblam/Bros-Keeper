
(()=>{
	
	app.prototype['to-do'] = function(app){
		this.app = app;
		this.setEvents();
	};
	
	let p = app.prototype['to-do'].prototype;
	
	p.setEvents = function(){
		this.setAddItemHandler();
	};
	
	p.setAddItemHandler = function(){
		let _this = this, editor;
		$(document).on("click", ".add-todo-item", function(){
			_this.app.loadHTML('modal', 'to-do').then($d=>{
				$d.modal({
					show: true,
					backdrop: false,
				});
				$('a[href="#edit-todo-item-tab"]').tab('show');
				editor = CodeMirror.fromTextArea($("#edit-todo-details-input")[0], {
					lineNumbers: false,
					theme: "elegant"
				});
				$('#due-date-picker').datepicker();
				$('#to-do-detail-completed-input').bootstrapToggle({
					on: '<i class="fa fa-check" aria-hidden="true"></i> Completed',
					off: '<i class="fa fa-cogs" aria-hidden="true"></i> Pending'
				});
				$('#todo-item-tags-input').tagsinput();
			});
		});
	};
	
})();