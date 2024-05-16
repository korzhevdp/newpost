	<div class="dialog_window hide" ref="deleteItems">
		<div id="dialog_header">Удалить выбранные элементы</div>
		<div id="dialog_buttons">
			<span class="dialog_other_buttons" ref="deleteItems">
				<a href="#" class="toolbtn delete_objects">&nbsp; Удалить &nbsp;</a>
				<a href="#" class="toolbtn dialog_close">&nbsp; Закрыть &nbsp;</a>
			</span>
		</div>
		<div id="dialog_content">
			<div id="delete_box">
				<div id="dialog_inner">
					<table>
						<tr>
							<td align="right"><img src="/images/ico/error.png"></td>
							<td>
								<b>Выбрано</b>: &nbsp; файлов: <span class="red_b filesToRemoveCount"></span>, папок: <span class="red_b foldersToRemoveCount"></span><br>
								<input type="checkbox" id="delete_confirm" class="cursor_p">&nbsp;<label for="delete_confirm" class="cursor_p">Удалить выбранные объекты<label><br>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>