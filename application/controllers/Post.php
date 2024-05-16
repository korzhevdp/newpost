<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Post extends CI_Controller {

	public function __construct() {
		parent::__construct();
		if ( !opcache_is_script_cached("index.php") ) {
			opcache_compile_file("index.php");
		}
		//$this->output->enable_profiler(true);
	}

	private function getContentFilePath( $userID ) {
		return $this->config->item("storageLocation").$userID.DIRECTORY_SEPARATOR."contents.json";
	}

	private function getUserDirPath( $userID ) {
		return $this->config->item("storageLocation").$userID.DIRECTORY_SEPARATOR;
	}

	public function index($userID = 0) {
		$this->showStorage($userID);
	}

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

	public function showStorage($userID = 0) {
		//$this->benchmark->mark('code_start');
		$userIP       = $this->input->ip_address();
		$isSuperAdmin = in_array($userIP, $this->superAdminEnable);
		$isITAdmin    = in_array($userIP, $this->itModeEnable);
		//print date("G");
		//exit;
		$data = array(
			"users"      => "",
			"mode"       => "production",
			"ipAddress"  => $userIP,
			"superAdmin" => $isSuperAdmin,
			"itAdmin"    => $isITAdmin,
			"SAKnob"     => "",
				/*( $isSuperAdmin && strpos($_SERVER['SERVER_NAME'], "post-dev.arhcity.ru") ) 
				? '<a href="http://post.arhcity.ru"     class="let dev" title="Переключить в нормальный режим.">to PROD</a>'
				: '<a href="http://post-dev.arhcity.ru" class="let dev" title="Переключить в режим разработчика.">to DEV</a>',*/
			"letters"    => $this->getLettersStrip(),
			"special"    => $this->getSpecialResources(),
			"autodelete" => ( (int) date("G") < 9 )
				? $this->load->view("autodelete", array(), true)
				: "",
			"proxyerror" => ( preg_match( "/192\.168\./", $userIP ) ) 
				? ""
				: $this->load->view("proxyerror", array("ipAddress" => $userIP), true),
			"startURL"   => ($this->input->post("u"))
					? "show_help();"
					: "showUserFiles('А', 0);"
		);
		//$this->benchmark->mark('code_end');
		$this->load->view('welcome_message', $data);
	}

	public function getUserList( $filter = "", $superAdmin = false ) {
		$filter = ( strlen($filter) ) ? $filter : $this->input->post("userName");
		$output    = array();
		$file = json_decode(file_get_contents("userdata/userlist.json"), true);
		foreach ( $file as $userID=>$userData ) {
			if (!isset($userData["name"])) {
				continue;
			}
			if ( preg_match('/^'.$filter.'/iu', $userData["name"] ) ) {
				$string = '<div class="fio" ref="'.$userID.'"><img src="/images/ico/star_grey.png" ref="'.$userID.'" class="favStar">'.$userData["name"].'</div>';
				array_push($output, $string);
			}
		}
		print implode("\n", $output);
		return true;
	}

	private function getLettersStrip( ) {
		$output = array('<a href="#" class="let" title="">&equiv;</a>');
		$lettersSrc = json_decode(file_get_contents("userdata/letters.json"), true);
		foreach ($lettersSrc as $letter) {
			array_push($output, '<a href="#'.$letter.'" class="let" title="'.$letter.'">'.$letter.'</a>');
		}
		return implode("\n", $output);
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

	public function phpinfo( ) {
		phpinfo();
	}

	public function dialog( $dialog ) {
		$this->load->view("dialogs/".$dialog);
	}
	
	public function htmleditor( $userID ) {
		$htmlFileName = $this->getUserDirPath( $userID )."index.html";
		$htmlText     = "";
		if ( file_exists( $htmlFileName ) ){
			$htmlText = file_get_contents( $htmlFileName );
		}
		$this->load->view( "dialogs/editor", array( "html" => $htmlText ) );
	}
}
