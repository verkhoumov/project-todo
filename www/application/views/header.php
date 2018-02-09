<div class="wrapper">
	<div class="container">
		<div class="row justify-content-between align-items-center">
			<div class="col">
				<h1 class="h4"><a href="/">TODO-менеджер</a></h1>
			</div>
			
			{{#auth}}
			<div class="col-auto">
				<ul class="nav nav-pills">
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">{{login}}</a>
						<div class="dropdown-menu dropdown-menu-right">
							<a class="dropdown-item" href="/">Списки</a>
							<a class="dropdown-item" href="/settings">Настройки</a>
							<a class="dropdown-item" href="/sign-out">Выйти</a>
						</div>
					</li>
				</ul>
			</div>
			{{/auth}}
		</div>
	</div>
</div>