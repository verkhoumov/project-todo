<!-- Модальное окно для создания нового/изменения старого доступа -->
<div class="modal fade" id="modal-share" tabindex="-1" role="dialog" aria-labelledby="modal-share-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modal-share-title">Изменение доступа</h5>

				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="modal-body">
				<form id="form-access">
					<div class="form-group">
						<label for="form-access-identity">Аккаунт</label>

						<div class="row">
							<div class="col-12 col-sm">
								<input type="text" class="form-control" id="form-access-identity" placeholder="ivan.invanoff@yandex.ru" name="access[identity]">
							</div>

							<div class="col-12 col-sm-auto mt-3 mt-sm-0">
								<button type="button" class="btn btn-primary" id="form-access-identity-button">Проверить</button>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label>Права доступа</label>

						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input disabled" id="form-access-read" name="access[read]" value="1" checked="checked" disabled>
							<label class="custom-control-label" for="form-access-read">Чтение</label>
						</div>

						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" id="form-access-edit" name="access[edit]" value="1">
							<label class="custom-control-label" for="form-access-edit">Изменение</label>
						</div>
					</div>

					<div class="row justify-content-between align-items-center">
						<div class="col-auto">
							<button type="button" class="btn btn-danger" id="share-delete">Закрыть доступ</button>
						</div>

						<div class="col-auto">
							<button type="submit" class="btn btn-success" name="access[submit]">Сохранить</button>
						</div>
					</div>
				</form>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>