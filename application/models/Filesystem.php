<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Filesystem extends CI_Model {
	
	function __construct(){
		parent::__construct();
	}
	
	private function copyFile( $source, $target ) {
		$command = 'copy /B /Y /V "'.$source.'" "'.$target.'"';
		shell_exec( $command );
		if ( file_exists($target) ) {
			return true;
		}
		//print $command." не скопирован\n";
		return false;
	}

	public function deleteFile( $source ) {
		$command = 'del /Q "'.$source.'"';
		shell_exec( $command );
		if ( file_exists($source) ) {
			//print $command." не удалён\n";
			return false;
		}
		return true;
	}

	// перенос файла -- композиция фунций копирования и удаления
	public function moveFile( $source, $target, $leaveAcopy ) {
		$this->copyFile( $source, $target );
		if ( $leaveAcopy ) {
			return true;
		}
		$this->deleteFile( $source );
	}

	public function makeUserDir( $userID ) {
		$userDir = $this->getUserDirPath( $userID );
		if ( !file_exists( $userDir ) ) {
			mkdir( $userDir );
		}
	}

	public function executeZipCommands( $diskPath, $tempDiskPath, $storageFilename ) {
		$command = "7z a -r -aoa -tzip -bd -mmt1 -mx1 -o".$diskPath." ".$diskPath.$storageFilename." ".$tempDiskPath."*.*";
		shell_exec($command);

		$command = "del /S /Q ".$tempDiskPath."*.*";
		shell_exec($command);

		$command = "ren \"".$diskPath.$storageFilename.".zip\" \"".$storageFilename."\"";
		shell_exec($command);

		//ren "D:\ForFiles\storage\1611\ab15c549-f102-42d6-9d8d-9248c00e3fd0.zip" "D:\ForFiles\storage\1611\ab15c549-f102-42d6-9d8d-9248c00e3fd0"
		//print $command."\n\n";
	}

	public function getFreeSpace( ) {
		print floor( disk_free_space("D:") / ( 1024 * 1024 ) );
	}

	public function getFileSize( $userID, $fileID ) {
		$filename = $this->sharedfunctions->getContentFilePath( $userID );
		$filelist = json_decode(file_get_contents($filename), true);
		foreach ( $filelist['files'] as $key=>$item ) {
			if ( $fileID == $item['id'] ) {
				$fileSName = $this->sharedfunctions->getUserDirPath( $userID ).$item["storageFilename"];
				$filelist['files'][$key]["fileSize"] = ( file_exists($fileSName) ) ? filesize($fileSName) : 0;
				file_put_contents($filename, json_encode($filelist));
				return $filelist['files'][$key]["fileSize"];
			}
		}
	}

	public function writeEmptyFileList( $userID ) {
		$filename = $this->sharedfunctions->getContentFilePath( $userID );
		$this->filesystem->makeUserDir( $userID );
		file_put_contents( $filename, json_encode( array( "files" => array() , "folders"  => array() ) ) );
	}

}