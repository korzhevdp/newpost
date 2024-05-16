<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Logs extends CI_Controller {

	public function __construct() {
		parent::__construct();
		//if ( !opcache_is_script_cached("index.php") ) {
			//opcache_compile_file("index.php");
		//}
		//$this->output->enable_profiler(true);
	}

	private $operationsLogFileName = "logs/operations.log";
	private $downloadLogFileName   = "logs/download.log";
	private $uploadLogFileName     = "logs/upload.log";

	public function operations( ) {
		if ( file_exists($this->operationsLogFileName) ) {
			print nl2br(file_get_contents($this->operationsLogFileName));
		}
	}
	public function uploaded( ) {
		if ( file_exists($this->uploadLogFileName) ) {
			print nl2br(file_get_contents($this->uploadLogFileName));
		}
	
	}
	public function download( ) {
		if ( file_exists($this->downloadLogFileName) ) {
			print nl2br(file_get_contents($this->downloadLogFileName));
		}
	
	}
	public function autodelete( ) {
		if ( file_exists($this->autodeleteLogFileName) ) {
			print nl2br(file_get_contents($this->autodeleteLogFileName));
		}
	
	}

}