<div class="wrapper">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-12 col-md-8 col-lg-6">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Настройки</h5>

						<form method="POST" class="mb-0" id="form-settings" enctype="multipart/form-data">
							<div class="form-group">
								<label>Логин</label>
								<input type="text" class="form-control disabled" value="{{login}}" disabled>
							</div>

							<div class="form-group">
								<label for="settings-form-password">Пароль</label>
								<input type="password" class="form-control" id="settings-form-password" name="settings[password]">
								<small class="form-text text-muted mt-2">Оставьте поле пустым, если не хотите менять пароль. Можно использовать любые символы. От 5 до 60 символов.</small>
							</div>

							<div class="form-group">
								<label for="settings-form-name">Имя</label>
								<input type="text" class="form-control" id="settings-form-name" name="settings[name]" placeholder="Иван Иванов" value="{{name}}">
								<small class="form-text text-muted mt-2">От 3 до 30 символов.</small>
							</div>

							<div class="form-group">
								<label for="settings-form-email">E-mail</label>
								
								<div class="row">
									<div class="col-12 col-sm">
										<input type="text" class="form-control" id="settings-form-email" name="settings[email]" placeholder="ivan.invanoff@yandex.ru" value="{{email}}">
									</div>

									<div class="col-12 col-sm-auto mt-3 mt-sm-0{{#email_accept}} d-none{{/email_accept}}" id="email-accept">
										<button type="button" class="btn btn-success" id="email-accept-button">Подтвердить</button>
									</div>
								</div>
							</div>

							<div class="form-group">
								<label for="settings-form-avatar">Аватар</label>

								<div class="row align-items-center">
									<div class="col-auto">
										<div class="image avatar opener image-loader image-loader-user">
											<img src="{{image}}" alt="Аватар пользователя" id="settings-image">
										</div>
									</div>

									<div class="col">
										<div class="form-group">
											<input type="file" class="form-control-file" id="settings-form-avatar" name="image">
										</div>
									</div>
									
									<div class="col-12 mt-3">
										<small class="form-text text-muted mb-3">Ограничения — не более 1024х1024, 2мб, JPG, PNG, GIF.</small>
										
										<div class="row">
											<div class="col-12 col-sm-6">
												<button type="button" class="btn btn-block btn-light" id="file-reset">Отменить</button>
											</div>

											<div class="col-12 mt-3 col-sm-6 mt-sm-0">
												<button type="button" class="btn btn-block btn-danger" id="delete-avatar"{{^image_name}} disabled{{/image_name}}>Удалить</button>
											</div>
										</div>
									</div>
								</div>
							</div>

							<hr>

							<div class="text-right">
								<button type="submit" class="btn btn-success" name="settings[submit]" disabled>Сохранить изменения</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>