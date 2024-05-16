	<div class="dialog_window" ref="fileInfo">
		<div id="dialog_header">Информация о файле</div>
		<div id="dialog_buttons">
			<span class="dialog_other_buttons" ref="fileInfo">
				<a href="#" class="toolbtn saveInfo" ref="fileInfo">Сохранить</a>
				<a href="#" class="toolbtn dialog_close">&nbsp; Закрыть &nbsp;</a>
			</span>
		</div>
		<div id="dialog_content">
			<table border="0" width="100%">
				<tr>
					<td class="dialog_td_l">Файл:</td>
					<td colspan="2" class="jsonTarget" ref="fileInfo" field="originalFilename"></td>
				</tr>
				<tr>
					<td class="dialog_td_l">Размер:</td>
					<td colspan="2" class="jsonTarget" ref="fileInfo" field="fileSize"></td>
				</tr>
				<tr>
					<td class="dialog_td_l">Загружен:</td>
					<td colspan="2" class="jsonTarget" ref="fileInfo" field="creationDate"></td>
				</tr>
				<tr>
					<td class="dialog_td_l">Автоудаление:</td>
					<td colspan="2" class="dialog_td_r">
						<input type="date" value="" class="jsonTarget" ref="fileInfo" field="deletionDate">
					</td>
				</tr>
				<tr>
					<td class="dialog_td_l">Компьютер:</td>
					<td colspan="2">
						<span class="jsonTarget" ref="fileInfo" field="ownerHost"></span>
						( <span class="jsonTarget" ref="fileInfo" field="ownerIP"></span> )
					</td>
				</tr>
				<tr>
					<td class="dialog_td_l">Лимит скачиваний:</td>
					<td colspan="2" class="dialog_td_r">
						<input type="number" id="id_down_limit" min="-1" step="1" class="jsonTarget" ref="fileInfo" field="downloadLimit">&nbsp;&nbsp;&nbsp;
						<span class="grey">-1 : без ограничений</span>
					</td>
				</tr>
				<tr>
					<td class="dialog_td_l" valign="top">Комментарий:</td>
					<td colspan="2">
						<textarea class="jsonTarget" ref="fileInfo" field="comments"></textarea>
					</td>
				</tr>
			</table>
		</div>
	</div>