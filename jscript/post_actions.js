	function collectSelectedItems() {
		var obj           = $('.itemChecker:checked'),
			selectedItems = { folders : [], files : [], userID : 0 };
		if ( obj.length ) {
			$(obj).each( function() {
				var item  = $(this).attr("itemid"),
					user  = $(this).attr("userid"),
					type  = $(this).attr("itemtype");
				selectedItems[type + "s"].push( item );
				selectedItems.userID = user;
			});
			return selectedItems;
		}
		selectedItems[itemType + "s"].push(itemID);
		selectedItems.userID = currentUserID;
		return selectedItems;
	}

	//////////////////////////////////////////////////////////////////////////////////////

	$('#actn').on({ // Показать новые (сегодняшние) файлы
		click: function() {

			new_files_list = {
				type: 'GET',
				url: 'post_action.asp?cmd=get_new_files',
				data: {
					fio_id: currentUserID
				},
				datatype: 'html',
				success: function(data, status) {
					dialog_content = data;
				},
				error         : function( data, stat, err ) {
					console.log( data, stat, err );
				}
			};
			$.ajax(new_files_list);

			dialog_content += '<div style="padding: 6px;color: #aaa;">Файлы, загруженные за последние 7 дней.</div>';

			$('#dialog_header').html('Новые файлы');
			$('#dialog_content').html('');
			$('#dialog_content').html(dialog_content);

			$('.dialog_window').fadeIn(200);
			//$('#modal_fader').fadeIn(200);
		}
	});

	//////////////////////////////////////////////////////////////////////////////////////
	// Обновить список файлов
	//$('#switchToDocs').click(function() {
	//	$(this).addClass("hide");
	//	$('#switchToWeb').removeClass("hide");
	//	showUserFiles(currentUserID, 1, currentFolder);
	//});
	//////////////////////////////////////////////////////////////////////////////////////
	 // Создать новую папку
	$('#act1, #makeFolder').click( function() {
		$('#dialogs').empty().load("/post/dialog/createFolder", function() {
			makeDialogWindowDraggable();
			setDialogWindowCloser();
			setCreateFolderHandler()
			$('#new_folder').focus();
		});
	});

	function setCreateFolderHandler() {
		$('#new_folder').unbind().keydown( function(event) {
			if (event.which == 13) {
				makeNewFolder();
				close_dialog();
			}
		});
		$('#create_folder').unbind().click( function() {
			makeNewFolder();
			close_dialog();
		});
	}

	function makeNewFolder() {
		var folderName = $('#new_folder').val();
		if ( folderName.length ) {
			$.ajax({
				type           : 'POST',
				url            : '/storage/createfolder',
				data           : {
					userID     : currentUserID,
					folderID   : currentFolder,
					folderName : folderName
				},
				datatype       : 'html',
				success        : function( data ) {
					showUserFiles(currentUserID, 1, currentFolder);
				},
				error          : function( data, stat, err ) {
					console.log( data, stat, err );
				}
			});
		}
	}

	//////////////////////////////////////////////////////////////////////////////////////
	
	// Редактировать index.html
	$('#act2').click( function() {
		$("#htmlSubstitution").empty().load("/post/htmleditor/" + currentUserID, function() {
			$('.saveHTML').click( function() {
				$.ajax({
					type         : 'POST',
					url          : 'storage/saveHTML',
					data         : {
						userID   : currentUserID,
						content  : editor.getData()
					},
					datatype     : 'html',
					success: function( data ) {
						showUserFiles(currentUserID, 0, currentFolder);
					},
					error        : function( data, stat, err ) {
						console.log( data, stat, err );
					}
				});
			});
			$("#html, #htmlSave").removeClass("hide");
			$("#files, #folders, #mainHeader, #upload").addClass("hide");
		});
	});


	//////////////////////////////////////////////////////////////////////////////////////

	function distributeJSON (jsonData, reference) {
		for ( a in jsonData ) {
			if ( $(".jsonTarget[ref=" + reference + "][field=" + a + "]").prop("nodeName") == "TEXTAREA" ) {
				$(".jsonTarget[ref=" + reference + "][field=" + a + "]").html(jsonData[a]);
			}
			if ( $(".jsonTarget[ref=" + reference + "][field=" + a + "]").prop("nodeName") == "INPUT"    ) {
				$(".jsonTarget[ref=" + reference + "][field=" + a + "]").val(jsonData[a]);
			}
			if ( $(".jsonTarget[ref=" + reference + "][field=" + a + "]").prop("nodeName") == "SPAN"     ) {
				$(".jsonTarget[ref=" + reference + "][field=" + a + "]").html(jsonData[a]);
			}
			if ( $(".jsonTarget[ref=" + reference + "][field=" + a + "]").prop("nodeName") == "TD"       ) {
				$(".jsonTarget[ref=" + reference + "][field=" + a + "]").html(jsonData[a]);
			}
		}
	}

	// Свойства файла / папки (V)
	$('#act3').click( function() {
		if ( itemType == 'file' ) {
			$.ajax({
				type       : 'POST',
				url        : '/storage/getfileinfo',
				data       : {
					userID : currentUserID,
					itemID : itemID
				},
				dataType   : 'json',
				success    : function(data) {
					$('#dialogs').empty().load("/post/dialog/fileInfo", function() {
						makeDialogWindowDraggable()
						setDialogWindowCloser();
						distributeJSON(data, "fileInfo");
						$('.saveInfo').unbind().click( function() {
							saveObjectInfo(itemID, "fileInfo");
							showUserFiles(currentUserID, 1, currentFolder);
						});
					});
				},
				error      : function( data, stat, err ) {
					console.log( data, stat, err );
				}
			});
		}

		if ( itemType == 'folder' ) {
			$.ajax({
				type       : 'POST',
				url        : '/storage/getfolderinfo',
				data       : {
					userID : currentUserID,
					itemID : itemID
				},
				dataType   : 'json',
				success    : function(data) {
					$('#dialogs').empty().load("/post/dialog/folderInfo", function() {
						makeDialogWindowDraggable()
						setDialogWindowCloser();
						distributeJSON(data, "folderInfo");
						$('.saveInfo').unbind().click( function() {
							saveObjectInfo(itemID, "folderInfo");
							showUserFiles(currentUserID, 1, currentFolder);
						});
					});
				},
				error      : function( data, stat, err ) {
					console.log( data, stat, err );
				}
			});
		}
	});

	//////////////////////////////////////////////////////////////////////////////////////
	// Переименовать
	$('#act4').click( function() {
		var selectedItems = collectSelectedItems();
		$('#actionList').fadeOut(50);
		for ( a in selectedItems.files ) {
			processRenameRow($(".itemChecker[itemType=file][itemID=" + selectedItems.files[a] + "]"));
		}
		for ( a in selectedItems.folders ) {
			processRenameRow($(".itemChecker[itemType=folder][itemID=" + selectedItems.files[a] + "]"));
		}
		setRenameEvent();
	});

	function processRenameRow(itemSrc) {
		var item     = $(itemSrc).attr("itemid"),
			user     = $(itemSrc).attr("userid"),
			type     = $(itemSrc).attr("itemtype"),
			flex     = [ "itemType=" + type, "userID=" + user, "itemID=" + item ],
			fileName = $(".fhref[" + flex.join("][") + "]").text();
		$(".fhref[" + flex.join("][") + "]").remove();
		$(".cflist.col2[" + flex.join("][") + "]").append('<input type="text" class="fileNameInput" ' + flex.join(" ") + ' value="' + fileName + '">');
	}

	function setRenameEvent() {
		$('.fileNameInput').unbind().keydown(function(event) {
			// if enter
			if ( event.which == 13 ) {
				var item = $(this),
					flex = "[itemtype=" + item.attr("itemType") + "][userID=" + item.attr("userID") + "][itemID=" + item.attr("itemID") + "]";
				$.ajax({
					type         : 'POST',
					url          : 'storage/saveItemName',
					data         : {
						userID   : item.attr("userID"),
						itemType : item.attr("itemType"),
						itemID   : item.attr("itemID"),
						newName  : item.val()
					},
					datatype     : 'html',
					success      : function(data, status) {
						$(item).remove();
						$(".cflist.col2" + flex).append('<a class="fhref" itemType="' + item.attr("itemType") + '" userID="' + item.attr("userID") + '" itemID="' + item.attr("itemID") + '" target="_blank" title="" href="/storage/download/' + item.attr("userID") + '/' + item.attr("itemID") + '">' + item.val() + '</a>');
						setRenameEvent();
					},
					error        : function( data, stat, err ) {
						console.log( data, stat, err );
					}
				});
			}
		});
	}

	//////////////////////////////////////////////////////////////////////////////////////
	/*
	$('#act5').on({ // Скопировать к пользователю
		click: function() {
			doNotify('Извините, но эта функция пока не реализована.');
		}
	});
	*/

	//////////////////////////////////////////////////////////////////////////////////////
	// Переместить к пользователю
	$('#act6').click( function() {
		var selectedItems = collectSelectedItems();

		$('#dialogs').empty().load("/post/dialog/copyToUser", function() {
			makeDialogWindowDraggable()
			setDialogWindowCloser();
			setMoveToUserEventHandler(selectedItems);
			$(".copyToFilesCount").html(selectedItems.files.length);
			$(".copyToFoldersCount").html(selectedItems.folders.length);
			leftMenuMode = "pickUp";
			setFIOListItemHandler();
		});
	});

	function setMoveToUserEventHandler( selectedItems ) {
		$('#copyToUser').click( function() {
			$.ajax({
				type        : 'POST',
				url         : '/storage/moveToUser',
				data        : {
					items        : selectedItems,
					targetUserID : $('#targetUser').val(),
					leaveAcopy   : ( $('#leaveMeACopy').prop("checked") ) ? 1 : 0
				},
				datatype    : 'json',
				success     : function( data ) {
					leftMenuMode = "normal";
					setFIOListItemHandler();
					showUserFiles(currentUserID, 1, currentFolder);
					close_dialog();
				},
				error       : function( data, stat, err ) {
					console.log( data, stat, err );
					close_dialog();
					$("#debugWindow")
					.empty()
					.click( function() { $(this).addClass("hide") })
					.html(data.responseText)
					.removeClass("hide");
				}
			});
		});
	}

	//////////////////////////////////////////////////////////////////////////////////////
	// Скопировать в папку
	$('#act7').click( function() {
		var selectedItems = collectSelectedItems();
		$.ajax({
			type         : 'POST',
			url          : '/storage/getFolderList',
			data         : {
				userID   : currentUserID,
				folderID : currentFolder,
			},
			datatype     : 'html',
			success      : function( data ) {
				$('#dialogs').empty().load("/post/dialog/copyToFolder", function() {
					makeDialogWindowDraggable();
					$('.dialog_window[ref=copyToFolder]').removeClass("hide").fadeIn(300);
					$('#folderSelector').append(data);
					setCopyItemsEventHandler(selectedItems);
					setDialogWindowCloser();
				});

			},
			error        : function( data, stat, err ) {
				console.log( data, stat, err );
			}
		});
	});

	function setCopyItemsEventHandler(selectedItems) {
		$('#copyToFolder').click( function() {
			$.ajax({
				type             : 'POST',
				url              : '/storage/copyToFolder',
				data             : {
					userID       : currentUserID,
					items        : selectedItems,
					targetFolder : $('#folderSelector').val()
				},
				datatype         : 'html',
				success          : function(data, status) {
					showUserFiles(currentUserID, 1, currentFolder);
					close_dialog();
				},
				error            : function( data, stat, err ) {
					console.log( data, stat, err );
				}
			});
		});
	}

	//////////////////////////////////////////////////////////////////////////////////////

	$('#act8').on({ // Переместить в папку
		click: function() {

			obj = $('.cflist input[type=checkbox]:checked');

			if (obj.length == 0) {
				doNotify('Надо сперва выбрать элемент(ы) для перемещения.');
			} else {

				var selectedItems = new Array();
				for(i = 0; i < obj.length; i++) { selectedItems.push(obj[i].id); }
				var o_list = selectedItems.join(',');

				var sel_options = ''
				folder_list_move_to = {
					type: 'POST',
					url: 'post_action.asp?cmd=folder_list_to',
					data: {
						cmd: 'folder_list_to',
						objects: o_list,
						folder: current_folder,
						fio_id: currentUserID
					},
					datatype: 'html',
					success: function(data, status) {
						sel_options = data;
					},
					error         : function( data, stat, err ) {
						console.log( data, stat, err );
					}
				};
				$.ajax(folder_list_move_to);

				dialog_content = '<table border="0" width="100%">';
				dialog_content += '<tr>';
				dialog_content += '<td class="dialog_td_l">Папка:</td>'
				dialog_content += '<td class="dialog_td_r"><select id="to_folder">'+sel_options+'</select></td>';
				dialog_content += '</tr>';
				dialog_content += '</table>';

				$('#dialog_header').html('Переместить в папку');
				$('#dialog_content').html('');
				$('#dialog_content').html(dialog_content);
				$('.dialog_other_buttons').html('<a href="#" class="toolbtn" id="move_to_folder">&nbsp; Переместить &nbsp;</a>');

				$('#move_to_folder').on({
					click: function() {

						move_to_folder = {
							type: 'POST',
							url: 'post_action.asp?cmd=move_to_folder',
							data: {
								cmd: 'move_to_folder',
								fio_id: currentUserID,
								objects: o_list,
								folder: current_folder,
								folder_to: $('#to_folder').val()
							},
							datatype: 'html',
							success: function(data, status) {
								showUserFiles(currentUserID, 1, current_folder);
								close_dialog();
							},
							error         : function( data, stat, err ) {
								console.log( data, stat, err );
							}
						};
						$.ajax(move_to_folder);
					}
				});

				$('.dialog_window').fadeIn(200);
				//$('#modal_fader').fadeIn(200);
				$('#to_folder').focus();

			}
		}
	});



	//////////////////////////////////////////////////////////////////////////////////////
	// Удалить файл/папку
	$('#deleteObject, .deleteObject').click( function() {
		if ($(this).hasClass("toolbtn_off")) { return false; }
		$('#actionList').fadeOut(50);
		selectedItems = collectSelectedItems();

		$('#dialogs').empty().load("/post/dialog/deleteItems", function() {
			$('.dialog_window[ref=deleteItems]').removeClass("hide").fadeIn(300);
			$(".foldersToRemoveCount").html(selectedItems['folders'].length);
			$(".filesToRemoveCount").html(selectedItems['files'].length);
			makeDialogWindowDraggable()
			setDialogWindowCloser();
			setDeleteEvent(selectedItems);
		});
	});

	function setDeleteEvent(selectedItems) {
		$('#delete_confirm').unbind().click(function() {
			( $(this).prop('checked') )
				? $('#delete_box').addClass("active")
				: $('#delete_box').removeClass("active")
		});
		$('.delete_objects').removeClass('toolbtn_off').addClass('toolbtn');
		$('.delete_objects').unbind().click( function() {
			if ( $('#delete_confirm').prop('checked') ) {
				$.ajax({
					type        : 'POST',
					url         : 'storage/deleteItems',
					data        : {
						userID  : currentUserID,
						itemsID : selectedItems
					},
					datatype    : 'html',
					success     : function(data, status) {
						showUserFiles(currentUserID, 1, currentFolder);
						close_dialog();
					},
					error       : function( data, stat, err ) {
						console.log( data, stat, err );
					}
				});
			}
		});
	}

	//////////////////////////////////////////////////////////////////////////////////////
	// Создать фотоальбом
	function createPhotoAlbum() {
		$.ajax({
			type         : 'POST',
			url          : '/storage/makeAlbum',
			data         : {
				userID   : currentUserID,
				folderID : currentFolder
			},
			datatype     : 'html',
			success      : function(data) {
				$("#folders, #files, #filler, #switchToDocs, #mainHeader, #htmlSubstitution").addClass("hide");
				$("#album, #switchToWeb").removeClass("hide");
				$("#albumContainer").empty().html(data);
				$("#switchToAlbum").removeClass("toolbtn_off").addClass("toolbtn");
			},
			error        : function( data, stat, err ) {
				console.log( data, stat, err );
			}
		});
	}

	//////////////////////////////////////////////////////////////////////////////////////
	// Продлить жизнь файлов
	$('#actb').click( function() {
		selectedItems = collectSelectedItems();

		$('#dialogs').empty().load("/post/dialog/prolongateFiles", function() {
			makeDialogWindowDraggable();
			$('.dialog_window[ref=prolongateFiles]').removeClass("hide").fadeIn(300);
			$('.prolongatedFilesCount').html(   selectedItems["files"].length   );
			$('.prolongatedFoldersCount').html( selectedItems["folders"].length );
			setDialogWindowCloser();
			$('#prolongateFor').focus();
			setProlongationEventHandler();
		});
	});

	function setProlongationEventHandler() {
		$('#prolongateItems').unbind().click(function() {
			$.ajax({
				type        : 'POST',
				url         : '/storage/prolongateFiles',
				data        : {
					items   : selectedItems,
					period  : $('#prolongateFor').val()
				},
				datatype    : 'html',
				success     : function(data, status) {
					showUserFiles(currentUserID, 1, currentFolder);
					close_dialog();
				},
				error       : function( data, stat, err ) {
					console.log( data, stat, err );
				}
			});
		});
	}

	//////////////////////////////////////////////////////////////////////////////////////
	// Скачать отмеченные файлы (V)
	$('#actz').click( function() {
		$('#actionList').fadeOut(50);
		selectedItems = collectSelectedItems();

		$('#dialogs').empty().load("/post/dialog/downloadFiles", function() {
			makeDialogWindowDraggable();
			$('.dialog_window[ref=downloadFiles]').removeClass("hide").fadeIn(300);
			$('.downloadFilesCount').html( selectedItems["files"].length + selectedItems["folders"].length );
			setDialogWindowCloser();
			$('#bulk_download').click( function() {
				for ( i in selectedItems["files"] ) {
					$('#multidownloader').append('<iframe src="/storage/download/' + selectedItems["userID"] + '/' + selectedItems["files"][i] + '/" width="1" height="1"></iframe>');
				}
				close_dialog();
			});
		});
	});

	//////////////////////////////////////////////////////////////////////////////////////
	// Заархивировать отмеченные файлы
	$('.makeZip').click( function() {
		var selectedItems = collectSelectedItems()
		$('#dialogs').empty().load("/post/dialog/makeZip", function() {
			makeDialogWindowDraggable();
			$('.dialog_window[ref=makeZip]').removeClass("hide").fadeIn(300);
			$('.selectedItemsCount').html( selectedItems["files"].length + selectedItems["folders"].length );
			$('#makeZip').removeClass('toolbtn_off').addClass('toolbtn');
			date = new Date();
			$('#zipName').val( currentUserID + "_" + date.getDate() + "." + date.getMonth() + "." + date.getFullYear() + "_" + date.getHours().toString().padStart(2, '0') + ":" + date.getMinutes().toString().padStart(2, '0') + ":" + date.getSeconds().toString().padStart(2, '0') + ".zip" );
			setDialogWindowCloser();
			setMakeZipEventHandler( selectedItems );
		});
	});

	function setMakeZipEventHandler( selectedItems ) {
		$('#makeZip').click( function() {
			$.ajax({
				type           : 'POST',
				url            : '/storage/makezip',
				data           : {
					userID     : currentUserID,
					items      : selectedItems,
					zipName    : $('#zipName').val(),
					leaveAcopy : ( $("#leaveAcopy").prop("checked") ) ? 1 : 0
				},
				datatype       : 'html',
				success        : function( data ) {
					showUserFiles(currentUserID, 1, currentFolder);
					close_dialog();
				},
				error          : function( data, stat, err ) {
					console.log( data, stat, err );
				}
			});
		});
	}

	//////////////////////////////////////////////////////////////////////////////////////