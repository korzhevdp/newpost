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

	public function index() {}
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

	private function getContentFilePath( $userID ) {
		return $this->config->item("storageLocation").$userID.DIRECTORY_SEPARATOR."contents.json";
	}

	private function getUserDirPath( $userID ) {
		return $this->config->item("storageLocation").$userID.DIRECTORY_SEPARATOR;
	}

	private function writeToLog( $string, $logFile ) {
		file_put_contents($logFile, $string."\r\n", FILE_APPEND);
	}

	public function download( $userID, $fileID ) {
		$JSONFileName = $this->getContentFilePath( $userID );
		$JSONfile     = json_decode( file_get_contents($JSONFileName), true );
		foreach( $JSONfile['files'] as $key=>$fileData ) {
			if ( $fileData['id'] == $fileID ) {
				if ( $fileData['downloadLimit'] == 0 ){
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $userID, $fileID, $fileData["storageFilename"], $fileData["originalFilename"], "Достигнут лимит скачиваний файла" );
					$this->writeToLog( implode("\t", $string), $this->downloadLogFileName );
					return false;
				}
				$JSONfile['files'][$key]["downloadLimit"] = ($JSONfile['files'][$key]["downloadLimit"] <= -1)
					? -1
					: $JSONfile['files'][$key]["downloadLimit"] - 1;
				file_put_contents( $JSONFileName, json_encode($JSONfile) );

				$fileName = $this->config->item("storageLocation").$userID.DIRECTORY_SEPARATOR.$fileData["storageFilename"];
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename='.$fileData["originalFilename"]); 
				header('Content-Transfer-Encoding: binary');
				header('Connection: Keep-Alive');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: '.filesize($fileName));
				print file_get_contents($fileName);

				$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $userID, $fileID, $fileData["storageFilename"], $fileData["originalFilename"], "Файл скачан с сервера" );
				$this->writeToLog( implode("\t", $string), $this->downloadLogFileName );
				return true;
			}
		}
	}

	public function getFileList() {
		$state = array(
			"userID"         => $this->input->post("userID"),
			"parentFolderID" => 0,
			"folderID"       => $this->input->post("folderID"),
			"sortMode"       => $this->input->post("sorts")
		);

		$filename  = $this->getContentFilePath( $state["userID"] );
		$HTMLFile  = $this->getUserDirPath( $state["userID"] )."index.html" ;
		$albumFile = $this->getUserDirPath( $state["userID"] )."photoalbum-".$state["folderID"].".html";
		$this->syncUserData( $state["userID"] );

		if ( !file_exists($filename) ) {
			$this->writeEmptyFileList( $state["userID"] );
		}

		$fileOutput   = array();
		$folderOutput = array();
		$filelist     = json_decode(file_get_contents($filename), true);
		$hasHTML      = file_exists($HTMLFile);
		$html         = ($hasHTML)
			? file_get_contents($HTMLFile)
			: $this->load->view("filestoragetableempty", array(), true);
		
		// заполняем $this->listC
		$this->getFolderPath($filelist["folders"], $state["folderID"]);

		$data = array(
			/*  порядок вызова files-folders важен  :) пока */
			"files"        => $this->collectFilesList( $filelist["files"], $state ),
			"folders"      => $this->collectFoldersList( $filelist["folders"], $state ),
			"folderData"   => array_reverse($this->listC),
			"userdata"     => $this->getUserData( $state["userID"] ),
			"treePosition" => $this->getCurrentPosition( $filelist["folders"], $state ),
			"hasHTML"      => file_exists( $HTMLFile  ),
			"hasAlbum"     => file_exists( $albumFile ),
			"html"         => $html
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

	private function getFileSize( $userID, $fileID ) {
		$filename = $this->getContentFilePath( $userID );
		$filelist = json_decode(file_get_contents($filename), true);
		foreach ($filelist['files'] as $key=>$item) {
			if ( $fileID == $item['id'] ) {
				$fileSName = $this->getUserDirPath( $userID ).$item["storageFilename"];
				$filelist['files'][$key]["fileSize"] = ( file_exists($fileSName) ) ? filesize($fileSName) : 0;
				file_put_contents($filename, json_encode($filelist));
				return $filelist['files'][$key]["fileSize"];
			}
		}
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
				: $this->getFileSize( $state["userID"], $storageItem["id"] );
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
		//if ( (int) $state["folderID"] > 0 ) {
			//array_push( $output, $this->load->view("upfolderitem", array("id" => 0, "comments" => ""), true) );
		//}
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

	private function syncUserData( $userID = 0 ) {
		$filename = "userdata/userlist.json";
		$userData = json_decode(file_get_contents($filename), true);
		if ( !isset( $userData[$userID]["name"] ) ) {
			return false;
		}
		$request = array(
			'fio' => $userData[$userID]["name"]
		);
		$options = array(
			'http' => array(
				'content'	=> http_build_query($request),
				'header'	=> 'Content-type: application/x-www-form-urlencoded',
				'method'	=> 'POST'
			)
		);
		$context  = stream_context_create($options);
		$userInfo = json_decode(file_get_contents("http://192.168.1.35/opendata/getUserInfo", false, $context), true);
		$userData[$userID]["phone"]      = $userInfo["phone"];
		$userData[$userID]["userID"]     = $userInfo["id"];
		$userData[$userID]["department"] = $userInfo["dn"];
		file_put_contents( $filename, json_encode($userData) );
	}

	public function showIndex( $userID = 0 ) {
		$filename = $this->getUserDirPath( $userID )."index.html";
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
		$fileName = $this->getContentFilePath( $userID );
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
			$this->getUserDirPath( $userID )."photoalbum-".$folderID.".html",
			implode( "\n", $output )
		);

		print json_encode( $output );
	}

	public function albumItem( $userID, $imageID ) {
		print file_get_contents( $this->getUserDirPath( $userID ).$imageID );
	}

	public function getFileInfo( $userID = 0, $itemID = 0 ) {
		$userID   = ($userID) ? $userID : $this->input->post("userID");
		$itemID   = ($itemID) ? $itemID : $this->input->post("itemID");
		$fileName = $this->getContentFilePath( $userID );
		$data     = json_decode(file_get_contents($fileName), true);
		foreach ( $data["files"] as $item ) {
			if ( $item["id"] == $itemID ) {
				$item["creationDate"] = date( "d.m.Y H:i:s", $item["creationDate"] );
				$item["fileSize"]     = (!isset($item["fileSize"]) || $item["fileSize"] == 0)
					? filesize( $this->getUserDirPath( $userID ).$item["storageFilename"] )
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
		$fileName = $this->getContentFilePath( $userID );
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
		$filename = $this->getContentFilePath( $userID );
		$fileData = json_decode( file_get_contents($filename), true );
		if ( $this->input->post("itemType") == "folderInfo" ) {
			foreach ( $fileData['folders'] as $key => $item ) {
				if ( $itemID == $item["id"] ) {
					$fileData['folders'][$key]["comments"] = $this->input->post("comment");
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $item["userID"], $item["id"], $item["name"], "Добавлен комментарий к директории" );
					$this->writeToLog( implode("\t", $string), $this->operationsLogFileName );
				}
			}
		}
		if ( $this->input->post("itemType") == "fileInfo" ) {
			foreach ( $fileData['files'] as $key => $item ) {
				if ( $itemID == $item["id"] ) {
					$fileData['files'][$key]["comments"]     = $this->input->post("comment");
					$fileData['files'][$key]["deletionDate"] = date_format(date_create_from_format("Y-m-d", $this->input->post("deletionDate")), "U");
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $item["userID"], $item["id"], $item["name"], "Добавлен комментарий, установлена дата удаления: ".$this->input->post("deletionDate"));
					$this->writeToLog( implode("\t", $string), $this->operationsLogFileName );
				}
			}
		}
		file_put_contents($filename, json_encode($fileData));
	}

	public function saveItemName( ) {
		//$this->output->enable_profiler(true);
		$filename = $this->getContentFilePath( $this->input->post("userID") );
		$fileData = json_decode(file_get_contents($filename), true);
		if ( $this->input->post("itemType") == "file" ) {
			foreach ( $fileData["files"] as $key=>$item ) {
				if ( $item["id"] == $this->input->post("itemID") ) {
					$fileData["files"][$key]["originalFilename"] = preg_replace("/\[\/\\:\*\?\"<>\|\]/", "_", $this->input->post("newName"));
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $item["userID"], $item["id"], $item["originalFilename"], "Установлено новое публичное имя файла: ".$this->input->post("newName"));
					$this->writeToLog( implode("\t", $string), $this->operationsLogFileName );
					break;
				}
			}
		}
		if ( $this->input->post("itemType") == "folder" ) {
			foreach ( $fileData["folders"] as $key=>$item ) {
				if ( $item["id"] == $this->input->post("itemID") ) {
					$fileData["folders"][$key]["folderName"] = preg_replace("/\[\/\\:\*\?\"<>\|\]/", "_", $this->input->post("newName"));
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $item["userID"], $item["id"], $item["FolderName"], "Установлено новое каталога: ".$this->input->post("newName"));
					$this->writeToLog( implode("\t", $string), $this->operationsLogFileName );
					break;
				}
			}
		}
		file_put_contents($filename, json_encode($fileData));
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}

	public function deleteItems( ) {
		$filename = $this->getContentFilePath( $this->input->post("userID") );
		$fileData = json_decode(file_get_contents($filename), true);
		$itemsID  = $this->input->post("itemsID");
		if ( isset( $itemsID["files"] ) ) {
			foreach ( $fileData["files"] as $key=>$item ) {
				if ( in_array( $item["id"], $itemsID["files"] ) ) {
					$unlinkFile = $this->getUserDirPath( $item["userID"] ).$item["storageFilename"];
					if ( $this->filesystem->deleteFile( $unlinkFile ) ){
						unset( $fileData["files"][$key] );
					}
					$string     = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $item["userID"], $item["id"], $item["storageFilename"], "Удалён файл: ".$item["originalFilename"] );
					$this->writeToLog( implode("\t", $string), $this->operationsLogFileName );
				}
			}
		}
		if ( isset( $itemsID["folders"] ) ) {
			foreach ( $fileData["folders"] as $key=>$item ) {
				if ( in_array( $item["id"], $itemsID["folders"] ) ) {
					unset( $fileData["folders"][$key] );
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $item["userID"], $item["id"], $item["FolderName"], "Удалён каталог: ".$item["FolderName"] );
					$this->writeToLog( implode("\t", $string), $this->operationsLogFileName );
				}
			}
		}
		file_put_contents($filename, json_encode($fileData));
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}

	public function getFreeSpace( ) {
		print floor( disk_free_space("D:") / ( 1024 * 1024 ) );
	}



	public function uploadFiles( ) {
		$userID   = $this->input->post("userID");
		$filename = $this->getContentFilePath( $userID );
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
		move_uploaded_file( $_FILES["files"]["tmp_name"], $this->getUserDirPath( $newFileData["userID"] ).$newFileData["storageFilename"] );
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
		$this->writeToLog( implode("\t", $string), $this->uploadLogFileName );
	}

	public function prolongateFiles( ) {
		$logOutput = array();
		$fileData  = $this->input->post("items");
		$filename  = $this->getContentFilePath( $fileData["userID"] );
		$filelist  = json_decode(file_get_contents($filename), true);
		if ( isset( $fileData["files"] ) ) {
			foreach ( $filelist["files"] as $key=>$itemData ) {
				if ( in_array( $itemData["id"], $fileData['files'] ) ) {
					$filelist["files"][$key]['deletionDate'] = ($this->input->post("period") == "eternal")
						? $this->input->post("period")
						: date("U") + ( $this->input->post("period") * 60 * 60 * 24 );
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $fileData["userID"], $itemData["id"], $itemData["storageFilename"], "Продлён срок жизни файла: ".$itemData["originalFilename"]." на ". $this->input->post("period")." дней" );
					array_push( $logOutput, implode("\t", $string) );
				}
			}
		}
		if ( isset($fileData["folders"]) ) {
			foreach ( $filelist["folders"] as $key=>$itemData ) {
				if ( in_array( $itemData["id"], $fileData["folders"] ) ) {
					$filelist["folders"][$key]['deletionDate'] = ($this->input->post("period") == "eternal")
						? $this->input->post("period")
						: date("U") + ( $this->input->post("period") * 60 * 60 * 24 );
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $fileData["userID"], $itemData["id"], $itemData["folderName"], "Продлён срок жизни каталога: ".$itemData["folderName"]." на ". $this->input->post("period")." дней" );
					array_push($logOutput, implode("\t", $string));
				}
			}
		}
		file_put_contents( $filename, json_encode($filelist) );
		$this->writeToLog( implode("\r\n", $logOutput), $this->operationsLogFileName );
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}

	public function createFolder( ) {
		$userID        = $this->input->post("userID");
		$filename      = $this->getContentFilePath( $userID );
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

		$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $newFolderData["userID"], $newFolderData["id"], $newFolderData["folderName"], "Создан каталог: ".$newFolderData["folderName"]." (90 дней)" );
		$this->writeToLog( implode("\t", $string), $this->uploadLogFileName );
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}

	public function getFolderList( ) {
		$filename = $this->getContentFilePath( $this->input->post("userID") );
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
	public function copyToFolder( ) {
		$logOutput = array();
		$fileData  = $this->input->post("items");
		$filename  = $this->getContentFilePath( $fileData["userID"] );
		$filelist  = json_decode( file_get_contents($filename), true );
		if ( isset( $fileData["files"] ) ) {
			foreach ( $filelist["files"] as $key=>$itemData ) {
				if ( in_array( $itemData["id"], $fileData['files'] ) ) {
					$filelist["files"][$key]['parent'] = $this->input->post("targetFolder");
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $fileData["userID"], $itemData["id"], $itemData["storageFilename"], "Файл перенесён в: ".$this->input->post("targetFolder") );
					array_push($logOutput, implode("\t", $string));
				}
			}
		}
		if ( isset( $fileData["folders"] ) ) {
			foreach ( $filelist["folders"] as $key=>$itemData ) {
				if ( in_array( $itemData["id"], $fileData["folders"] ) ) {
					$filelist["folders"][$key]['parent'] = $this->input->post("targetFolder");
					$string = array( date("Y-m-d H:i:s"), $this->input->ip_address(), $fileData["userID"], $itemData["id"], $itemData["folderName"], "Папка перенесена в: ".$this->input->post("targetFolder") );
					array_push($logOutput, implode("\t", $string));
				}
			}
		}
		file_put_contents( $filename, json_encode($filelist) );
		$this->writeToLog( implode("\r\n", $logOutput), $this->operationsLogFileName );
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}

	private function writeEmptyFileList( $userID ) {
		$filename = $this->getContentFilePath( $userID );
		$this->filesystem->makeUserDir( $userID );
		file_put_contents( $filename, json_encode( array( "files" => array() , "folders"  => array() ) ) );
	}

	public function moveToUser( ) {
		$fileData        = $this->input->post("items");
		$targetUser      = $this->input->post("targetUserID");
		$filename        = $this->getContentFilePath( $fileData["userID"] );
		$targetfilename  = $this->getContentFilePath( $targetUser );

		if ( !file_exists($targetfilename) ) {
			$this->writeEmptyFileList( $targetUser );
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
		$string = array( 
			date("Y-m-d H:i:s"),
			$this->input->ip_address(),
			$folderData["userID"],
			$folderData["id"],
			$folderData["folderName"],
			"Папка ".( ( $leaveAcopy ) ? "скопирована" : "перенесена")." к пользователю ".$targetUser
		);
		$this->writeToLog( implode("\t", $string), $this->operationsLogFileName );
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
				$this->writeToLog( implode("\t", $string), $this->operationsLogFileName );
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
		$fileName = $this->getContentFilePath( $userID );
		if ( file_exists( $fileName ) ) {
			$file = file_get_contents( $fileName );
			//header('Content-Type: application/json');
			print $file;
			return true;
		}
		print "No such file";
	}

	public function saveHTML( ) {
		$htmlFileName  = $this->getUserDirPath( $this->input->post("userID") )."index.html";
		file_put_contents( $htmlFileName, $this->input->post("content") );
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}
	
	public function makeZip ( ) {
		$this->load->model("zipmodel");
		$fileData   = $this->input->post("items");
		$targetUser = $this->input->post("targetUserID");
		$filename   = $this->getContentFilePath( $fileData["userID"] );

		$fileList   = json_decode(file_get_contents($filename), true);
		$fileList   = $this->zipmodel->moveFilesForZip( $fileList, $fileData, $this->input->post("leaveAcopy") );

		file_put_contents( $filename, json_encode($fileList) );
		print json_encode( array( "error" => 0, "status" => "OK" ) );
	}
}
