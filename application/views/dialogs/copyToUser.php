	<div class="dialog_window" ref="copyToUser">
		<div id="dialog_header">Переместить к пользователю</div>
		<div id="dialog_buttons">
			<span class="dialog_other_buttons" ref="copyToUser">
				<a href="#" class="toolbtn" id="copyToUser" ref="copyToUser">&nbsp; Переместить &nbsp;</a>
				<a href="#" class="toolbtn dialog_close">&nbsp; Закрыть &nbsp;</a>
			</span>
		</div>
		<div id="dialog_content">
			<table border="0" width="100%">
				<tr>
					<td>
						Переместить <span class="red_b copyToFoldersCount"></span>
						папок и <span class="red_b copyToFilesCount"></span> файлов к пользователю<br><br><br>
						<span class="red_b copyToUserName">&#x01F878;&nbsp;Выберите пользователя в меню слева</span>
						<input type="hidden" id="targetUser" value="0"><br><br><br>
						<input type="checkbox" id="leaveMeACopy" checked> Оставить копию себе
					</td>
					<td></td>
				</tr>
			</table>
		</div>
	</div>
