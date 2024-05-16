<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Storage extends CI_Controller {

	public function __construct() {
		parent::__construct();
		if ( !opcache_is_script_cached("index.php") ) {
			opcache_compile_file("index.php");
		}
		$this->load->model("sharedfunctions");
		$this->load->model("filesystem");
		header('Content-Type: application/json');
	}

	private $operationsLogFileName = "logs/operations.log";
	private $downloadLogFileName   = "logs/download.log";
	private $uploadLogFileName     = "logs/upload.log";
	private $filesCount            = array();
	private $foldersCount          = array();
	private $sortSymbols           = array(
		'&nbsp;',
		'<span class="srt_symbol">&#x25B2;</span>',
		'<span class="srt_symbol">&#x25BC;</span>'
	);
	private $listD                 = array(); // буфер для переносимых папок
	private $listC                 = array(); // буфер для путей папок
	private $logOutput             = array();

	private function sendHeaders( $fileData, $fileName ) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$fileData["originalFilename"]); 
		header('Content-Transfer-Encoding: binary');
		header('Connection: Keep-Alive');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: '.filesize($fileName));
	}

	private function isDownloadPermitted( $fileData, $userID, $JSONFileName, $JSONfile, $fileID ) {
		if ( $fileData['downloadLimit'] == 0 ) {
			$string = array_push( $this->sharedfunctions->getMinimalLog( $userID ),
				$fileID,
				$fileData["storageFilename"],
				$fileData["originalFilename"],
				"Достигнут лимит скачиваний файла"
			);
			$this->sharedfunctions->writeToLog( implode("\t", $string), $this->downloadLogFileName );
			header("HTTP/1.0 404 Not Found");
			return false;
		}
		$JSONfile['files'][$key]["downloadLimit"] = ($JSONfile['files'][$key]["downloadLimit"] <= -1)
			? -1
			: $JSONfile['files'][$key]["downloadLimit"] - 1;
		file_put_contents( $JSONFileName, json_encode($JSONfile) );
		return true;
	}

	public function download( $userID, $fileID ) {
		$JSONFileName = $this->sharedfunctions->getContentFilePath( $userID );
		$JSONfile     = json_decode( file_get_contents($JSONFileName), true );
		foreach( $JSONfile['files'] as $key=>$fileData ) {
			if ( $fileData['id'] == $fileID ) {
				if ( !$this->isDownloadPermitted( $fileData, $userID, $JSONFileName, $JSONfile, $fileID ) ) {
					continue;
				}

				$fileName = $this->sharedfunctions->getUserDirPath( $userID ).$fileData["storageFilename"];
				$this->sendHeaders( $fileData, $fileName );
				print file_get_contents($fileName);

				$string = array_push( $this->sharedfunctions->getMinimalLog( $userID ),
					$fileID,
					$fileData["storageFilename"],
					$fileData["originalFilename"],
					"Файл скачан с сервера"
				);
				$this->sharedfunctions->writeToLog( implode("\t", $string), $this->downloadLogFileName );
				return true;
			}
		}
	}

	private function composeStateStructure() {
		return array(
			"userID"         => $this->input->post("userID"),
			"parentFolderID" => 0,
			"folderID"       => $this->input->post("folderID"),
			"sortMode"       => $this->input->post("sorts")
		);
	}

	public function getFileList() {
		$state = $this->composeStateStructure();

		$filename  = $this->sharedfunctions->getContentFilePath( $state["userID"] );
		$HTMLFile  = $this->sharedfunctions->getUserDirPath( $state["userID"] )."index.html" ;
		$albumFile = $this->sharedfunctions->getUserDirPath( $state["userID"] )."photoalbum-".$state["folderID"].".html";

		if ( !file_exists($filename) ) {
			$this->filesystem->writeEmptyFileList( $state["userID"] );
		}

		$fileList     = json_decode(file_get_contents($filename), true);
		$this->getFolderPath($filelist["folders"], $state["folderID"]);	// заполняем $this->listC

		$data = array(
			/*  порядок вызова files-folders важен  :) пока */
			"files"        => $this->collectFilesList( $fileList["files"], $state ),
			"folders"      => $this->collectFoldersList( $fileList["folders"], $state ),
			"folderData"   => array_reverse($this->listC),
			"userdata"     => $this->getUserData( $state["userID"] ),
			"treePosition" => $this->getCurrentPosition( $fileList["folders"], $state ),
			"hasHTML"      => file_exists( $HTMLFile  ),
			"hasAlbum"     => file_exists( $albumFile ),
			"html"         => ( file_exists($HTMLFile) ) ? file_get_contents($HTMLFile) : $this->load->view("filestoragetableempty", array(), true)
		);
		print json_encode($data);
		return true;
	}

	private function getFolderPath( $folders, $folderID ) {
		foreach ( $folders as $folder ) {
			if ( $folder["id"] == $folderID ) {
				$this->processSubfolders( $folders, $folder["id"] );
			}
		}
	}

	private function processSubfolders( $folders, $folderID ) {
		foreach ( $folders as $folder ) {
			if ( $folder["id"] == $folderID ) {
				array_push( $this->listC, $folder );
				$this->processSubfolders( $folders, $folder["parent"] );
			}
		}
		return true;
	}

	private function getCurrentPosition( $list, $state ) {
		$output = array(
			"folderName"  => array(
				"name"    => "..",
				"current" => 0,
				"parent"  => 0
			)
		);
		foreach ( $list as $folderItem ) {
			if ( $folderItem["id"] == $state["folderID"] ) {
				$output["folderName"] = array(
					"name"    => $folderItem["folderName"],
					"parent"  => $folderItem["parent"],
					"current" => $folderItem["id"]
				);
			}
		}
		return $output;
	}

	private function collectFilesList( $list, $state ) {
		//print_r($list);
		$output = array();
		foreach ( $list as $itemID => $storageItem ) {
			if ( !isset( $this->filesCount[$storageItem["parent"]] ) ) {
				$this->filesCount[$storageItem["parent"]] = 0;
			}
			$this->filesCount[$storageItem["parent"]] += 1;
			if ( $state["folderID"] != $storageItem["parent"] ) {
				continue;
			}
			$fileName                 = explode(".", $storageItem["originalFilename"]);
			$storageItem["fileType"]  = end($fileName);

			$storageItem["fileStyle"] = ($storageItem["originalFilename"] == "index.html") ? "fileType index" : "";
			$storageItem["fileSize"]  = ( isset($storageItem["fileSize"]) && $storageItem["fileSize"] )
				? $storageItem["fileSize"]
				: $this->filesystem->getFileSize( $state["userID"], $storageItem["id"] );
			$storageItem["humanCreationDate"] = date("d.m.Y H:i", $storageItem["creationDate"]);
			$storageItem["humanDeletionDate"] = ( $storageItem["deletionDate"] == "eternal" )
				? "-- / --"
				: date("d.m.Y H:i", $storageItem["deletionDate"]);
			unset($storageItem["storageFilename"]);
			array_push( $output, $storageItem);
		}
		return $output;
	}

	private function collectFoldersList( $list, $state ) {
		$output = array();
		// first pass -- statistics
		foreach ( $list as $storageItem ) {
			if ( !isset( $this->foldersCount[$storageItem["parent"]] ) ) {
				$this->foldersCount[$storageItem["parent"]] = 0;
			}
			$this->foldersCount[$storageItem["parent"]] += 1;
		}
		// second pass -- collecting data
		foreach ( $list as $storageItem ) {
			if ( $state["folderID"] != $storageItem["parent"] ) {
				continue;
			}
			$storageItem["foldersCount"] = ( isset($this->foldersCount[$storageItem["id"]]) ) 
				? $this->foldersCount[$storageItem["id"]]
				: 0;
			$storageItem["filesCount"]   = ( isset($this->filesCount[$storageItem["id"]]) ) 
				? $this->filesCount[$storageItem["id"]]
				: 0;
			$storageItem["humanCreationDate"] = date("d.m.Y H:i", $storageItem["creationDate"]);
			$storageItem["humanDeletionDate"] = ( $storageItem["deletionDate"] == "eternal" )
				? "-- / --"
				: date("d.m.Y H:i", $storageItem["deletionDate"]);
			array_push( $output, $storageItem );
		}
		return $output;
	}

	public function showIndex( $userID = 0 ) {
		$filename = $this->sharedfunctions->getUserDirPath( $userID )."index.html";
		header("Content-Type: text/html");
		print file_get_contents($filename);
	}

	private function getUserData( $userID = 0 ) {
		$filename = "userdata/userlist.json";
		$data     = json_decode( file_get_contents( $filename ), true );
		header("Content-Type: application/json");
		return $data[$userID];
	}

	public function makeAlbum( $userID = 0, $folderID = 0 ) {
		$userID   = ($userID)   ? $userID   : $this->input->post("userID");
		$folderID = ($folderID) ? $folderID : $this->input->post("folderID");
		$fileName = $this->sharedfunctions->getContentFilePath( $userID );
		$data     = json_decode( file_get_contents($fileName), true );
		$output   = array();
		foreach ( $data["files"] as $item ) {
			if ( $folderID === $item["parent"] ) {
				if ( preg_match( "/(jpg|jpeg|png|gif|webp)$/", $item["originalFilename"] ) ) {
					$string = '<a target="_new" class="photoalbumItem" of="'.$item["originalFilename"].'" href="/storage/download/'.$item['userID'].'/'.$item['id'].'"><img src="/storage/albumItem/'.$item['userID'].'/'.$item["storageFilename"].'">'.$item["originalFilename"].'</a>';
					array_push( $output, $string );
				}
			}
		}
		sort( $output );
		file_put_contents(
			$this->sharedfunctions->getUserDirPath( $userID )."photoalbum-".$folderID.".html",
			implode( "\n", $output )
		);

		print json_encode( $output );
	}

	public function albumItem( $userID, $imageID ) {
		print file_get_contents( $this->sharedfunctions->getUserDirPath( $userID ).$imageID );
	}

	public function getFileInfo( $userID = 0, $itemID = 0 ) {
		$userID   = ($userID) ? $userID : $this->input->post("userID");
		$itemID   = ($itemID) ? $itemID : $this->input->post("itemID");
		$fileName = $this->sharedfunctions->getContentFilePath( $userID );
		$data     = json_decode(file_get_contents($fileName), true);
		foreach ( $data["files"] as $item ) {
			if ( $item["id"] == $itemID ) {
				$item["creationDate"] = date( "d.m.Y H:i:s", $item["creationDate"] );
				$item["fileSize"]     = (!isset($item["fileSize"]) || $item["fileSize"] == 0)
					? filesize( $this->sharedfunctions->getUserDirPath( $userID ).$item["storageFilename"] )
					: $item["fileSize"];
				$item["deletionDate"] = date("Y-m-d", $item["deletionDate"]);
				unset($item["storageFilename"]);
				print json_encode($item);
				return true;
			}
		}
	}

	public function getFolderInfo( $userID = 0, $itemID = 0 ) {
		$userID   = ($userID) ? $userID : $this->input->post("userID");
		$itemID   = ($itemID) ? $itemID : $this->input->post("itemID");
		$fileName = $this->sharedfunctions->getContentFilePath( $userID );
		$data     = json_decode( file_get_contents($fileName), true );
		foreach ( $data["folders"] as $item ) {
			if ( $item["id"] == $itemID ) {
				$item["creationDate"] = date("d.m.Y", $item["creationDate"]);
				$item["deletionDate"] = date("d.m.Y", $item["deletionDate"]);
				print json_encode($item);
				return true;
			}
		}
	}

	public function saveInfo( $userID = 0, $itemID = 0 ) {
		//$this->output->enable_profiler(true);
		$userID   = ($userID) ? $userID : $this->input->post("userID");
		$itemID   = ($itemID) ? $itemID : $this->input->post("itemID");
		$filename = $this->sharedfunctions->getContentFilePath( $userID );
		$fileData = json_decode( file_get_contents($filename), true );
		if ( $this->input->post("itemType") == "folderInfo" ) {
			foreach ( $fileData['folders'] as $key => $item ) {
				if ( $itemID == $item["id"] ) {
					$fileData['folders'][$key]["comments"] = $this->input->post("comment");
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $item["userID"], $item["id"], $item["name"], "Добавлен комментарий к директории" );
					$this->sharedfunctions->writeToLog( implode("\t", $string), $this->operationsLogFileName );
				}
			}
		}
		if ( $this->input->post("itemType") == "fileInfo" ) {
			foreach ( $fileData['files'] as $key => $item ) {
				if ( $itemID == $item["id"] ) {
					$fileData['files'][$key]["comments"]     = $this->input->post("comment");
					$fileData['files'][$key]["deletionDate"] = date_format(date_create_from_format("Y-m-d", $this->input->post("deletionDate")), "U");
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $item["userID"], $item["id"], $item["name"], "Добавлен комментарий, установлена дата удаления: ".$this->input->post("deletionDate"));
					$this->sharedfunctions->writeToLog( implode("\t", $string), $this->operationsLogFileName );
				}
			}
		}
		file_put_contents($filename, json_encode($fileData));
	}

	public function saveItemName( ) {
		//$this->output->enable_profiler(true);
		$filename = $this->sharedfunctions->getContentFilePath( $this->input->post("userID") );
		$fileData = json_decode(file_get_contents($filename), true);
		if ( $this->input->post("itemType") == "file" ) {
			foreach ( $fileData["files"] as $key=>$item ) {
				if ( $item["id"] == $this->input->post("itemID") ) {
					$fileData["files"][$key]["originalFilename"] = preg_replace("/\[\/\\:\*\?\"<>\|\]/", "_", $this->input->post("newName"));
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $item["userID"], $item["id"], $item["originalFilename"], "Установлено новое публичное имя файла: ".$this->input->post("newName"));
					$this->sharedfunctions->writeToLog( implode("\t", $string), $this->operationsLogFileName );
					break;
				}
			}
		}
		if ( $this->input->post("itemType") == "folder" ) {
			foreach ( $fileData["folders"] as $key=>$item ) {
				if ( $item["id"] == $this->input->post("itemID") ) {
					$fileData["folders"][$key]["folderName"] = preg_replace("/\[\/\\:\*\?\"<>\|\]/", "_", $this->input->post("newName"));
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $item["userID"], $item["id"], $item["FolderName"], "Установлено новое каталога: ".$this->input->post("newName"));
					$this->sharedfunctions->writeToLog( implode("\t", $string), $this->operationsLogFileName );
					break;
				}
			}
		}
		file_put_contents($filename, json_encode($fileData));
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}

	private function deleteFiles( $fileData, $items ) {
		foreach ( $fileData["files"] as $key=>$item ) {
			if ( in_array( $item["id"], $items["files"] ) ) {
				$unlinkFile = $this->sharedfunctions->getUserDirPath( $item["userID"] ).$item["storageFilename"];
				if ( $this->filesystem->deleteFile( $unlinkFile ) ){
					unset( $fileData["files"][$key] );
				}
				$string = array_push( $this->sharedfunctions->getMinimalLog( $item["userID"] ), $item["id"], $item["storageFilename"], "Удалён файл: ".$item["originalFilename"] );
				$this->sharedfunctions->writeToLog( implode("\t", $string), $this->operationsLogFileName );
			}
		}
		return $fileData;
	}

	private function deleteFolders( ) {
		foreach ( $fileData["folders"] as $key=>$item ) {
			if ( in_array( $item["id"], $itemsID["folders"] ) ) {
				unset( $fileData["folders"][$key] );
				$string = array_push( $this->sharedfunctions->getMinimalLog( $item["userID"] ), $item["id"], $item["FolderName"], "Удалён каталог: ".$item["FolderName"] );
				$this->sharedfunctions->writeToLog( implode("\t", $string), $this->operationsLogFileName );
			}
		}
		return $fileData;
	}

	public function deleteItems( ) {
		$filename = $this->sharedfunctions->getContentFilePath( $this->input->post("userID") );
		$fileData = json_decode( file_get_contents($filename), true );
		$itemsID  = $this->input->post("itemsID");
		if ( isset( $itemsID["files"] ) ) {
			$fileData = $this->deleteFiles( $fileData, $items );
		}
		if ( isset( $itemsID["folders"] ) ) {
			$fileData = $this->deleteFolders( $fileData, $items );
		}
		file_put_contents( $filename, json_encode($fileData) );
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}

	public function getFreeSpace() {
		$this->filesystem->getFreeSpace();
	}

	public function uploadFiles( ) {
		$userID   = $this->input->post("userID");
		$filename = $this->sharedfunctions->getContentFilePath( $userID );
		$filelist = json_decode(file_get_contents($filename), true);
		$this->filesystem->makeUserDir( $userID );

		$newFileData = array(
			"id"               => $this->sharedfunctions->getUUID(),
			"userID"           => $userID,
			"parent"           => $this->input->post("folderID"),
			"storageFilename"  => $this->sharedfunctions->getUUID(),
			"originalFilename" => $_FILES["files"]['name'],
			"creationDate"     => date("U"),
			"deletionDate"     => date("U") + (60 * 60 * 24 * $this->input->post("period")),
			"ownerIP"          => $this->input->ip_address(),
			"ownerHost"        => "",
			"downloadLimit"    => "-1",
			"fileSize"         => $_FILES["files"]['size'],
			"tags"             => "",
			"comments"         => ""
		);
		move_uploaded_file( $_FILES["files"]["tmp_name"], $this->sharedfunctions->getUserDirPath( $newFileData["userID"] ).$newFileData["storageFilename"] );
		array_push( $filelist["files"], $newFileData );
		file_put_contents( $filename, json_encode($filelist) );

		$string = array( 
			date("Y-m-d H:i:s"),
			$this->input->ip_address(),
			$newFileData["userID"],
			$newFileData["id"],
			$newFileData["storageFilename"],
			"На сервер загружен файл: ".$newFileData["originalFilename"]
		);
		$this->sharedfunctions->writeToLog( implode("\t", $string), $this->uploadLogFileName );
	}
	/* jsonStorage operations */
	private function prolongateFiles ( $filelist, $fileData, $period ) {
		foreach ( $filelist["files"] as $key=>$itemData ) {
			if ( in_array( $itemData["id"], $fileData['files'] ) ) {
				$filelist["files"][$key]['deletionDate'] = ( $period == "eternal" )
					? $period
					: date("U") + ( $period * 60 * 60 * 24 );
				$string = array_push( $this->sharedfunctions->getMinimalLog( $fileData["userID"] ), $itemData["id"], $itemData["storageFilename"], "Продлён срок жизни файла: ".$itemData["originalFilename"]." на ".$period." дней" );
				array_push( $this->logOutput, implode("\t", $string) );
			}
		}
		return $filelist;
	}

	private function prolongateFolders( $filelist, $fileData, $period ) {
		foreach ( $filelist["folders"] as $key=>$itemData ) {
			if ( in_array( $itemData["id"], $fileData["folders"] ) ) {
				$filelist["folders"][$key]['deletionDate'] = ($period == "eternal")
					? $period
					: date("U") + ( $period * 60 * 60 * 24 );
				$string = array_push( $this->sharedfunctions->getMinimalLog( $fileData["userID"] ), $itemData["id"], $itemData["folderName"], "Продлён срок жизни каталога: ".$itemData["folderName"]." на ". $period." дней" );
				array_push($this->logOutput, implode("\t", $string));
			}
		}
		return $filelist;
	}

	public function prolongateItems( ) {
		$this->logOutput = array();
		$fileData  = $this->input->post("items");
		$filename  = $this->sharedfunctions->getContentFilePath( $fileData["userID"] );
		$filelist  = json_decode(file_get_contents($filename), true);
		if ( isset( $fileData["files"] ) ) {
			$filelist = $this->prolongateFiles( $filelist, $fileData, $this->input->post("period") );
		}
		if ( isset($fileData["folders"]) ) {
			$filelist = $this->prolongateFolders( $filelist, $fileData, $this->input->post("period") );
		}
		file_put_contents( $filename, json_encode($filelist) );
		$this->sharedfunctions->writeToLog( implode("\r\n", $this->logOutput), $this->operationsLogFileName );
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}

	public function createFolder( ) {
		$userID        = $this->input->post("userID");
		$filename      = $this->sharedfunctions->getContentFilePath( $userID );
		$filelist      = json_decode(file_get_contents($filename), true);
		$newFolderData = array(
			"id"               => $this->sharedfunctions->getUUID(),
			"userID"           => $userID,
			"parent"           => $this->input->post("folderID"),
			"folderName"       => $this->input->post("folderName"),
			"creationDate"     => date("U"),
			"deletionDate"     => date("U") + (60 * 60 * 24 * 90),
			"ownerIP"          => $this->input->ip_address(),
			"ownerHost"        => "",
			"tags"             => "",
			"comments"         => ""
		);
		array_push( $filelist["folders"], $newFolderData );
		file_put_contents( $filename, json_encode($filelist) );

		$string = array_push( $this->sharedfunctions->getMinimalLog( $newFolderData["userID"] ), $newFolderData["id"], $newFolderData["folderName"], "Создан каталог: ".$newFolderData["folderName"]." (90 дней)" );
		$this->sharedfunctions->writeToLog( implode("\t", $string), $this->uploadLogFileName );
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}

	public function getFolderList( ) {
		$filename = $this->sharedfunctions->getContentFilePath( $this->input->post("userID") );
		$filelist = json_decode(file_get_contents($filename), true);
		$output   = array('<option value="0"> -- В корень -- </option>');
		if ( isset( $filelist["folders"] ) ) {
			foreach ( $filelist["folders"] as $folderData ) {
				if ( $folderData["id"] == $this->input->post("folderID") ) {
					continue;
				}
				array_push($output, '<option value="'.$folderData["id"].'">'.$folderData["folderName"].'</option>');
			}
		}
		header('Content-Type: text/html');
		print implode("\n", $output);
	}
	// Нужен ли режим копирования? здесь реализован перенос
	// ????????????????????????????????????

	private function copyFiles( $filelist, $fileData, $target ) {
		foreach ( $filelist["files"] as $key=>$itemData ) {
			if ( in_array( $itemData["id"], $fileData['files'] ) ) {
				$filelist["files"][$key]['parent'] = $target;
				$string = array_push( 
					$this->sharedfunctions->getMinimalLog( $fileData["userID"] ),
					$itemData["id"],
					$itemData["storageFilename"],
					"Файл перенесён в: ".$target
				);
				array_push( $this->logOutput, implode( "\t", $string ) );
			}
		}
		return $filelist;
	}

	private function copyFolders( $filelist, $fileData, $target ) {
		foreach ( $filelist["folders"] as $key=>$itemData ) {
			if ( in_array( $itemData["id"], $fileData["folders"] ) ) {
				$filelist["folders"][$key]['parent'] = $target;
				$string = array_push( 
					$this->sharedfunctions->getMinimalLog( $fileData["userID"] ),
					$itemData["id"],
					$itemData["folderName"],
					"Папка перенесена в: ".$target
				);
				array_push($this->logOutput, implode("\t", $string));
			}
		}
		return $filelist;
	}

	public function copyToFolder( ) {
		$this->logOutput = array();
		$fileData  = $this->input->post("items");
		$filename  = $this->sharedfunctions->getContentFilePath( $fileData["userID"] );
		$filelist  = json_decode( file_get_contents($filename), true );
		if ( isset( $fileData["files"] ) ) {
			$filelist = $this->copyFiles( $filelist, $fileData, $this->input->post("targetFolder") );
		}
		if ( isset( $fileData["folders"] ) ) {
			$filelist = $this->copyFolders( $filelist, $fileData, $this->input->post("targetFolder") );
		}
		file_put_contents( $filename, json_encode($filelist) );
		$this->sharedfunctions->writeToLog( implode("\r\n", $this->logOutput), $this->operationsLogFileName );
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}



	public function moveToUser( ) {
		$fileData        = $this->input->post("items");
		$targetUser      = $this->input->post("targetUserID");
		$filename        = $this->sharedfunctions->getContentFilePath( $fileData["userID"] );
		$targetfilename  = $this->sharedfunctions->getContentFilePath( $targetUser );

		if ( !file_exists($targetfilename) ) {
			$this->filesystem->writeEmptyFileList( $targetUser );
		}

		$fileLists = array(
			"source" => json_decode(file_get_contents($filename), true),
			"target" => json_decode(file_get_contents($targetfilename), true)
		);

		if ( isset($fileData["folders"]) ) {
			$folderList = $this->enlistFolders( $fileLists['source']['folders'], $fileData['folders'] );
			foreach ( $folderList as $folderData ) {
				if ( in_array( $folderData['id'], $fileData['folders'] ) ) {
					$folderData["parent"] = 0;
				}
				$fileLists = $this->moveFolder( $fileLists, $folderData, $targetUser, $this->input->post("leaveAcopy") );
			}
		}

		if ( isset( $fileData["files"] ) ) {
			$fileLists = $this->moveFiles( $fileLists, $fileData, $targetUser, $newParent, $this->input->post("leaveAcopy") );
		}

		if ( !$this->input->post("leaveAcopy") ) {
			file_put_contents( $filename, json_encode($fileLists["source"]) );
		}
		file_put_contents( $targetfilename, json_encode($fileLists["target"]) );
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}

	private function enlistFolders( $list, $foldersToMove ) {
		// Хитро-странное решение для перетаскивания значения.
		// Не хотелось делать передачу по ссылке. То есть не сумел.
		
		// Сначала перетащим корневые
		foreach ( $list as $folder ) {
			if ( in_array($folder["id"], $foldersToMove ) ) {
				array_push( $this->listD, $folder );
			}
		}
		//Потом все, что под ними. Как бы рекурсивно.
		foreach ( $foldersToMove as $folderToMove ) {
			$this->processChildren( $list, $folderToMove );
		}
		return $this->listD;
	}

	private function processChildren( $list, $folderToMove ) {
		foreach ( $list as $folder ) {
			if ( $folder["parent"] == $folderToMove ) {
				array_push( $this->listD, $folder );
				$this->processChildren( $list, $folder["id"] );
			}
		}
		return true;
	}

	private function moveFolder( $fileLists, $folderData, $targetUser, $leaveAcopy ) {
		$moveFileList   = $this->getFilesInFolder( $fileLists["source"]["files"], $folderData["id"] );

		/* операции по переносу */
		array_push($fileLists["target"]["folders"], $folderData);
		$fileLists      = $this->moveFiles( $fileLists, $moveFileList, $targetUser, $folderData["id"], $leaveAcopy );
		/* !операции по переносу */

		if ( !$leaveAcopy ) {
			unset( $fileLists["source"]["folders"][$key] );
		}
		$string = array_push( $this->sharedfunctions->getMinimalLog( $folderData["userID"] ),
			$folderData["id"],
			$folderData["folderName"],
			"Папка ".( ( $leaveAcopy ) ? "скопирована" : "перенесена")." к пользователю ".$targetUser
		);
		$this->sharedfunctions->writeToLog( implode("\t", $string), $this->operationsLogFileName );
		return $fileLists;
	}

	private function moveFiles( $fileLists, $fileData, $targetUser, $newParent = 0, $leaveAcopy = 1 ) {
		foreach ( $fileLists["source"]["files"] as $key=>$itemData ) {
			if ( in_array( $itemData["id"], $fileData['files'] ) ) {
				$oldFileID          = $itemData["id"];
				$oldStorageFilename = $itemData["storageFilename"];

				// замена целевого userID, целевого имени хранимого файла и его папки
				$itemData["id"]     = $this->sharedfunctions->getUUID();
				$itemData["userID"] = $targetUser;
				$itemData["parent"] = ($newParent) ? $newParent : $itemData["parent"];
				$itemData["storageFilename"] = $this->sharedfunctions->getUUID();
				// !!!!!!!!!!!!!!!!!!!возможно ошибка! если так, то перенести $source до замены полей
				$source             = $this->sharedfunctions->getUserDirPath( $itemData["userID"] ).$oldStorageFilename;
				$target             = $this->sharedfunctions->getUserDirPath( $targetUser).$itemData["storageFilename"];

				array_push( $fileLists["target"]["files"], $itemData );

				if ( !$leaveAcopy ) {
					unset( $fileLists["source"]["files"][$key] );
				}

				$this->filesystem->makeUserDir( $itemData["userID"] );

				$this->filesystem->moveFile( $source, $target, $leaveAcopy );

				$string = array(
					date("Y-m-d H:i:s"),
					$this->input->ip_address(),
					$itemData["userID"],
					$oldFileID,
					$oldStorageFilename,
					"Файл ".( ( $leaveAcopy ) ? "скопирован" : "перенесен" )." к пользователю ".$itemData["userID"]." в ".$itemData["storageFilename"]
				);
				$this->sharedfunctions->writeToLog( implode("\t", $string), $this->operationsLogFileName );
			}
		}
		return $fileLists;
	}

	private function getFilesInFolder( $files, $folderID ) {
		$output = array("files" => array(), "folders" => array() );
		foreach ( $files as $key=>$itemData ) {
			if ( $itemData["parent"] == $folderID ) {
				array_push( $output["files"], $itemData['id'] );
			}
		}
		return $output;
	}



	public function showRawData( $userID ) {
		$fileName = $this->sharedfunctions->getContentFilePath( $userID );
		if ( file_exists( $fileName ) ) {
			$file = file_get_contents( $fileName );
			//header('Content-Type: application/json');
			print $file;
			return true;
		}
		print "No such file";
	}

	public function saveHTML( ) {
		$htmlFileName  = $this->sharedfunctions->getUserDirPath( $this->input->post("userID") )."index.html";
		file_put_contents( $htmlFileName, $this->input->post("content") );
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}
	
	public function makeZip ( ) {
		$this->load->model("zipmodel");
		$fileData   = $this->input->post("items");
		$targetUser = $this->input->post("targetUserID");
		$filename   = $this->sharedfunctions->getContentFilePath( $fileData["userID"] );

		$fileList   = json_decode(file_get_contents($filename), true);
		$fileList   = $this->zipmodel->moveFilesForZip( $fileList, $fileData, $this->input->post("leaveAcopy") );

		file_put_contents( $filename, json_encode($fileList) );
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}
}
