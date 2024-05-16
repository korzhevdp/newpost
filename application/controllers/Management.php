<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Management extends CI_Controller {

	public function __construct() {
		parent::__construct();
		if ( !in_array($this->input->ip_address(), $this->superAdminEnable) ) {
			$this->load->helper("url");
			redirect("/");
		}
	}

	private $userListFileName = "userdata/userlist.json";
	private $statFileName     = "D:\ForFiles\storage\stat.json";

	private $fileSorts = array (
		'docs'  => array( "docx", "xlsx", "pptx", "rtf","doc", "xls", "pdf" ),
		'imgs'  => array( "jpeg", "jpg", "gif", "png", "bmp"),
		'video' => array( "avi", "mkv", "mp4", "mpg", "flv"),
		'texts' => array( "txt", "html", "ini", "cfg", "conf", "log", "xml", "xsl"),
		'progs' => array( "exe", "msi", "dll", "cpl", "msc", "acm", "ocx"),
		'archs' => array( "rar", "zip", "7z", "gz", "bz2", "arj"),
		'scrpt' => array( "js", "vbs", "php", "pl", "py", "asp"),
		'books' => array( "djvu", "chm")
	);

	private $defaultStat = array(
		"filesCount"        => 0,
		"usersStoringFiles" => 0,
		"diskFreeSpace"     => 0,
		"fileVolume"        => 0,
		"bigFilesCount"     => 0,
		"eternalFilesCount" => 0,
		"docsCount"         => 0,
		"imgsCount"         => 0,
		"videoCount"        => 0,
		"textsCount"        => 0,
		"progsCount"        => 0,
		"archsCount"        => 0,
		"scrptCount"        => 0,
		"booksCount"        => 0,
		"otherCount"        => 0,
		"sorts"             => array (
			"docs"          => array( "docx", "xlsx", "pptx", "rtf","doc", "xls", "pdf" ),
			"imgs"          => array( "jpeg", "jpg", "gif", "png", "bmp"),
			"video"         => array( "avi", "mkv", "mp4", "mpg", "flv"),
			"texts"         => array( "txt", "html", "ini", "cfg", "conf", "log", "xml", "xsl"),
			"progs"         => array( "exe", "msi", "dll", "cpl", "msc", "acm", "ocx"),
			"archs"         => array( "rar", "zip", "7z", "gz", "bz2", "arj"),
			"scrpt"         => array( "js", "vbs", "php", "pl", "py", "asp"),
			"books"         => array( "djvu", "chm")
		)
	);

	private $itModeEnable = array(
		'127.0.0.1',    # localhost
		#'192.168.1.2', # CFS2
		'192.168.1.84', # gribanovdg
		'192.168.1.44', # korzhevdp
		'192.168.1.45', # usupov
		'192.168.1.46', # saharov
		'192.168.51.42' # ekimovks
	);
	
	private $superAdminEnable = array(
		'192.168.51.42',   # ekimovks
		'192.168.1.44',    # korzhevdp
		'192.168.1.84',    # gribanovdg
		#'192.168.51.151', # shablykimmu
		#'192.168.1.46',   # saharov
		#'192.168.1.45',   # usupov
	);

	private function getContentFilePath( $userID ) {
		return $this->config->item("storageLocation").$userID.DIRECTORY_SEPARATOR."contents.json";
	}

	private function getUserDirPath( $userID ) {
		return $this->config->item("storageLocation").$userID.DIRECTORY_SEPARATOR;
	}

	public function collectStatistics( ) {
		$data = $this->getFilesCount();
		$stat = ( file_exists($this->statFileName) ) 
			? file_get_contents($this->statFileName)
			: "{}" ;
		$stat = json_decode( $stat, true );
		$stat[date("Y-m-d")] = $data;
		file_put_contents($this->statFileName, json_encode($stat));
		$this->load->helper("url");
		redirect("management");
	}

	private function getCurrentStat( ) {
		$stat = $this->defaultStat;
		if ( file_exists($this->statFileName) ) {
			$stat = json_decode( file_get_contents($this->statFileName), true );
			return $stat[date("Y-m-d")];
		}
		return $stat;
	}
	
	private function getSpecialResources( ) {
		$output = array();
		$list   = file("userdata/spec_list.txt");
		foreach ($list as $line) {
			$data = explode(":", $line);
			array_push($output, '<div class="fio specialResource" id="fio'.$data[1].'" currentUserID="'.$data[0].'" folder="0"><a href="#">'.$data[1].'</a></div>');
		}
		return implode("\n", $output);
	}

	public function index() {
		$userIP          = $this->input->ip_address();
		$isITAdmin       = ( in_array( $userIP, $this->itModeEnable ) );
		$isSuperAdmin    = ( in_array( $userIP, $this->itModeEnable ) );
		$pageData        = array(
			"userCount"      => $this->getUserCount(),
			"fileStatistics" => $this->getCurrentStat( )
		);
		$data            = array(
			"content"    => ( $isITAdmin )
				? $this->load->view("itpages/itfunctions", $pageData, true)
				: "Не, не прокатит, вы не трушный ойтишнег...",
			"ipAddress"  => $userIP,
			"itAdmin"    => $isITAdmin,
			"superAdmin" => $isSuperAdmin,
			"SAKnob"     => '<a href="http://newpost.arhcity.ru" class="let dev" title="Переключить в нормальный режим.">В хранилище ФОР</a>',
			"special"    => $this->getSpecialResources()
		);
		$this->load->view("itpages/container", $data);
	}

	private function getUserCount( ) {
		$file = json_decode(file_get_contents($this->userListFileName), true);
		return sizeof($file);
	}

	private function getFilesCount( ) {
		$statistics = $this->defaultStat;
		$file = json_decode(file_get_contents($this->userListFileName), true);
		foreach ( $file as $userID => $userData ) {
			$filePath = $this->getContentFilePath( $userID );
			if ( !file_exists( $filePath ) ) {
				continue;
			}
			$userDataFile = json_decode(file_get_contents($filePath), true);
			$statistics["filesCount"] += sizeof($userDataFile["files"]);
			if ( sizeof($userDataFile["files"]) ) {
				$statistics["usersStoringFiles"] += 1;
			}
			foreach ( $userDataFile["files"] as $fileData ) {
				$statistics["fileVolume"] += $fileData["fileSize"];
				if ( $fileData["fileSize"] > ( 100 * 1024 * 1024 ) ) {
					$statistics["bigFilesCount"] += 1;
				}
				if ( $fileData["deletionDate"] == "eternal" ) {
					$statistics["eternalFilesCount"] += 1;
				}
				$ext = explode(".", $fileData["originalFilename"]);
				$ext = end($ext);
				$found = false;
				foreach ( $this->fileSorts as $sort=>$exts ) {
					if ( in_array( $ext, $exts ) ) {
						$statistics[$sort."Count"] += 1;
						$found = true;
					}
				}
				if ( !$found ) {
					$statistics["otherCount"] += 1;
				}
			}
		}
		$statistics["fileVolume"] = floor( $statistics["fileVolume"] / ( 1024 * 1024 ) );
		return $statistics;
	}

	public function deleteUser( $userID = 0 ) {

		$file = json_decode(file_get_contents($this->userListFileName), true);
		unset($file[$userID]);
		file_put_contents($this->userListFileName, json_encode($file));
	}

	public function graph( $parameter ) {
		print "";
	}
}
