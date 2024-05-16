<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Zipmodel extends CI_Model {
	
	function __construct(){
		parent::__construct();
	}

	private $listZ = array(); // буфер для путей папок ZIP
	private $listY = array(); // буфер для путей папок ZIP

	private function processZipRootFolder( $folders, $folderID ) {
		foreach ( $folders as $folder ) {
			if ( $folder["id"] == $folderID ) {
				return $folder;
			}
		}
		return true;
	}

	// построение дерева от корня к ветвям
	private function processZipSubfolders( $folders, $parentID ) {
		foreach ( $folders as $folder ) {
			if ( $folder["parent"] == $parentID ) {
				array_push( $this->listZ, $folder );
				$this->processZipSubfolders( $folders, $folder["id"] );
			}
		}
		return true;
	}

	//построение дерева от ветви к корню
	private function backTrackZipPath( $folders, $parentID ) {
		foreach ( $folders as $folder ) {
			if ( $folder["id"] == $parentID ) {
				array_push( $this->listY, $folder );
				$this->backTrackZipPath( $folders, $folder["parent"] );
			}
		}
		return true;
	}

	private function getZipPath( $folders, $fileParentID, $topmostFolder, $mode="folder" ) {
		$output      = array();
		$this->listY = array();
		foreach ( $folders as $folder ) {
			if ( $folder["id"] == $fileParentID ) {
				array_push( $this->listY, $folder );
				$this->backTrackZipPath( $folders, $folder["parent"] );
			}
		}
		foreach ( $this->listY as $listItem ) {
			if ( $mode == "folder" ) {
				array_push($output, $listItem["folderName"]);
				if ( in_array( $listItem['id'], $topmostFolder ) ) {
					break;
				}
				continue;
			}
			if ( in_array( $listItem['id'], $topmostFolder ) ) {
				break;
			}
			array_push($output, $listItem["folderName"]);
		}
		return array_reverse($output);
	}

	private function prepareZipFolders( $path, $tempDiskPath ) {
		foreach ( $path as $folder ) {
			$tempDiskPath .= DIRECTORY_SEPARATOR.$folder;
			if ( !file_exists( $tempDiskPath ) ){
				$command   = 'mkdir "'.$tempDiskPath.'"';
				shell_exec( $command );
			}
		}
	}

	private function processMoveFilesToZip ( $fileList, $fileData, $leaveAcopy, $tempDiskPath, $diskPath ) {
		foreach ( $fileList["files"] as $key=>$fileItemData ) {
			if ( in_array($fileItemData["id"], $fileData['files'] ) ) {
				$path   = $this->getZipPath( $fileList["folders"], $fileItemData["parent"], array( $fileItemData["parent"], "file" ) );
				$this->prepareZipFolders($path, $tempDiskPath);
				$source = $diskPath.$fileItemData["storageFilename"];
				$target = $tempDiskPath.implode( DIRECTORY_SEPARATOR, $path ).DIRECTORY_SEPARATOR.$fileItemData["originalFilename"];
				$this->filesystem->moveFile( $source, $target, $leaveAcopy );
				if ( !$leaveAcopy ) {
					unset( $fileList["files"][$key] );
				}
			}
		}
		return $fileList;
	}

	private function processMoveFoldersToZip( $fileList, $zipFolders, $leaveAcopy, $tempDiskPath, $diskPath ) {
		foreach ( $zipFolders as $folderID ) {
			foreach ( $fileList["files"] as $key=>$fileItemData ) {
				if ( $fileItemData["parent"] == $folderID ) {
					$path   = $this->getZipPath( $fileList["folders"], $folderID, $fileData['folders'], "folder" );
					$this->prepareZipFolders($path, $tempDiskPath);
					$source = $diskPath.$fileItemData["storageFilename"];
					$target = $tempDiskPath.implode( DIRECTORY_SEPARATOR, $path).DIRECTORY_SEPARATOR.$fileItemData["originalFilename"];
					$this->filesystem->moveFile( $source, $target, $leaveAcopy );
					if ( !$leaveAcopy ) {
						unset( $fileList["files"][$key] );
					}
				}
			}
		}
		return $fileList;
	}

	private function collectZipFolders( $fileList, $fileData ) {
		$this->listZ  = array();
		$zipFolders   = array();
		foreach ( $fileList['folders'] as $key=>$itemData ) {
			if ( in_array( $itemData["id"], $fileData['folders'] ) ) {
				array_push( $this->listZ, $this->processZipRootFolder( $fileList['folders'], $itemData["id"] ) );
				$this->processZipSubfolders( $fileList['folders'], $itemData["id"] );
			}
		}
		foreach ( $this->listZ as $zipFolderData ) {
			array_push( $zipFolders, $zipFolderData['id'] );
		}
		return $zipFolders;
	}

	public function moveFilesForZip( $fileList, $fileData, $leaveAcopy = 1 ) {

		$tempDiskPath   = $this->sharedfunctions->getUserDirPath( $fileData["userID"] )."temp".DIRECTORY_SEPARATOR;
		$diskPath       = $this->sharedfunctions->getUserDirPath( $fileData["userID"] ).DIRECTORY_SEPARATOR;

		if ( isset( $fileData['files'] ) ) {
			$fileList   = $this->processMoveFilesToZip ( $fileList, $fileData, $leaveAcopy, $tempDiskPath, $diskPath );
		}

		if ( isset( $fileData['folders'] ) ) {
			// составляем список каталогов, чьи файлы надо перенести в zip
			$zipFolders = $this->collectZipFolders( $fileList, $fileData );
			// двигаем директории со всеми вложенностями
			$fileList   = processMoveFoldersToZip( $fileList, $zipFolders, $leaveAcopy, $tempDiskPath, $diskPath );
		}

		$storageFilename = $this->sharedfunctions->getUUID();
		$this->filesystem->executeZipCommands( $diskPath, $tempDiskPath, $storageFilename );

		$zipFileData = array(
			"id"               => $this->sharedfunctions->getUUID(),
			"userID"           => $fileData["userID"],
			"parent"           => 0,
			"storageFilename"  => $storageFilename,
			"originalFilename" => $this->input->post("zipName"),
			"creationDate"     => date("U"),
			"deletionDate"     => date("U") + (60 * 60 * 24 * 30),
			"ownerIP"          => $this->input->ip_address(),
			"ownerHost"        => "",
			"downloadLimit"    => "-1",
			"fileSize"         => 0,
			"tags"             => "",
			"comments"         => ""
		);
		array_push( $fileList["files"], $zipFileData );

		return $fileList;
	}

}