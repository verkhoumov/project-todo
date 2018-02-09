<div class="wrapper">
	<div class="container">
		<div class="row">
			<div class="col-12 order-1 order-md-0">
				<div class="row justify-content-between align-items-center mb-3">
					<div class="col-12 col-sm">
						<h1 class="h4 mb-0" id="list-title">{{list.title}}</h1>
					</div>

					<div class="col-12 mt-2 col-sm-auto mt-sm-0">
						<p class="text-muted mb-0">Выполнено: <span id="tasks-completed">{{list.count.completed}}</span> из <span id="tasks-count">{{list.count.all}}</span></p>
					</div>
				</div>
				
				{{#list.description}}
				<p id="list-description">{{list.description}}</p>
				{{/list.description}}

				<p class="text-muted">Создан <span class="tooltips" data-time="{{list.created}}">{{list.created}}</span>, обновлён <span class="tooltips" data-time="{{list.updated}}">{{list.updated}}</span></p>
			</div>

			<div class="col-12 order-2 col-md-7 order-md-1 col-lg-8">
				<h2 class="h5">Задачи</h2>

				<ul class="list-group tasks-list{{#access.edit}} editable{{/access.edit}}">
					<!-- См. template-task -->
					{{#tasks}}
					<li class="list-group-item task-item{{#status}} active{{/status}}" data-task-id="{{id}}">
						<div class="row align-items-center">
							<div class="col-auto">
								<div class="image opener image-loader image-loader-task">
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

							{{#access.edit}}
							<div class="col-auto">
								<button type="button" class="btn btn-sm btn-primary task-edit" data-task-id="{{id}}">Ред.</button>
							</div>
							{{/access.edit}}
						</div>
					</li>
					{{/tasks}}
				</ul>
				
				{{#access.edit}}
				<button type="button" class="btn btn-success mt-3" id="task-add">Добавить задачу</button>
				{{/access.edit}}
				
				{{#access.owner}}
				<h2 class="h5 mt-5">Доступ</h2>
				<div class="shares">
					<div class="shares-list">
						<!-- См. template-share -->
						{{#shares}}
						<div class="shares-user">
							<div class="image opener image-loader image-loader-user">
								<img src="{{image}}" alt="Аватар пользователя">
							</div>

							<div class="info">
								<div class="info-email">{{login}}</div>
								<div class="info-access">
									<span class="info-access-item{{#access_read}} active{{/access_read}}" data-access-type="read">Чтение</span>
									<span class="info-access-item{{#access_edit}} active{{/access_edit}}" data-access-type="edit">Изменение</span>
								</div>
							</div>

							<div class="action">
								<button type="button" class="btn btn-sm btn-primary share-edit" data-share-id="{{id}}">Ред.</button>
							</div>
						</div>
						{{/shares}}
					</div>
					
					{{#access.edit}}
					<button type="button" class="btn btn-success mt-3" id="share-add">Добавить пользователя</button>
					{{/access.edit}}
				</div>
				{{/access.owner}}
				
				{{^access.owner}}
				<h2 class="h5 mt-5">Автор списка</h2>
				<div class="shares">
					<div class="shares-list">
						<div class="shares-user">
							<div class="image opener image-loader image-loader-user">
								<img src="{{list.image}}" alt="Аватар пользователя">
							</div>

							<div class="info">
								<div class="info-email">{{list.login}}</div>
							</div>
						</div>
					</div>

					<p class="mt-3">Вам доступно: {{#access.read}}<b>Чтение</b>{{/access.read}} {{#access.edit}}<b>Изменение</b>{{/access.edit}}.</p>

					<button type="button" class="btn btn-danger" id="list-unsubscribe">Отписаться</button>
				</div>
				{{/access.owner}}
					
				{{#access.owner}}
				<hr class="mt-5">

				<div class="row justify-content-between align-items-center">
					<div class="col-auto">
						<button type="button" class="btn btn-danger" id="list-delete">Удалить список</button>
					</div>

					<div class="col-auto">
						<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-list">Изменить</button>
					</div>
				</div>
				{{/access.owner}}
			</div>

			<div class="col-12 order-0 mb-5 col-md-5 order-md-2 mb-md-0 col-lg-4">
				<h2 class="h5">Поиск</h2>

				<div class="card">
					<div class="card-body">
						<form class="mb-0" method="POST" id="form-search">
		  					<div class="form-group">
		  						<label for="search-form-text">Задача</label>
								<input type="text" class="form-control" id="search-form-text">
							</div>
							
							<div class="form-group">
								<label for="search-form-tags">Метки</label>
								<select class="custom-select" id="search-form-tags" multiple>
									<option>Выбрать метки</option>
								</select>
							</div>
							
							<div class="row">
								<div class="col-6">
									<button type="reset" class="btn btn-light btn-block">Сбросить</button>
								</div>

								<div class="col-6">
									<button type="submit" class="btn btn-primary btn-block">Найти</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>