<!-- Модальное окно создания нового списка -->
<div class="modal fade" id="modal-list" tabindex="-1" role="dialog" aria-labelledby="modal-list-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div class="h5 modal-title" id="modal-list-title">Новый список задач</div>

				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				<form method="POST" id="form-list" data-type="{{type}}">
					<div class="form-group">
						<label for="list-form-title">Название</label>
						<input type="text" class="form-control" id="list-form-title" name="list[title]" value="{{title}}">
						<small class="form-text text-muted mt-2">От 5 до 200 символов.</small>
					</div>

					<div class="form-group">
						<label for="list-form-description">Описание</label>
						<textarea class="form-control" id="list-form-description" name="list[description]" rows="5">{{description}}</textarea>
						<small class="form-text text-muted mt-2">От 5 до 2000 символов.</small>
					</div>

					<button type="submit" class="btn btn-success">{{button}}</button>
				</form>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>