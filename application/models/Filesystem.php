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

}