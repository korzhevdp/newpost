	<div class="dialog_window" ref="folderInfo">
		<div id="dialog_header">Информация о папке</div>
		<div id="dialog_buttons">
			<span class="dialog_other_buttons" ref="folderInfo">
				<a href="#" class="toolbtn saveInfo" ref="fileInfo">Сохранить</a>
				<a href="#" class="toolbtn dialog_close">&nbsp; Закрыть &nbsp;</a>
			</span>
		</div>
		<div id="dialog_content">
			<table border="0" width="100%">
				<tr>
					<td class="dialog_td_l">Папка:</td>
					<td class="jsonTarget" ref="folderInfo" field="folderName"></td>
				</tr>
				<tr>
					<td class="dialog_td_l">Создана:</td>
					<td>
						<span class="jsonTarget" ref="folderInfo" field="creationDate"></span> - 
						<span class="jsonTarget" ref="folderInfo" field="deletionDate"></span>
					</td>
				</tr>
				<tr>
					<td class="dialog_td_l">Компьютер:</td>
					<td class="jsonTarget" ref="folderInfo" field="ownerIP"></td>
				</tr>
				<tr>
					<td class="dialog_td_l" valign="top">Комментарий:</td>
					<td><textarea class="jsonTarget" ref="folderInfo" field="comments"></textarea></td>
				</tr>
			</table>
		</div>
	</div>