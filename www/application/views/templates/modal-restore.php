<!-- Модальное окно с восстановлением доступа -->
<div class="modal fade" id="modal-restore" tabindex="-1" role="dialog" aria-labelledby="modal-restore-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div class="h5 modal-title" id="modal-restore-title">Восстановление доступа</div>

				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				<form method="POST" id="form-restore">
					<div class="step step-1">
						<label for="restore-form-login">Логин</label>
						<div class="row">
							<div class="col-12 col-sm">
								<input type="text" class="form-control" id="restore-form-login">
							</div>

							<div class="col-12 col-sm-auto mt-3 mt-sm-0">
								<button type="button" class="btn btn-primary" id="restore-form-button-restore">Восстановить</button>
							</div>
						</div>
					</div>

					<div class="step step-2">
						<p>На указанный при регистрации E-mail (<b id="restore-email">example@ya.ru</b>) был отправлен код восстановления. Введите его в поле ниже.</p>
						
						<div class="form-group">
							<label for="restore-form-code">Код восстановления</label>
							<input type="text" class="form-control" id="restore-form-code">
						</div>

						<label for="restore-form-password">Новый пароль</label>
						<div class="row">
							<div class="col-12 col-sm">
								<input type="password" class="form-control" id="restore-form-password">
							</div>

							<div class="col-12 col-sm-auto mt-3 mt-sm-0">
								<button type="button" class="btn btn-primary" id="restore-form-button-save">Сохранить</button>
							</div>
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