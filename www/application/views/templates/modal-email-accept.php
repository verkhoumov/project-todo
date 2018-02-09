<!-- Модальное окно с подтверждением почты -->
<div class="modal fade" id="modal-email-accept" tabindex="-1" role="dialog" aria-labelledby="modal-email-accept-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div class="h5 modal-title" id="modal-email-accept-title">Подтверждение почты</div>

				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
				<form method="POST" id="form-email-accept">
					<p>На указанный E-mail был отправлен код подтвеждения. Пожалуйста, укажите его в поле ниже.</p>

					<label for="accept-form-code">Код подтверждения</label>
					<div class="row">
						<div class="col-12 col-sm">
							<input type="text" class="form-control" id="accept-form-code" name="code">
						</div>

						<div class="col-12 col-sm-auto mt-3 mt-sm-0">
							<button type="submit" class="btn btn-primary" id="restore-form-button-restore">Проверить</button>
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