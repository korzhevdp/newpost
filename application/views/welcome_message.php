<!DOCTYPE html>
<html>
<head>
	<title>Файлообменный ресурс</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" media="screen" type="text/css" href="/css/post.css">
</head>

<body>
	<div id="top_fixed">
		<div id="letters">
			<?=$SAKnob;?>

			<a href="#help" class="let hlp" onclick="show_help();" title="Справка по файлообменной системе.">&nbsp;?&nbsp;</a>

			<?php if ( $itAdmin ) { ?>
				<a href="/management" class="let itm" title="Для IT-специалистов.">&nbsp;it&nbsp;</a>
			<?php } ?>
			
			<!-- <span class="PDSW let itm">PD</span> -->
			&nbsp;<a href="#" id="systemBusy">&bull;</a>&nbsp;&nbsp;<span id="xletters"><?=$letters;?></span>
			
		</div>

		<div id="letters_bottom"></div>

		<div id="toolbars">
			<div id="fio_search_label">
				<img src="/images/ico/star_grey.png" class="favStarToggler"> Пользователи сети:
			</div>
			<div id="fio_search_field">
				<input type="text" id="fio_search" class="xinput" placeholder="Введите фамилию для поиска" value="коржев">
			</div>
			<div id="toolbar3">
				<span class="toolbtn hide" id="switchToDocs" title="Переключиться к документам"><img class="ico" src="/images/ico/folder_page.png"></span>
				<span class="toolbtn_off" id="switchToWeb" title="Переключиться к веб-странице"><img class="ico" src="/images/ico/gost.png"></span>
				<span class="toolbtn_off" id="switchToAlbum" title="Режим фотоальбома"><img class="ico" src="/images/ico/album.png"></span>
				<!-- <span class="toolbtn_off" id="actn" title="Показать новые файлы">&#x2605;</span> -->
				<span class="toolbtn_off" id="makeFolder" title="Создать новую папку" ><img class="ico" src="/images/ico/folder_add.png"></span>
				<span class="toolbtn_off makeZip" title="Создать архив"><img class="ico" src="/images/ico/compress.png"></span>&nbsp;&nbsp;&nbsp;&nbsp;
				<span class="toolbtn_off deleteObject" title="Удалить файл / папку"><img class="ico" src="/images/ico/cross.png"></span>
			</div>
			<div id="toolbar2">
				<div id="folderpath"></div>
			</div>
			<div id="toolbar1">
				<div id="toolmessage">&nbsp;</div>
			</div>
		</div>
	</div>

	<div id="left_fixed">
		<div id="fiolist">
			<?=$users;?>
		</div>
		<div id="speclist_label">
			Специальные ресурсы:
		</div>
		<div id="speclist">
			<?=$special;?>
		</div>
	</div>

	<!-- FileList -->
	<div id="filelist" class="hide">
		<table class="fixed-hdr">
			<thead id="mainHeader">
				<tr bgcolor="#e0e0e0" class="gradient1">
					<th class="ftheader col1">
						<input type="checkbox" id="ffc_header">
					</th>
					<th class="ftheader col2">
						<div ref="0" class="srt_colmn" title="Переключить сортировку колонки">
							Имя файла&nbsp;
							<span class="sorter" ref="0" direction="down">&#x25B2;</span>
							<span class="sorter" ref="0" direction="up">&#x25BC;</span>
						</div>
					</th>
					<th class="ftheader col3">
						<div ref="1" class="srt_colmn" title="Переключить сортировку колонки">
							Размер&nbsp;
							<span class="sorter" ref="1" direction="down">&#x25B2;</span>
							<span class="sorter" ref="1" direction="up">&#x25BC;</span></div>
					</th>
					<th class="ftheader col4">
						<div ref="2" class="srt_colmn" title="Переключить сортировку колонки">
							Дата загрузки&nbsp;
							<span class="sorter" ref="2" direction="down">&#x25B2;</span>
							<span class="sorter" ref="2" direction="up">&#x25BC;</span>
						</div>
					</th>
					<th class="ftheader col5">
						<div ref="3" class="srt_colmn" title="Переключить сортировку колонки">
							Удаление&nbsp;
							<span class="sorter" ref="3" direction="down">&#x25B2;</span>
							<span class="sorter" ref="3" direction="up">&#x25BC;</span>
						</div>
					</th>
				</tr>
			</thead>
			<tbody id="filler"      class="hide">
				<tr>
					<td class="cflist col1"><img src="/images/img/_t.gif" height="1" width="20"></td>
					<td class="cflist col2"></td>
					<td class="cflist col3"><img src="/images/img/_t.gif" height="1" width="100"></td>
					<td class="cflist col4"><img src="/images/img/_t.gif" height="1" width="122"></td>
					<td class="cflist col5"><img src="/images/img/_t.gif" height="1" width="122"></td>
				</tr>
			</tbody>
			<tbody id="emptyfolder" class="hide">
				<tr class="trlist">
					<td class="cflist col1">&nbsp;</td>
					<td class="cflist t_filename col2">
						<img class="ico" src="/images/ico/folder.png">&nbsp;<a class="fhref uplevel" href="#change_folder(0);">&nbsp;.&nbsp;.&nbsp;</a>
					</td>
					<td class="cflist col3">
						<acronym title="Верхний уровень">&lsaquo; Вверх &rsaquo;</acronym>
					</td>
					<td class="cflist col4">&nbsp;</td>
					<td class="cflist col5">&nbsp;</td>
				</tr>
			</tbody>
			<tbody id="folders"     class="hide"></tbody>
			<tbody id="files"       class="hide"></tbody>
			<tbody id="html"        class="hide">
				<tr><td colspan="100" id="htmlSubstitution"></td></tr>
			</tbody>
			<tbody id="album"       class="hide">
				<tr><td colspan="100" id="albumContainer"></td></tr>
			</tbody>
		</table>
	</div>
	<!-- !FileList -->

	<!-- Right-click Menu -->
	<div id="actionList">
		<a href="#" id="actz"><div class="action_item"><img class="ico" src="/images/ico/download_for_windows.png">&nbsp; Скачать отмеченные файлы</div></a>
		<div class="break_line"></div>
		<a href="#" id="act0"><div class="action_item"><img class="ico" src="/images/ico/update.png">&nbsp; Обновить список файлов</div></a>
		<a href="#" id="act3"><div class="action_item"><img class="ico" src="/images/ico/information.png">&nbsp; Свойства файла / папки</div></a>
		<a href="#" id="actb"><div class="action_item"><img class="ico" src="/images/ico/date_delete.png">&nbsp; Продлить жизнь файлов</div></a>
		<div class="break_line"></div>
		<a href="#" id="actc" class="makeZip"><div class="action_item "><img class="ico" src="/images/ico/compress.png">&nbsp; Создать архив</div></a>
		<a href="#" id="act1"><div class="action_item"><img class="ico" src="/images/ico/folder_add.png">&nbsp; Создать новую папку</div></a>
		<!-- <a href="#" id="acta"><div class="action_item"><img class="ico" src="/images/ico/map_add.png">&nbsp; Создать фотоальбом</div></a> -->
		<a href="#" id="act2"><div class="action_item"><img class="ico" src="/images/ico/page_add.png">&nbsp; Редактировать <b>index.html</b></div></a>
		<div class="break_line"></div>
		<a href="#" id="act4"><div class="action_item"><img class="ico" src="/images/ico/textfield_rename.png">&nbsp; Переименовать</div></a>
		<div class="break_line"></div>
		<!-- <a href="#" id="act5"><div class="action_item grey"><img class="ico" src="/images/ico/folder_user.png">&nbsp; Скопировать к пользователю</div></a> -->
		<a href="#" id="act6"><div class="action_item"><img class="ico" src="/images/ico/folder_user.png">&nbsp; Переместить к пользователю</div></a>
		<div class="break_line"></div>
		<!-- <a href="#" id="act7"><div class="action_item"><img class="ico" src="/images/ico/folder_page.png">&nbsp; Скопировать в папку</div></a> -->
		<!-- <a href="#" id="act8"><div class="action_item"><img class="ico" src="/images/ico/folder_page_white.png">&nbsp; Переместить в папку</div></a> -->
		<a href="#" id="act7"><div class="action_item"><img class="ico" src="/images/ico/folder_page_white.png">&nbsp; Переместить в папку</div></a>
		<div class="break_line"></div>
		<a href="#" id="deleteObject"><div class="action_item"><img class="ico" src="/images/ico/cross.png">&nbsp; Удалить</div></a>
	</div>
	<!-- !Right-click Menu -->

	<!-- Easter Egg -->
	<div id="bb_header" class="easterEgg hide">
		Это только на первый взгляд в системе ничего не происходит...<br>На самом деле, на молекулярном уровне она очень и очень занята
	</div>
	<div id="bb_content" class="easterEgg hide"></div>
	<!-- !Easter Egg -->

	<!-- HelpPage -->
	<div id="help_content">

		<div class="hdr">Файлообменная система</div>
		<div id="help_ver">Реинкарнация 3.0</div>
		<small>Разработчик системы - отдел сетевого администрирования, тел: 607-450</small>
		<br>
		<ol>
			<li><b>Права доступа:</b>
				<ul>
					<li>У Вас есть возможность <i>читать</i>, <i>загружать</i> и <i>удалять</i> любые файлы в любых папках у любых пользователей.</li>
					<li>Редактирование <i>прямо здесь</i> - <b>НЕВОЗМОЖНО</b>. Для редактирования файлов их следует сохранить к себе на компьютер, отредактировать, сохранить и снова загрузить на сервер.</li>
					<li>Удаление файлов загруженных <i>без</i> даты автоудаления - <i>невозможно</i> *.</li>
					<li>Удаление и загрузка файлов в папках "Специальные ресурсы" - <i>невозможно</i> *.</li>
					<li>Количество чтений/скачиваний файла может быть ограничено.</li>
				</ul>
			</li> 
		<br>
		<li><b>Просмотр папки с файлами:</b>
			<ul>
				<li>Для поиска своей, или любой другой папки, Вы можете воспользоваться верхней панелью с буквами. При нажатии на любую из букв, в левом списке будут отображены фамилии, начинающиеся с указанной буквы.</li>
				<li>Или ввести начало фамилии в поле поиска слева вверху.</li>
				<li>Если Вашей (или нужной Вам) фамилии в списке нет - обратитесь к техническому специалисту в курирующее Вас подразделение.</li>
				<li>Для отображения списка файлов нужного человека, нажмите на его фамилию в списке слева.</li>
			</ul>
		</li> 
		<br>
		<li><b>Получить файл с сервера:</b>
			<ul>
				<li>Чтобы загрузить любой файл с сервера достаточно кликнуть его мышкой.</li>
				<li>При загрузке файлов будет автоматически предложено его сохранить на свой компьютер.</li>
				<li>Для картинок открывается новое окно, в котором они отображаются.</li>
				<li class="help_warn"><b>Внимание!</b><br>В некоторых случаях (зависит от настроек Вашего браузера) файлы офисных документов (Word, Excel) могут сразу открыться. В такой ситуации при попытке его сохранить, документ будет сохранён <i>НА ВАШЕМ</i> компьютере во временную папку и <i>НЕ ПОПАДЁТ</i> обратно на сервер. Будьте внимательны.</li>
			</ul>
		</li> 
		<br>
		<li><b>Загрузить файл на сервер:</b>
			<ul>
				<li>Что бы загрузить файл на сервер к пользователю в какую либо папку сначала надо выбрать саму эту папку.</li>
				<li>В нижней панели выбрать необходимый файл (1).</li>
				<li>Указать время жизни файла (2), по истечении которого файл автоматически будет удалён.</li>
				<li>Нажать на кнопку "Загрузить файл" (3).</li>
				<li>Либо, вместо пунктов (1) и (3) - перетащить файл(ы) <i>но не папки</i> на поле, обозначенное как <abbr id="ddz" title="Поле для загрузки файлов перетаскиванием">"Drag'n'Drop Zone"</abbr>. Эта возможность есть только в современных браузерах: <i>MSIE-10, Chrome, FireFox, Opera</i>.</li>
			</ul>
		</li> 
		<br>
		<li><b>Время жизни файла:</b>
			<ul>
				<li>Файл будет <i>автоматически</i> удалён, когда наступит время его <i>автоудаления</i>, назначенное при загрузке файла.</li>
				<li>Процедура <i>автоудаления</i> запускается ежедневно в 23:30.</li>
			</ul>
		</li> 
		<br>
		<li><b>Дополнительные действия:</b>
			<ul>
				<li><u>Обновить список файлов</u><br><dd>Обновляет список файлов в текущей папке, позволяет увидеть новые файлы, если кто то их для Вас только что загрузил.</li>
				<li><u>Свойства файла / папки</u><br><dd>Отображается детальная информация о помеченном файле или папке. Для файлов можно изменить или убрать дату автоудаления.</li>
				<li><u>Продлить жизнь файлов</u><br><dd>Позволяет простым способом отодвинуть дату автоудаления для нескольких файлов / папок.</li>
				<li><u>Создать новую папку</u><br><dd>Создаётся новая папка.</li>
				<li><u>Создать фотоальбом</u><br><dd>Создаётся файл <b>index.html</b>, в котором будет сформирован фотоальбом из фотографий текущей папки.</li>
				<li><u>Редактировать <b>index.html</b></u><br><dd>Позволяет создать новый или отредактировать существующий файл <b>index.html</b>, находящийся в текущей папке.</li>
				<li><u>Переименовать</u><br><dd>Переименовывает помеченную папку или файл.</li>
				<li><u>Скопировать к пользователю</u><br><dd>Пока не реализовано.</li>
				<li><u>Переместить к пользователю</u><br><dd>Перемещает выбранные папки и файлы к другому пользователю</li>
				<li><u>Скопировать в папку</u><br><dd>Копирует выбранные файлы в какую либо папку у этого же пользователя.</li>
				<li><u>Переместить в папку</u><br><dd>Перемещает выбранные папки и файлы в какую либо папку у этого же пользователя.</li>
				<li><u>Удалить</u><br><dd>Удаляет выбранные папки и файлы. <i>Без возможности восстановления!</i></li>
			</ul>
		</li>
		<br>
		<li><b>Функциональные особенности:</b>
			<ul>
				<li><u>index.html</u><br><dd>Наличие данного файла в папке означает что система вместо отображения списка файлов в этой папке покажет файл index.html. Переключится к виду списка файлов можно кнопкой "Список файлов", и обратно - кнопкой "WEB-страница"</li>
				<li><u>Имена файлов</u><br><dd>При попытке загрузить в папку один и тот же файл несколько раз - каждому следующему файлу к имени будет добавлен суффикс с датой и временем его загрузки. При стечении определенных обстоятельств в папке могут находится файлы с одинаковыми именами - это не ошибка системы, а функциональная особенность. Содержимое у одноименных файлов будет у каждого своё.</li>
				<li><u>Комментарии к файлу / папке</u><br><dd>Для пояснения назначения файла или папки есть возможность создать к ним комментарий. В меню "Выполнить действия" -&gt; "Свойства файла / папки" -&gt; поле "Комментарий"</li>
			</ul>
		</li>
		<br>
		<li><b>Если ничего не получается:</b>
			<ul>
				<li>... жаль.</li>
			</ul>
		</li>
		</ol>
		<div style="border-top:1px solid #808080;">(*) - Для определённой категории пользователей поведение системы отличается от описанного.</div>
		<br>
	</div>
	<!-- !HelpPage -->

	<div id="bottom_fixed">
		<div id="drop_area_progress">
			<div id="drop_area_progress_left"></div>
			<div id="drop_area_progress_right"></div>
		</div>
		<div id="upload">
			<table class="upload_table">
				<tr>
					<td>
						<input type="file" name="fileToUpload" id="fileToUpload">
						<select name="file_ttl" id="file_ttl">
							<option value="1">1 день</option>
							<option value="7">1 неделя</option>
							<option value="30">1 месяц</option>
							<option value="60">2 месяца</option>
							<option value="90" selected="">3 месяца</option>
							<option value="180">6 месяцев</option>
							<option value="365">1 год</option>
							<option value="0">Вечно</option>
						</select> - Срок жизни файла
						<button id="buttonUpload">Загрузить файл</button>
					</td>
				</tr>
			</table>
		</div>
		<div id="htmlSave" class="hide">
			<span class="saveHTML saveHTMLButton">Сохранить HTML</span>
		</div>
	</div>

	<!-- Dialog Windows -->

	<div id="dialogs">
		<?=$proxyerror;?>
		<?=$autodelete;?>
	</div>

	<!-- !Dialog Window -->

	<!-- Special Dialogs -->

	<!-- !Special Dialogs -->

	<div id="loading" class="hide">
		<img src="/images/img/loading_2.gif">
	</div>

	<div id="hidden_box" class="hide"></div>

	<div id="multidownloader" class="hide"></div>
	<form action="" method="post" class="hide" enctype="multipart/form-data"></form>

	<div id="debugWindow" class="hide">
		<div id="debugWindowHeader">Ошибки</div>
		<textarea id="debug"></textarea>
	</div>

	<script type="text/javascript" src="/jscript/jquery-3.7.1.min.js"></script>
	<script type="text/javascript" src="/jscript/jquery-ui-1.10.2.custom.min.js"></script>
	<script type="text/javascript" src="/jscript/ajax-dragdrop-multi-upload.js"></script>
	<script type="text/javascript" src="/jscript/post.js"></script>
	<script type="text/javascript" src="/jscript/post_actions.js"></script>
	<script type="text/javascript" src="/jscript/postui.js"></script>

</body>
</html>
