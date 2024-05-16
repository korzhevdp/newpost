<div id="it_content">

<div class="hdr">Меню дополнительной функциональности (IT-shneg-mode)</div>

<div id="it_ver">Реинкарнация 3.0 / PHP</div>


<div class="it_controls">
	<table border="0" cellspacing="2" cellpadding="0">
		<tr>
			<td align="center">
				<b>Управление пользователями:</b>
				<hr>
			</td>
		</tr>
		<tr>
			<td>
				<table border="0">
					<tr>
						<td>
							<input type="text" id="faddi" value="" size="35">
						</td>
						<td>
							<div id="faddb">
								<a href="#" onclick="add_user($('#faddi').val());">
									<img src="/images/img/_t.gif" width="66" height="22" border="0">
								</a>
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td><hr>
				<table border="0">
					<tr>
						<td><input type="checkbox" id="rmu_enable" onchange="enable_rm_func();"></td>
						<td><label for="rmu_enable">Режим удаления пользователей</label></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>

<ul><p></p>
	<li>
		<div class="underli">Просмотр логов файлообменной системы:</div><br>
		<ul>
			<li><a href="/logs/uploaded"    target="_blank">Кто, кому, что загрузил.</a></li>
			<li><a href="/logs/download"    target="_blank">Кто, у кого, что посмотрел.</a></li>
			<li><a href="/logs/autodeleted" target="_blank">У кого, что было удалено автоматически.</a></li>
			<li><a href="/logs/operations"  target="_blank">Кто, у кого как-либо пошевeлил файлы/папки.</a></li>
		</ul>
	</li>
	<li><p></p>
		<div class="underli">Сбор статистики:</div><br>
		<ul>
			<a href="/management/collectStatistics">Пересобрать статистику</a>
		</ul>
	</li><br><br>


	<li>
		<div class="underli">Статистика использования:</div>
		<table border="0" width="580" id="tabstat" cellpadding="4" cellspacing="0">
			<tr>
				<td align="right">Всего пользователей:</td>
				<td align="right"><b><?=$userCount;?></b></td>
				<td align="center"><i>Статистика за последние 100 дней</i></td>
			</tr>
			<tr>
				<td align="right">Пользователей, хранящих файлы:</td>
				<td align="right"><b><?=$fileStatistics["usersStoringFiles"];?></b></td>
				<td width="300"><img src="/management/graph/1" width="300" height="16"></td>
			</tr>
			<tr>
				<td align="right">Всего хранится файлов:</td>
				<td align="right"><b><?=$fileStatistics["filesCount"];?></b></td>
				<td width="300"><img src="/management/graph/2" width="300" height="16"></td>
			</tr>
			<tr>
				<td align="right">Хранится "вечных" файлов:</td>
				<td align="right"><b><?=$fileStatistics["eternalFilesCount"];?></b></td>
				<td width="300"><img src="/management/graph/3" width="300" height="16"></td>
			</tr>
			<tr>
				<td align="right">Суммарный объём файлов:</td>
				<td align="right"><b><?=$fileStatistics["fileVolume"];?></b></td>
				<td width="300">МБайт, &nbsp; (Свободно <b><?=$fileStatistics["diskFreeSpace"];?></b> ГБайт)</td>
			</tr>
			<tr>
				<td align="right">Файлы > 100 Mb:</td>
				<td align="right"><b><?=$fileStatistics["bigFilesCount"];?></b></td>
				<td width="300">
					<select style="width:300px;">
					</select>
				</td>
			</tr>
			<!-- <tr>
				<td align="right" valign="top"><acronym title="Файл на диске есть, но в базе его нет.">Беспризорные файлы:</acronym></td>
				<td align="right" valign="top"><b>&nbsp;</b></td>
				<td width="300">&nbsp;</td>
			</tr> -->
		</table>
		</p>
	</li>

	<li>
		<div class="underli">Типы файлов в хранилище:</div>
		<table border="0" cellpadding="5" cellspacing="0">
			<tr>
				<td align="right" width="200">
					<img src="img_pie.asp" width="190" height="190">
				</td>
				<td>
					<ul class="pie_legend">
						<li><span class="ltr_legend" style="background-color:#5A95DF">&nbsp;A&nbsp;</span>&nbsp; Документы (<?=implode(", ", $fileStatistics["sorts"]["docs"]);?>) <?=$fileStatistics["docsCount"];?></li>
						<li><span class="ltr_legend" style="background-color:#E15A57">&nbsp;B&nbsp;</span>&nbsp; Изображения (<?=implode(", ", $fileStatistics["sorts"]["imgs"]);?>) <?=$fileStatistics["imgsCount"];?></li>
						<li><span class="ltr_legend" style="background-color:#B3D964">&nbsp;C&nbsp;</span>&nbsp; Видео (<?=implode(", ", $fileStatistics["sorts"]["video"]);?>) <?=$fileStatistics["videoCount"];?></li>
						<li><span class="ltr_legend" style="background-color:#9674BE">&nbsp;D&nbsp;</span>&nbsp; Текстовые файлы (<?=implode(", ", $fileStatistics["sorts"]["texts"]);?>) <?=$fileStatistics["textsCount"];?></li>
						<li><span class="ltr_legend" style="background-color:#54C7E8">&nbsp;E&nbsp;</span>&nbsp; Программы (<?=implode(", ", $fileStatistics["sorts"]["progs"]);?>) <?=$fileStatistics["progsCount"];?></li>
						<li><span class="ltr_legend" style="background-color:#FFA346">&nbsp;F&nbsp;</span>&nbsp; Архивы (<?=implode(", ", $fileStatistics["sorts"]["archs"]);?>) <?=$fileStatistics["archsCount"];?></li>
						<li><span class="ltr_legend" style="background-color:#60A7FF">&nbsp;G&nbsp;</span>&nbsp; Скрипты (<?=implode(", ", $fileStatistics["sorts"]["scrpt"]);?>) <?=$fileStatistics["scrptCount"];?></li>
						<li><span class="ltr_legend" style="background-color:#FF625E">&nbsp;H&nbsp;</span>&nbsp; Книги (<?=implode(", ", $fileStatistics["sorts"]["books"]);?>) <?=$fileStatistics["booksCount"];?></li>
						<li><span class="ltr_legend" style="background-color:#CBF96D">&nbsp;I&nbsp;</span>&nbsp; Остальное <?=$fileStatistics["otherCount"];?></li>
					</ul>
				</td>
			</tr>
		</table>
	</li>
</ul>

</div>

<script>

if (get_rmu_status()) {
	$('#rmu_enable').attr('checked', 'checked');
	$('.it_controls label').css('color', '#ff0000');
} else {
	$('#rmu_enable').removeAttr('checked');
	$('.it_controls label').css('color', '#000000');
}

</script>