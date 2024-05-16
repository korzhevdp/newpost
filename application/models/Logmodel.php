<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Logmodel extends CI_Model {
	
	function __construct(){
		parent::__construct();
	}

	public function getDeleteFileLogString( $item ) {
		$string = $this->sharedfunctions->getMinimalLog( $item["userID"] );
		array_push( $string, $item["id"], $item["storageFilename"], "Удалён файл: ".$item["originalFilename"] );
		return implode( "\t", $string );
	}

	public function getDeleteFolderLogString( $item ) {
		$string = $this->sharedfunctions->getMinimalLog( $item["userID"] );
		$string = array_push( $string, $item["id"], $item["FolderName"], "Удалён каталог: ".$item["FolderName"] );
		return implode( "\t", $string );
	}
}