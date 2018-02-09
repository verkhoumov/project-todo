<!-- Модальное окно для добавления/изменения задач -->
<div class="modal fade" id="modal-task" tabindex="-1" role="dialog" aria-labelledby="modal-task-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modal-task-title">Новая задача</h5>

				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="modal-body">
				<form method="POST" id="form-task" enctype="multipart/form-data">
					<div class="form-group">
						<label for="form-task-name">Название задачи</label>
						<input type="text" class="form-control" id="form-task-name" name="task[name]">
						<small class="form-text text-muted mt-2">От 1 до 400 символов.</small>
					</div>

					<div class="form-group">
						<label>Статус выполнения</label>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" id="form-task-status" name="task[status]" value="1">
							<label class="custom-control-label" for="form-task-status">Задача выполнена</label>
						</div>
					</div>

					<div class="form-group">
						<label>Изображение</label>
						<div class="row align-items-center">
							<div class="col-auto">
								<div class="image avatar opener">
									<img src="/upload/images/task.png" alt="Изображение задачи" id="form-task-image">
								</div>
							</div>

							<div class="col">
								<div class="form-group">
									<input type="file" class="form-control-file" id="form-task-file" name="task[image]">
								</div>
							</div>
							
							<div class="col-12 mt-3">
								<small class="form-text text-muted mb-3">Ограничения — не более 1024х1024, 2мб, JPG, PNG, GIF.</small>

								<div class="row">
									<div class="col-12 col-sm-6">
										<button type="button" class="btn btn-block btn-light" id="file-reset">Отменить</button>
									</div>

									<div class="col-12 mt-3 col-sm-6 mt-sm-0">
										<button type="button" class="btn btn-block btn-danger" id="form-task-image-delete">Удалить</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="form-group">
						<label>Метки</label>
						<div class="form-tags"></div>
						<small class="form-text text-muted mb-3">От 1 до 30 символов. Любые символы кроме "|".</small>
						<button type="button" class="btn btn-block btn-success" id="form-task-tags-add">Добавить метку</button>
					</div>

					<hr class="mt-5">

					<div class="row">
						<div class="col">
							<button type="button" class="btn btn-danger btn-block" id="form-task-delete">Удалить задачу</button>
						</div>

						<div class="col">
							<button type="submit" class="btn btn-success btn-block" name="task[submit]">Сохранить</button>
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