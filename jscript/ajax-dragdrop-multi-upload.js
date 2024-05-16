function setAjaxUploadEvent () {
	
	var dropArea       = $('#filelist'),
		da_text        = $('#da_text'),
		filename       = '',
		num_files      = 0,
		complete_files = 0,
		upload_error   = false,
		diskSpaceNeed  = 0;

	// Проверка поддержки браузером
	if ( window.FileReader === undefined ) {
		da_text.html('Перетаскивание файлов не поддерживается браузером!');
		$('#drop_area').addClass('drop_area_error');
	}

	// Добавляем класс hover при наведении
	dropArea[0].ondragover = function() {
		dropArea.addClass('drop_area_hover');
		return false;
	};

	// Убираем класс hover
	dropArea[0].ondragleave = function() {
		dropArea.removeClass('drop_area_hover');
		return false;
	};

	// Обрабатываем событие Drop
	dropArea[0].ondrop = function(event) {
		event.preventDefault();

		// ресет счётчиков
		complete_files = 0;
		diskSpaceNeed  = 0;

		dropArea.removeClass('drop_area_hover');
		dropArea.addClass('drop_area_drop');

		fileTrunk = event.dataTransfer.files; // шорткат для данных события drag'n'drop -- "ящик с файлами"
		num_files = fileTrunk.length;

		for ( i in fileTrunk ) {
			if ( fileTrunk[i].size === undefined ) {
				continue;
			}
			diskSpaceNeed += fileTrunk[i].size;
		}
		diskSpaceNeed = diskSpaceNeed / (1024 * 1024); //МБ

		if ( currentUserID ) {
			$.ajax({
				type     : 'GET',
				url      : '/storage/getfreespace',
				datatype : 'text',
				success  : function( freeDiskSpace ) {
					if ( freeDiskSpace < diskSpaceNeed ) { 
						// нет свободного места на диске
						$('#dialogs').empty().load("/post/dialog/diskFreeSpace", function() {
							$('.dialog_window[ref=diskFreeSpace]').removeClass("hide").fadeIn(300);
							$('.dialog_close').click( function() { close_dialog(); });
						});
						return false;
					}
					// если свободного места достаточно - грузим файлы...

					for ( i = 0; i < num_files; i++ ) {
						var formData = new FormData();			// Создаем 'форму' со всеми полями
						formData.append('period'  , $('#file_ttl').val());
						formData.append('userID'  , currentUserID);
						formData.append('folderID', currentFolder);
						formData.append('files'   , fileTrunk[i]);

						xhr      = new XMLHttpRequest();	// Создаем запрос
						xhr.upload.addEventListener('progress', uploadProgress, false);
						xhr.onreadystatechange = stateChange;
						xhr.open('POST', '/storage/uploadFiles');
						xhr.send(formData);
					}
				},
				error   : function( data, stat, err ) {
					console.log( data, stat, err );
				}
			});
		}
	};
	
	// Показываем процент загрузки
	function uploadProgress( event ) {
		var percent = parseInt( event.loaded / event.total * 100, 10);
		if ( num_files == 1 ) {
			if ( percent < 100 ) {
				da_text.html('Загрузка: ' + percent + '%');
				$( '#drop_area_progress_left' ).css( 'width', percent + '%' );
				return true;
			}
			if ( upload_error ) {
				da_text.html('Ошибка при загрузке файла. Запись на диск не удалась');
				return true;
			}
			da_text.html('Ожидание ответа сервера...<br><br><small>Сохранение файла.</small>');
			return true;
		}

		if ( percent < 100 ) {
			da_text.html('Загружено файлов: ' + complete_files + ' из ' + num_files);
			$('#drop_area_progress_left').css('width', percent + '%');
			return true;
		}
		if ( upload_error ) {
			da_text.html('Ошибка при загрузке файла. Запись на диск не удалась');
			return true;
		}
		da_text.html('Загрузка ' + num_files + ' файлов завершена.');
	}

	// Пост обрабочик
	function stateChange( event ) {
		if ( event.target.readyState == 4 ) {
			$('#drop_area_progress_left').css('width', 0);
			if ( event.target.status == 200 ) {
				complete_files++;
				if ( num_files == 1 ) {
					da_text.html('Загрузка файла завершена');
					showUserFiles( currentUserID, 1, currentFolder );
					return true;
				} 
				if ( complete_files == num_files ) {
					da_text.html('Загрузка ' + num_files + ' файлов завершена');
					showUserFiles( currentUserID, 1, currentFolder );
					return true;
				}
				return true;
			}

			upload_error = 1;
			dropArea.addClass('error');
			if ( event.target.status == 404 ) {
				da_text.html('Произошла ошибка!<br><br>Сервер не найден! (' + event.target.status + ')');
			}
			if ( event.target.status == 500 ) {
				da_text.html('Произошла ошибка!<br><br>Сервер сломался! (' + event.target.status + ')');
			}
			if ( event.target.status == 401 ) {
				da_text.html('Произошла ошибка!<br><br>Сервер запутался и просит имя пользователя и пароль (' + event.target.status + ')');
			}
			if ( !event.target.status ) {
				da_text.html('Произошла ошибка!<br><br>Возможно - попытка загрузить папку на сервер (' + event.target.status + ')');
			}
			showUserFiles(currentUserID, 1, currentFolder);
		}
	}
}
