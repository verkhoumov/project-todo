<div class="wrapper">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
				<div class="card">
					<div class="card-header">
						<ul class="nav nav-pills card-header-pills" id="pills-tab" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" id="sign-in-tab" data-toggle="pill" href="#sign-in" role="tab" aria-controls="sign-in" aria-selected="true">Вход</a>
							</li>

							<li class="nav-item">
								<a class="nav-link" id="sign-up-tab" data-toggle="pill" href="#sign-up" role="tab" aria-controls="sign-up" aria-selected="false">Регистрация</a>
							</li>
						</ul>
					</div>

					<div class="card-body">
						<div class="tab-content">
							<div class="tab-pane fade show active" id="sign-in" role="tabpanel" aria-labelledby="sign-in-tab">
								<form method="POST" id="form-signin">
									<div class="form-group">
										<label for="signin-login">Логин</label>
										<input type="text" class="form-control" id="signin-login" name="signin[login]">
									</div>

									<div class="form-group">
										<label for="signin-password">Пароль</label>
										<input type="password" class="form-control" id="signin-password" name="signin[password]">
									</div>

									<div class="row justify-content-between align-items-center">
										<div class="col-auto">
											<button type="submit" class="btn btn-primary" name="signin[submit]">Войти</button>
										</div>

										<div class="col-auto">
											<a href="#" data-toggle="modal" data-target="#modal-restore">Забыли пароль?</a>
										</div>
									</div>
								</form>
							</div>

							<div class="tab-pane fade" id="sign-up" role="tabpanel" aria-labelledby="sign-up-tab">
								<form method="POST" id="form-signup">
									<div class="form-group">
										<label for="signup-login">Логин</label>
										<input type="text" class="form-control" id="signup-login" name="signup[login]">
										<small class="form-text text-muted mt-2">Можно использовать цифры, латинские буквы и нижнее подчёркивание. От 3 до 20 символов.</small>
									</div>

									<div class="form-group">
										<label for="signup-password">Пароль</label>
										<input type="password" class="form-control" id="signup-password" name="signup[password]">
										<small class="form-text text-muted mt-2">Можно использовать любые символы. От 5 до 60 символов.</small>
									</div>

									<div class="form-group">
										<label for="signup-email">E-mail</label>
										<input type="email" class="form-control" id="signup-email" placeholder="Ivan.Ivanoff@yandex.ru" name="signup[email]">
										<small class="form-text text-muted mt-2">Если забудите пароль, сможете восстановить доступ к аккаунту.</small>
									</div>

									<button type="submit" class="btn btn-block btn-primary" name="signup[submit]">Зарегистрироваться</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>