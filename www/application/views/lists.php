<div class="wrapper">
	<div class="container">
		<div class="row justify-content-between align-items-center">
			<div class="col-12 col-sm-auto">
				<h1 class="h4 mb-0">Мои списки <span class="badge badge-secondary ml-2">{{lists_count}}</span></h1>
			</div>

			<div class="col-12 mt-3 col-sm-auto mt-sm-0">
				<button type="button" class="btn btn-block btn-success" data-toggle="modal" data-target="#modal-list">Создать новый список</button>
			</div>
		</div>
	</div>

	<div class="container mt-4">
		{{^lists}}<p>В данный момент у Вас нет ни одного TODO-списка.</p>{{/lists}}
		
		{{#lists}}
		<div class="card mb-4">
			<div class="card-body">
				<div class="row justify-content-between align-items-center mb-3">
					<div class="col-12 col-sm">
						<h2 class="h5 card-title mb-0">{{title}}</h2>
					</div>

					<div class="col-12 mt-2 col-sm-auto mt-sm-0">
						<p class="card-text"><small class="text-muted">Выполнено: {{count.completed}} из {{count.all}}</small></p>
					</div>
				</div>
				
				{{#description}}
				<p class="card-text">{{description}}</p>
				{{/description}}

				{{#access}}{{^owner}}
				<p class="mt-3"><i>Вам доступно: {{#read}}<b>чтение</b>{{/read}}{{#read}}{{#edit}} и {{/edit}}{{/read}}{{#edit}}<b>изменение</b>{{/edit}}.</i></p>
				{{/owner}}{{/access}}

				<div class="row justify-content-between align-items-center mt-3">
					<div class="col">
						<p class="card-text"><small class="text-muted">Создан <span class="tooltips" data-time="{{created}}">{{created}}</span></small>, <small class="text-muted">обновлён <span class="tooltips" data-time="{{updated}}">{{updated}}</span></small></p>
					</div>

					<div class="col-auto">
						<a href="/lists/{{id}}" class="btn btn-sm btn-primary">Подробнее</a>
					</div>
				</div>
			</div>
		</div>
		{{/lists}}
	</div>
</div>