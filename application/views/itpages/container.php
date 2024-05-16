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

			<?php if ( $itAdmin ) { ?>
				<a href="/management" class="let itm" title="Для IT-специалистов.">&nbsp;it&nbsp;</a>
			<?php } ?>
		</div>

		<div id="letters_bottom"></div>

		<div id="toolbars">
			<div id="fio_search_label">
				<img src="/images/ico/star_grey.png" class="favStarToggler"> Пользователи сети:
			</div>
			<div id="fio_search_field">
				<input type="text" id="fio_search" class="xinput" placeholder="Введите фамилию для поиска" value="">
			</div>
			<div id="toolbar3">
				<a href="#" id="actn" class="toolbtn_off" title="Показать новые файлы">&#x2605;</a>
			</div>
			<div id="toolbar2">
				<div id="folderpath"></div>
			</div>
			<div id="toolbar1">
			</div>
		</div>
	</div>

	<div id="left_fixed">
		<div id="fiolist">
			<?//=$users;?>
		</div>
		<div id="speclist_label">
			Специальные ресурсы:
		</div>
		<div id="speclist">
			<?=$special;?>
		</div>
	</div>

	<!-- Content -->
	<div id="content">
		<div id="filelist"><?=$content;?></div>
		
	</div>
	<!-- Content -->

	<div id="loading" style="display:none;">
		<img src="/images/img/loading_2.gif">
	</div>

	<div id="hidden_box" style="display:none"></div>

	<div id="multidownloader"></div>


	<script type="text/javascript" src="/jscript/jquery-3.7.1.min.js"></script>
	<script type="text/javascript" src="/jscript/jquery-ui-1.10.2.custom.min.js"></script>
	<script type="text/javascript" src="/jscript/ajax-file-upload.js"></script>
	<script type="text/javascript" src="/jscript/ajax-dragdrop-multi-upload.js"></script>
	<script type="text/javascript" src="/jscript/post.js"></script>
	<script type="text/javascript" src="/jscript/post_actions.js"></script>
	<script type="text/javascript" src="/jscript/postui.js"></script>

</body>
</html>
