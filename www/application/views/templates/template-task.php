{{=<% %>=}}
<li class="list-group-item task-item{{#status}} active{{/status}}" data-task-id="{{id}}">
	<div class="row align-items-center">
		<div class="col-auto">
			<div class="image opener">
				<img src="{{image}}" alt="Изображение задачи" width="30" height="30">
			</div>
		</div>
		
		<div class="col">
			<div class="title">{{name}}</div>
			
			{{#tags.0}}
			<div class="tags">
				{{#tags}}<div class="tag">{{.}}</div>{{/tags}}
			</div>
			{{/tags.0}}
		</div>

		<div class="col-auto">
			<button type="button" class="btn btn-sm btn-primary task-edit" data-task-id="{{id}}">Ред.</button>
		</div>
	</div>
</li>
<%={{ }}=%>