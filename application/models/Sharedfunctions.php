<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sharedfunctions extends CI_Model {
	
	function __construct(){
		parent::__construct();
	}
	
	public function getContentFilePath( $userID ) {
		return $this->config->item("storageLocation").$userID.DIRECTORY_SEPARATOR."contents.json";
	}

	public function getUserDirPath( $userID ) {
		return $this->config->item("storageLocation").$userID.DIRECTORY_SEPARATOR;
	}

	public function getUUID( ) {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0xffff)
		);
	}

	public function writeToLog( $string, $logFile ) {
		file_put_contents($logFile, $string."\r\n", FILE_APPEND);
	}

	public function getMinimalLog( $userID ) {
		return array(
			date("Y-m-d H:i:s"),
			$this->input->ip_address(),
			$userID
		);
	}
}