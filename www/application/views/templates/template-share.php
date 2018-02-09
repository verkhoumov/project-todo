{{=<% %>=}}
<div class="shares-user">
	<div class="image opener">
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
<%={{ }}=%>