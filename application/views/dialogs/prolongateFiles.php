	<div class="dialog_window" ref="prolongateFiles">
		<div id="dialog_header">Продлить жизнь файлов</div>
		<div id="dialog_buttons">
			<span class="dialog_other_buttons" ref="prolongateFiles">
				<a href="#" id="prolongateItems" class="toolbtn" ref="prolongateFiles">&nbsp; Продлить &nbsp;</a>
				<a href="#" class="toolbtn dialog_close">&nbsp; Закрыть &nbsp;</a>
			</span>
		</div>
		<div id="dialog_content">
			<div id="longlive_box">
				<div id="dialog_inner">
					<table border="0" width="100%">
						<tr>
							<td class="dialog_td_l">Выбрано: </td>
							<td>
								&nbsp; файлов: <span class="red_b prolongatedFilesCount"></span>,
								папок: <span class="red_b prolongatedFoldersCount"></span>
							</td>
						</tr>
						<tr>
							<td class="dialog_td_l">Продлить на:</td>
							<td>
								<select id="prolongateFor">
									<option value="0">Устаревают сейчас</option>
									<option value="30">1 месяц</option>
									<option value="90">3 месяца</option>
									<option value="180">Полгода</option>
									<option value="365">1 год</option>
									<option value="1095">3 года</option>
									<option value="eternal">Вечное хранение</option>
								</select>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>