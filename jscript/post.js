var filterString      = '',
	currentUserID     = 0, // fio_name
	currentFolder     = 0,  // real currentFolder
	enable_rm_usr     = false,
	enable_action_btn = 0,
	enable_iindex_btn = 0,
	parentFolderID    = 0,
	leftMenuMode      = 'normal',
	displayMode       = "files",
	userID            = 0,
	itemType          = "file",
	itemID            = 0,
	filesData,
	favs              = {},
	favMode           = false,
	nativeRightClick  = true,
	//nativeRightClick  = false,
	sortPattern       = [1, 0, 0, 0];

//////////////////////////////////////////////////////////////////////////////////////
function close_dialog() {
	//console.log("close");
	$("#dialogs"/*, #modal_fader"*/).empty();
	$('#dialog_content textarea').off();
	//$('.dialog_other_buttons').html('');
}

//////////////////////////////////////////////////////////////////////////////////////
function get_width_of_text(txt) {
	$('#hidden_box').html(txt);
	txt_len = $('#hidden_box').css('width');
	return txt_len.replace(/px/, '') * 1;
}

//////////////////////////////////////////////////////////////////////////////////////
function show_help() {
	$("#filelist, .easterEgg").empty().addClass("hide");
	$("#help_content").removeClass("hide");
}

//////////////////////////////////////////////////////////////////////////////////////

function showSystemBusy() {
	$("#help_content").addClass("hide");
	$('#filelist, .easterEgg').removeClass("hide");
}

//////////////////////////////////////////////////////////////////////////////////////

function getUserList() {
	var userName  = $('#fio_search').val();
	$('#loading').removeClass("hide");
	$.ajax({
		type          : 'POST',
		url           : '/post/getuserlist',
		datatype      : 'html',
		data          : {
			userName      : userName,
			let_len       : userName.length,
			enable_rm_usr : enable_rm_usr
		},
		success: function(data, status) {
			$('#loading').addClass("hide");
			$('#fiolist').html(data);
			$('#fio_search').focus();
			setFIOListItemHandler();
			setFavStarHandler();
		},
		error         : function( data, stat, err ) {
			$('#loading').addClass("hide");
			console.log( data, stat, err );
		}
	});
}

function setFavStars() {
	favs   = localStorage.getItem('favs');
	if ( favs === null || favs == '[object Object]' ) { favs = "{}"; }
	favs = JSON.parse(favs);
	$(".fio").each( function() {
		var ref = $(this).attr("ref");
		if ( favs[ref] === undefined ) {
			return true;
		}
		$(".favStar[ref=" + ref + "]").attr("src", "/images/ico/star.png");
	});
}

function setFIOListItemHandler() {


	if ( leftMenuMode == "normal") {
		$(".fio").unbind().click(function() {
			$('#html, #htmlSave').addClass("hide");
			$('#upload').removeClass("hide");
			//( editor === undefined ) ? null : editor.destroy();
			$("#htmlSubstitution").empty();
			$(".fio").removeClass("fio_sel");
			$(this).addClass("fio_sel");
			showUserFiles( $(this).attr("ref"), 0, 0 );
		});
		return true;
	}
	if ( leftMenuMode == "pickUp") {
		$(".fio").unbind().click(function() {
			$('#html, #htmlSave').addClass("hide");
			$('#upload').removeClass("hide");
			//( editor === undefined ) ? null : editor.destroy();
			$("#htmlSubstitution").empty();
			$("#targetUser").val( $(this).attr("ref") );
			//console.log($("#targetUser").val());
			$(".copyToUserName").html( $(this).text() )
		});
	}
}

function setFavStarHandler() {
	$(".favStar").unbind().click( function(e) {
		e.stopPropagation();
		userID = $(this).attr("ref");
		favs   = localStorage.getItem('favs');
		if ( favs === null || favs == '[object Object]' ) { favs = "{}"; }
		favs = JSON.parse(favs);
		if ( $(this).attr("src") == "/images/ico/star_grey.png" ) {
			favs[userID] = 1;
			localStorage.setItem('favs', JSON.stringify(favs));
			$(this).attr("src", "/images/ico/star.png");
			return true;
		}
		delete(favs[userID]);
		localStorage.setItem('favs', JSON.stringify(favs));
		$(this).attr("src", "/images/ico/star_grey.png");
		///////////////////////////////////////////////////////////////////////////////
	});
	$(".favStarToggler").unbind().click( function() {
		favMode = !favMode;
		( favMode )
			? $(".favStarToggler").attr( "src", "/images/ico/star.png" )
			: $(".favStarToggler").attr( "src", "/images/ico/star_grey.png" );

		$(".fio").each( function() {
			var ref = $(this).attr("ref");
			if ( favMode && favs[ref] === undefined ) {
				$(this).addClass("hide");
				return true;
			}
			$(this).removeClass("hide");
		});
	});
	setFavStars();
}

//////////////////////////////////////////////////////////////////////////////////////
/*
function enableActionButton() {
	enable_action_btn = 1;
	$('#makeZip, .deleteObject').removeClass('toolbtn_off').addClass('toolbtn');
}

//////////////////////////////////////////////////////////////////////////////////////

function disableActionButton() {
	enable_action_btn = 0;
	$('#makeZip, .deleteObject').removeClass('toolbtn').addClass('toolbtn_off');
}

//////////////////////////////////////////////////////////////////////////////////////
*/
function show_FIO_list( letter ) {
	letter = (( letter == '') || ( letter == '?')) 
		? "" 
		: letter;
	$('#fio_search').val( letter );
	getUserList();
}

function setDialogWindowCloser() {
	$('.dialog_close').unbind().click( function() {
		close_dialog();
	});
}

//////////////////////////////////////////////////////////////////////////////////////
function showUserFiles( userID, showfiles, folderID ) {
	// устанавливаем переменные в global scope
	currentFolder  = (folderID) ? folderID : 0;
	currentUserID  = userID;
	//parentFolderID = parent;
	column_sort    = sortPattern;
	$('#html, #htmlSave').addClass("hide");
	$('#upload').removeClass("hide");

	//$('#fio' + currentUserID).removeClass('fio_sel');
	//console.log("call:", folderID);
	$('#actionList').fadeOut(300);

	$.ajax({
		type          : 'POST',
		url           : '/storage/getfilelist',
		datatype      : 'json',
		data          : {
			userID    : currentUserID,
			showFiles : showfiles,
			folderID  : currentFolder,
			parentID  : parentFolderID,
			sorts     : column_sort
		},

		success       : function(data) {
			filesData = data;

			$('#fio_search').focus();
			// сборка списка файлов
			$("#help_content, .easterEgg, #album, #files").addClass("hide");
			$("#filelist, #folders").removeClass("hide");
			$('#folders').empty().append( getFoldersTable(filesData.folders, filesData.treePosition.folderName.current, filesData.treePosition.folderName.parent) );
			//$('#files').empty().append( getFilesTable(filesData.files) );
			//resizeTabHeader('filelist'); // что-то демоническое
			//console.log( parentFolderID /*filesData.files.length, filesData.folders.length, filesData.treePosition.folderName.parent*/ )
			tableContent = getFilesTable(filesData.files);
			if ( filesData.files.length + filesData.folders.length == 0 ) {
				if ( filesData.treePosition.folderName.current.length < 2 ) {
					tableContent = filesData.html;
				}
			}
			$('#files').empty().append( tableContent ).removeClass("hide");

			parentFolderID = filesData.treePosition.folderName.current;

			folderName = ( filesData.treePosition.folderName.name === undefined ) 
				? ""
				: filesData.treePosition.folderName.name;

			$('#folderpath').empty().append(createFolderPath( filesData.folderData ) );

			$('#toolmessage').empty().append('<a href="/post/' + currentUserID +' " class="fl_bar_fio" title="Постоянная ссылка для размещения в &laquo;Закладках&raquo;">' + filesData.userdata.name + '</a>, тел: ' + filesData.userdata.phone + '; ' + filesData.userdata.department);

			if ( filesData.hasHTML ) {
				setSubstitutionalViewHandler();
			}

			if ( filesData.hasAlbum ) {
				$("#switchToAlbum").removeClass('toolbtn_off').addClass('toolbtn');
			}

			setSortIndicators();
			setFileListHandlers();
			$("#makeFolder").removeClass('toolbtn_off').addClass('toolbtn');
		},
		error         : function( data, stat, err ) {
			console.log( data, stat, err );
		}
	});
}

function createFolderPath( folderData ) {
	var out = [ '<span class="topNavLink" currentUserID="' + currentUserID + '" folder=0 title="Начало">&equiv;</span>' ];
	for ( a in folderData ) {
		out.push('<span class="topNavLink" currentUserID="' + currentUserID + '" folder="' + folderData[a].id + '" title="' + folderData[a].folderName + '">' + folderData[a].folderName + '</span>');
	}
	return out.join("&nbsp;/&nbsp;");
}

function setSubstitutionalViewHandler() {
	$('#switchToWeb, #switchToDocs').removeClass("toolbtn-off").addClass("toolbtn");

	$('#switchToWeb, #switchToDocs').unbind().click(function() {
		if ( displayMode == "files" ) {
			displayMode = "index";
			$("#htmlSubstitution").empty().append( filesData.html );
			$("#html, #htmlSubstitution, #switchToDocs").removeClass("hide");
			$("#folders, #files, #mainHeader, #album, #switchToWeb").addClass("hide");
			//$('#fi_switch').empty().attr("title", "Переключиться к документам").append('<img class="ico" src="/images/ico/folder_page.png">');
			return true;
		}
		displayMode = "files";
		$("#folders, #files, #mainHeader, #switchToWeb").removeClass("hide");
		$("#html, #htmlSubstitution, #album, #switchToDocs").addClass("hide");
		//$('#fi_switch').empty().attr("title", "Переключиться к веб-странице").append('<img class="ico" src="/images/ico/gost.png">');
		//resizeTabHeader('filelist');
	})
	.removeClass('toolbtn_off')
	.addClass('toolbtn');
}

function setSortIndicators(){
	//console.log("setting");
	var modes = { 1 : "up", 2 : "down" };
	$(".sorter").addClass("hide");
	for ( a in sortPattern ) {
		$(".sorter[ref=" + a + "][direction=" + modes[sortPattern[a]] +"]").removeClass("hide");
	}
}

function getFilesTable(data) {
	var output = [];
	for (a in data) {
		output.push( getFileItem(data[a]) );
	}
	return output;
}

function getFoldersTable(data, current, parent) {
	var output = [];
	for (a in data) {
		output.push( getFolderItem(data[a]) );
	}
	if ( current ) {
		output.unshift(getFolderItem({
			id                : parent,
			comments          : "Верхний уровень",
			folderName        : "..",
			parent            : current,
			humanCreationDate : '<..>',
			humanDeletionDate : '<..>',
		}));
	}
	return output;
}

function getFileItem(item) {
	return '<tr class="trlist" itemType="file" userID="' + item.userID + '" itemID="' + item.id + '">' + 
	'<td class="cflist col1">' +
		'<input type="checkbox" class="itemChecker" itemType="file" userID="' + item.userID + '" itemID="' + item.id + '">' + 
	'</td>' + 
	'<td class="cflist t_filename col2" itemType="file" userID="' + item.userID + '" itemID="' + item.id + '">' +
		'<img class="ico" src="/images/ico/ext/' + item.fileType + '.png">&nbsp;' +
		'<a class="fhref" itemType="file" userID="' + item.userID + '" itemID="' + item.id + '" target="_blank" title="' + item.comments + '" href="/storage/download/' + item.userID + '/' + item.id + '">' +
			item.originalFilename +
		'</a>' +
	'</td>' +
	'<td class="cflist col3">' + item.fileSize + '</td>' +
	'<td class="cflist col4">' + item.humanCreationDate + '</td>' +
	'<td class="cflist col5">' + item.humanDeletionDate + '</td>' +
	'</tr>';
}

function getFolderItem(item) {
	return '<tr class="trlist" itemType="folder" userID="' + item.userID + '" itemID="' + item.id + '">' +
		'<td class="cflist col1">' +
			'<input type="checkbox" class="itemChecker" itemType="folder" userID="' + item.userID + '" itemID="' + item.id + '">' +
		'</td>' +
		'<td class="cflist t_filename col2" itemType="folder" userID="' + item.userID + '" itemID="' + item.id + '">' +
			'<img class="ico" src="/images/ico/folder.png">&nbsp;' +
			'<a class="fhref" href="#" itemType="folder" userID="' + item.userID + '" itemID="' + item.id + '" title="' + item.comments + '">' + item.folderName + '</a>' +
		'</td>' +
		'<td class="cflist col3">' +
			'<acronym title="Папок: ' + item.foldersCount + ' / Файлов: ' + item.filesCount + '">&lsaquo; Папка &rsaquo;</acronym>' +
		'</td>' +
		'<td class="cflist col4" ref="' + item.creationDate + '">' + item.humanCreationDate + '</td>' +
		'<td class="cflist col5" ref="' + item.deletionDate + '">' + item.humanDeletionDate + '</td>' +
	'</tr>';
}

//////////////////////////////////////////////////////////////////////////////////////
function change_folder( folder ) {
	// переменные в global scope
	currentFolder = folder;
	showUserFiles( currentUserID, 0, currentFolder );
}

//////////////////////////////////////////////////////////////////////////////////////
function saveObjectInfo(itemID, itemType) {

	var comment       = $('.jsonTarget[ref=' + itemType + '][field="comments"]').val(),
		deletionDate  = $('.jsonTarget[ref=' + itemType + '][field="deletionDate"]').val(),
		downloadlimit = $('.jsonTarget[ref=' + itemType + '][field="downloadLimit"]').val();

	comment = (comment === undefined) ? '' : comment;

	$.ajax({
		type              : 'POST',
		url               : '/storage/saveinfo',
		data              : {
			userID        : currentUserID,
			itemType      : itemType,
			itemID        : itemID,
			deletionDate  : deletionDate,
			downloadlimit : downloadlimit,
			comment       : comment,
			comment_len   : comment.length
		},
		datatype          : 'json',
		success           : function(data, status) {
			//$('#save_comment').removeClass('toolbtn').addClass('toolbtn_off').off('click');
			close_dialog();
		},
		error             : function( data, stat, err ) {
			console.log( data, stat, err );
		}

	});
}

//////////////////////////////////////////////////////////////////////////////////////

function setFileListHandlers() {


	$(".srt_colmn").unbind().click( function(e) {
		var ref = $(this).attr('ref');
		sortPattern[ref]++;
		for ( i in sortPattern ) {
			if ( i == ref ) { continue; }
			sortPattern[i] = 0;
		}
		sortPattern[ref] = (sortPattern[ref] > 2) ? 1 : sortPattern[ref];
		setSortIndicators();
		sortFiles(filesData.files, sortPattern);
	});

	$('.trlist').unbind().on("contextmenu", function(e) {
		if ( nativeRightClick ) {
			e.preventDefault();
		}
		/* setting global scope */
		itemID          = $(this).attr("itemid");
		currentUserID   = $(this).attr("userid");
		itemType        = $(this).attr("itemtype");
		$("#actionList").css({
			left : e.originalEvent.x + "px",
			top  : e.originalEvent.y + "px"
		}).fadeIn(300);
	});

	$('.fhref[itemtype=folder]').unbind().click(function(){
		change_folder( $(this).attr("itemID") );
	});

	$(".itemChecker").unbind().click( function() {
		if ( $(".itemChecker:checked").length ) {
			$(".deleteObject, .makeZip").removeClass("toolbtn_off").addClass("toolbtn");
			return true;
		}
		$(".deleteObject, .makeZip").removeClass("toolbtn").addClass("toolbtn_off");
	});

	$("#ffc_header").click( function() {
		$('.cflist input[type=checkbox]').prop('checked', $(this).prop('checked'));
	});

	$(".topNavLink, .specialResource").click( function() {
		var userID   = $(this).attr("currentUserID"),
			folderID = $(this).attr("folder");
		showUserFiles( userID, 0, folderID )
	})
}

function sortFiles(srcArray, sortPattern) {
	var sortFields = {
		0 : "originalFilename",
		1 : "fileSize",
		2 : "creationDate",
		3 : "deletionDate"
	},
	sortFunctions = {
		"originalFilename" : function( a, b, rev ) {
			var x = a[sortFields[0]].toLowerCase(),
				y = b[sortFields[0]].toLowerCase();
			if ( x < y ) { return -1 * rev; }
			if ( x > y ) { return  1 * rev; }
			return 0;
		},
		"fileSize"         : function( a, b, rev ) {
			return (parseInt(a[sortFields[1]], 10) - parseInt(b[sortFields[1]], 10)) * rev;
		},
		"creationDate"     : function( a, b, rev ) {
			return (parseInt(a[sortFields[2]], 10) - parseInt(b[sortFields[2]], 10)) * rev;
		},
		"deletionDate"     : function( a, b, rev ) {
			return (parseInt(a[sortFields[3]], 10) - parseInt(b[sortFields[3]], 10)) * rev;
		}
	};
	for ( d in sortPattern ) {
		if ( sortPattern[d] == 0 ) { continue; }
		rev = (sortPattern[d] == 2) ? -1 : 1;
		srcArray.sort( function( a, b ) {
			return sortFunctions[sortFields[d]]( a, b, rev );
		});
		$('#files').empty().append( getFilesTable(srcArray) );
		break;
	}
}

//////////////////////////////////////////////////////////////////////////////////////
function ajaxFileUpload() {
	if ( currentUserID.length ) {
		$.ajaxFileUpload({
			url           : 'upload_file.asp',
			secureuri     : false,
			fileElementId : 'fileToUpload',
			dataType      : 'json',
			data          : {
				file_ttl  : $('#file_ttl').val(),
				fio_id    : currentUserID,
				folder    : currentFolder
			},
			success       : function(data) {
				showUserFiles(currentUserID, 1, currentFolder );
				return true;
			},
			error         : function( data, stat, err ) {
				console.log( data, stat, err );
			}
		})
	}
	alert('ОШИБКА: Не выбрана папка для загрузки файла.');
	return false;
}

//////////////////////////////////////////////////////////////////////////////////////

function add_user( userName ) { // Атестовый Апользователь
	if ( userName.length ) {
		$.ajax({
			type          : 'POST',
			url           : 'post_action.asp?cmd=create_user',
			data          : {
				cmd       : 'create_user',
				fio       : userName,
				fio_len   : userName.length
			},
			datatype      : 'html',
			success       : function( data ) {
				$('#faddi').val('');
				show_FIO_list( $('#fio_search').val() );
				return false;
			},
			error         : function( data, stat, err ) {
				console.log( data, stat, err );
			}
		});
	}
	return true;
}

//////////////////////////////////////////////////////////////////////////////////////

function enable_rm_func() {
	enable_rm_usr = !enable_rm_usr;
	$('.it_controls label').css('color', ( (enable_rm_usr) ? '#ff0000' : '#000000'));
	show_FIO_list($('#fio_search').val());
}

//////////////////////////////////////////////////////////////////////////////////////

function delete_currentUserID(userID) {

	// TODO: попытаться вывести диалог с фамилией на подтверждение удаления

	$.ajax({
		type          : 'POST',
		url           : 'post_action.asp?cmd=delete_user',
		data          : {
			cmd       : 'delete_user',
			fid       : userID
		},
		datatype      : 'html',
		success       : function( data ) {
			show_FIO_list( $('#fio_search').val() );
		},
		error         : function( data, stat, err ) {
			console.log( data, stat, err );
		}
	});
}

//////////////////////////////////////////////////////////////////////////////////////
