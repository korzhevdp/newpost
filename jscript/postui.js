	/* ------------------------------------------------------------------------------------ */

	function resizeTabHeader(tab_container) {
		$('#' + tab_container + ' table thead tr:first-child th:nth-child(1)').css('width', '20px');  // fixed header width
		$('#' + tab_container + ' table thead tr:first-child th:nth-child(2)').css('width',           // variable header width...
			$('#' + tab_container + ' table tbody tr:first-child td:nth-child(2)').css('width')
		);     // ...from column width
		$('#' + tab_container + ' table thead tr:first-child th:nth-child(3)').css('width', '100px'); // fixed header width
		$('#' + tab_container + ' table thead tr:first-child th:nth-child(4)').css('width', '122px'); // fixed header width
		$('#' + tab_container + ' table thead tr:first-child th:nth-child(5)').css('width', '122px'); // fixed header width
	}

	/* ################################################################################### */

	$(window).on('resize', function() {
		//resizeTabHeader('filelist');
	});

	$(document).ready(function () {

		$('#multi_upload_progress').css('width', parseInt($('#drop_area').css('width')));

		/* Обработчики событий */

		$(window).resize(function() {
			$('#multi_upload_progress').css('width', parseInt($('#drop_area').css('width')));
		});

		$('#actionList').click( function() {
			$(this).fadeOut(400);
		})
		.mouseleave(function() {
			$(this).fadeOut(400);
		});

		setDialogWindowCloser();
		/* фильтр-поиск по фамилии */

		$('#fio_search').keyup( function() {
			if ( filterString.length == $(this).val().length) {
				return false;
			}
			filterString = $(this).val();
			getUserList();
		});

		$('#xletters a.let').click( function() {
			var letter   = $(this).attr("title");
			$(".let").removeClass("active");
			$(this).addClass("active");
			$('#fio_search').val(letter);
			getUserList();
		});



		// Фокус на поле поиска фамилий
		$('#fio_search').focus();

		/* Функции инициализации */
		// Создаем обработчики для "действий"
		//init_all_actions();
		setAjaxUploadEvent();
		getUserList();
		
		$("#systemBusy").click(function(){
			//console.log("click");
			showSystemBusy();
		});

		$("#switchToAlbum").click(function(){
			//showSystemBusy();
			if ( $(this).hasClass("toolbtn_off") ) {
				console.log("no action")
				return true;
			}
			createPhotoAlbum();
		});

	});

	$(".let.PDSW").click(function(){
		if ($(this).hasClass("active")) {
			$(this).removeClass("active");
			nativeRightClick = false;
			return true;
		}
		$(this).addClass("active");
		nativeRightClick = true;
	});

	function doNotify( notification ){
		alert(notification);
	}



	function makeDialogWindowDraggable(){
		$('.dialog_window').draggable({
			cursor: 'move',
			handle: '#dialog_header'
		});
	}

	/* !!! begin of events for future-elements !!! */
