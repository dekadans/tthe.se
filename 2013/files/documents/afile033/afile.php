<?php
/*
aFile Beta 3 (0.3.2)

Copyright 2010, 2011, Tomas Thelander
Licensed under the GNU General Public License

http://www.tthe.se

	This file is part os aFile.

    aFile is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    aFile is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with aFile.  If not, see <http://www.gnu.org/licenses/>.
*/

header("Cache-Control: no-cache, must-revalidate");

error_reporting(0);

require_once 'tfile31.php';

$conf = new aFile_Config();

/* ****
 * Configuration goes here
 * **** */
 
/*
	For info about how to configure aFile, see the Useage Guide.
*/

/*  Roots  */
$conf->AddRoot('./storage/');
$conf->AddRoot('./storage2/');


/*  User Management  */
$conf->mode = aFile_Config::MODE_AUTH;

$conf->AddUser('demo', '9d4e1e23bd5b727046a9e3b4b7db57bd8d6ee684', array(
		'Upload' => false,
		'Delete' => false,
		'Rename' => false,
		'Move' => false,
		'New_File' => false,
		'New_Directory' => false,
		'Extract_Zip' => false,
		'Zip_Directory' => false
	), '/');


/*  URL Shortening  */
$conf->urlshort['user'] = '';
$conf->urlshort['key'] = '';


/*  Control filetypes  */
$conf->limitListing = false;
$conf->limitUploading = false;
$conf->AddFiletype(array('jpeg', 'jpg', 'png', 'gif'));


/*  Max upload size in bytes  */
$conf->uploadSize = 20971520; // 20 MB

/*  Sessions  */
$conf->session->mode = tSession::MODE_NATIVE;
//$conf->session->SetKey('For custom: uncomment and change this');
$conf->session->StartSession();

/* ****
 * End of config 
 * **** */

$int = new aFile_Interpreter($conf, $_GET, $_POST, new aFile_Executor());
$int->run();

exit(0);

/*
 *	Classes
 */


class aFile_Config
{
	const MODE_LITE = 0;
	const MODE_AUTH = 1;
	
	public $profile1 = array(
		'Upload' => true,
		'Delete' => true,
		'Rename' => true,
		'Move' => true,
		'New_File' => true,
		'New_Directory' => true,
		'Extract_Zip' => true,
		'Zip_Directory' => true
	);
	public $profile2 = array(
		'Upload' => true,
		'Delete' => true,
		'Rename' => true,
		'Move' => true,
		'New_File' => true,
		'New_Directory' => true,
		'Extract_Zip' => false,
		'Zip_Directory' => false
	);
	public $profile3 = array(
		'Upload' => true,
		'Delete' => true,
		'Rename' => false,
		'Move' => false,
		'New_File' => true,
		'New_Directory' => false,
		'Extract_Zip' => false,
		'Zip_Directory' => false
	);
	public $filetypes = array();
	public $urlshort = array('user' => '', 'key' => '');
	public $limitListing;
	public $limitUploading;
	public $mode;
	public $roots = array();
	public $uploadSize;
	public $user = array();
	public $session;
	
	public function __construct()
	{
		$this->session = new tSession();
	}
	
	public function AddFiletype($newType)
	{
		if(!is_array($newType))
		{
			$this->filetypes[] = $newType;
		}
		else
		{
			foreach($newType as $type)
			{
				$this->filetypes[] = $type;
			}
		}
	}
	
	public function AddRoot($newRoot)
	{
		$this->roots[] = $newRoot;
	}
	
	public function AddUser($usern, $pw, $profile, $path)
	{
		$this->user[$usern] = array(
			'pw' => $pw,
			'profile' => $profile,
			'path' => $path
		);
	}
}

class aFile_Executor
{
	private $currentId = 'ad';
	public $conf;
	
	public function Check()
	{
		header("Content-Type:application/json; charset=ISO-8859-1");
		
		$r = '';
		
		if($this->conf->mode == aFile_Config::MODE_LITE)
		{
			$r = '{"mode" : "lite", "login" : "null", "user" : "null"}';
		}
		else if($this->conf->mode == aFile_Config::MODE_AUTH)
		{
			if($this->conf->session->Get('aFile_Login') == 'true' && $this->conf->session->Get('aFile_IP') == $_SERVER['REMOTE_ADDR'])
			{
				$user = $this->conf->session->Get('aFile_User');
				$r = '{"mode" : "auth", "login" : "true", "user" : "'. $user .'","userpath":[';
				
				if(is_array($this->conf->user[$user]['path']))
				{
					$i = 0;
					foreach($this->conf->user[$user]['path'] as $path)
					{
						if($i > 0)
						{
							$r .= ', ';
						}
						
						$r .= '"'. $path .'"';
						$i++;
					}
				}
				else
				{
					$r .= '"'. $this->conf->user[$user]['path'] .'"';
				}
				$r .= '], "allow":[';
				
				$i = 0;
				foreach($this->conf->user[$user]['profile'] as $p => $value)
				{
					if($value)
					{
						if($i > 0)
						{
							$r .= ', ';
						}
						
						$r .= '"'. $p .'"';
						$i++;
					}
				}
				$r .= '], "deny":[';
				$i = 0;
				foreach($this->conf->user[$user]['profile'] as $p => $value)
				{
					if(!$value)
					{
						if($i > 0)
						{
							$r .= ', ';
						}
						
						$r .= '"'. $p .'"';
						$i++;
					}
				}
				$r .= ']}';
			}
			else
			{
				$r = '{"mode" : "auth", "login" : "false", "user" : "guest"}';
			}
		}
		
		return $r;
	}
	
	private function CheckPermission($service, $ad)
	{
		$user = $this->conf->session->Get('aFile_User');
		$users = $this->conf->user;
		
		if($users[$user]['profile'][$service])
		{
			if(is_array($users[$user]['path']))
			{
				foreach($users[$user]['path'] as $path)
				{
					$l = strlen($path);
					if(substr($ad, 0, $l) == $path)
					{
						return true;
					}
				}
			}
			else
			{
				$l = strlen($users[$user]['path']);
				if(substr($ad, 0, $l) == $users[$user]['path'])
				{
					return true;
				}
			}
		}
		return false;
	}
	
	public function Delete(&$get)
	{
		header("Content-Type:application/json; charset=ISO-8859-1");
		
		if($this->CheckPermission('Delete', $get['ad']))
		{
			$file = new tFile($get['root']);
			
			if(isset($get['ad']))
			{
				$file->ActiveDirectory = $get['ad'];
			}
			
			if($file->FileDelete($get['file']))
			{
				$file->FileDelete($get['file'] . '.desc');
				return '{"status":"ok", "message":"Success"}';
			}
			else
			{
				return '{"status":"error", "message":"File could not be deleted"}';
			}
		}
		else
		{
			return '{"status":"error", "message":"Permission denied"}';
		}
	}
	
	public function Edit($get)
	{
		$file = new tFile($get['root']);
		if(isset($get['ad']))
		{
			$file->ActiveDirectory = $get['ad'];
		}
		
		$r = $file->FileRead($get['file']);
		
		if ($r['status'] == 1)
		{
			return utf8_encode($r['content']);
		}
		else
		{
			return $r['status'];
		}
	}
	
	public function Extract($get)
	{
		header("Content-Type:application/json; charset=ISO-8859-1");
		
		if($this->CheckPermission('Extract_Zip', $get['ad']) && $this->CheckPermission('Extract_Zip', $get['path']))
		{
			$file = new tFile($get['root']);
			$file->ActiveDirectory = $get['ad'];
			$r = $file->ExtractZip($get['file'], $get['path']);
			
			if($r['status'] == 1)
			{
				return '{"status":"ok", "message":"Success"}';
			}
			else
			{
				return '{"status":"error", "message":"Zip could not be extracted"}';
			}
		}
		else
		{
			return '{"status":"error", "message":"Permission denied"}';
		}
	}
	
	public function ListContent($get)
	{
		header("Content-Type:application/json; charset=ISO-8859-1");
		
		$file = new tFile($get['root']);
		
		$ad = $get[$this->currentId];
		
		if(isset($ad))
		{
			$file->ActiveDirectory = $ad;
		}
		
		if ($this->conf->session->Get('aFile_User') != 'root')
		{
			$file->LimitedListing = $this->conf->limitListing;
			$file->AllowedFiletypes = $this->conf->filetypes;
		}
		
		$file->DefaultFileDesc = 'Not set.';
		$file->DefaultLinkDesc = 'Not set.';
		
		
		$r = $file->ListContent();
		if(strstr($ad, '/users') !== false)
		{
			if($this->conf->session->Get('aFile_Login') == 'true' && $this->conf->mode == aFile_Config::MODE_AUTH)
			{
				if ($this->conf->session->Get('aFile_User') == 'root')
				{
					return $r;
				}
				else if(strstr($ad, '/users/' . $this->conf->session->Get('aFile_User') . '/') !== false || preg_match("/\/users\/" . $this->conf->session->Get('aFile_User') . "$/", $ad))
				{
					return $r;
				}
				else if(strstr($ad, '/users/public') !== false)
				{
					return $r;
				}
				else if(substr($ad, -6, 6) == '/users')
				{
					return $r;
				}
				else
				{
					return '{"files":[{"name":"..","type":"dir"}]}';
				}
			}
			else
			{
				return '{"files":[{"name":"..","type":"dir"}]}';
			}
		}
		else
		{
			return $r;
		}
	}
	
	public function Login($post)
	{
		header("Content-Type:application/json; charset=ISO-8859-1");
		
		$r = '';
		if(array_key_exists($post['user'], $this->conf->user))
		{
			if(sha1($post['pw']) == $this->conf->user[$post['user']]['pw'])
			{
				$this->conf->session->Set('aFile_Login', 'true');
				$this->conf->session->Set('aFile_User', $post['user']);
				$this->conf->session->Set('aFile_IP', $_SERVER['REMOTE_ADDR']);
				$r = '{"status":"ok", "message":"Success!", "user":"'. $post['user'] .'"}';
			}
			else
			{
				$r = '{"status":"error", "message":"Wrong password."}';
			}
		}
		else
		{
			$r = '{"status":"error", "message":"Wrong username/password."}';
		}
		$this->conf->session->SetDone();
		return $r;
	}
	
	public function Logout()
	{
		$this->conf->session->Set('aFile_Login', 'false');
		$this->conf->session->Set('aFile_User', 'null');
		$this->conf->session->Set('aFile_IP', '0.0.0.0');
		$this->conf->session->SetDone();
	}
	
	public function Move($get)
	{
		header("Content-Type:application/json; charset=ISO-8859-1");
		
		if($this->CheckPermission('Move', $get['ad']) && $this->CheckPermission('Move', $get['src']))
		{
			$file = new tFile($get['root']);
			$file->ActiveDirectory = $get['src'];
			
			if($file->FileMove($get['file'], trim($get['ad'])))
			{
				$file->ActiveDirectory = $get['src'];
				$file->FileMove($get['file'] . '.desc', trim($get['ad']));
				return '{"status":"ok", "message":"Success"}';
			}
			else
			{
				return '{"status":"error", "message":"Could not move file"}';
			}
		}
		else
		{
			return '{"status":"error", "message":"Permission denied"}';
		}
	}
	
	public function NewDir($get)
	{
		header("Content-Type:application/json; charset=ISO-8859-1");
		
		if($this->CheckPermission('New_Directory', $get['ad']))
		{
			$file = new tFile($get['root']);
			if(isset($get['ad']))
			{
				$file->ActiveDirectory = $get['ad'];
			}
			
			if($file->CreateDirectory(trim($get['name'])))
			{
				return '{"status":"ok", "message":"Success"}';
			}
			else
			{
				return '{"status":"error", "message":"Could not create directory"}';
			}
		}
		else
		{
			return '{"status":"error", "message":"Permission denied"}';
		}
	}
	
	public function NewFile($post, $get)
	{
		header("Content-Type:application/json; charset=ISO-8859-1");
		
		if($this->CheckPermission('New_File', $get['ad']))
		{
			$file = new tFile($get['root']);
			if(isset($get['ad']))
			{
				$file->ActiveDirectory = $get['ad'];
			}
			if ($post["overwrite"] == "false")
			{
				$overwrite = false;
			}
			else
			{
				$overwrite = true;
			}
			
			$r = $file->FileWrite($post['name'], utf8_decode($post['content']), $overwrite);
			
			if($r['status'] == 1)
			{
				return '{"status":"ok", "message":"Success"}';
			}
			else
			{
				return '{"status":"error", "message":"'. $r['text'] .'"}';
			}
		}
		else
		{
			return '{"status":"error", "message":"Permission denied"}';
		}
	}
	
	public function Rename($get)
	{
		header("Content-Type:application/json; charset=ISO-8859-1");
		
		if($this->CheckPermission('Rename', $get['ad']))
		{
			$file = new tFile($get['root']);
			if(isset($get['ad']))
			{
				$file->ActiveDirectory = $get['ad'];
			}
			if ($objFiles->ActiveDirectory == '..' || $objFiles->ActiveDirectory == ".")
				return;
			
			if ($file->FileRename($get['file'], trim($get['newname'])))
			{
				$file->FileRename($get['file'] . '.desc', trim($get['newname']) . '.desc');
				return '{"status":"ok", "message":"Success"}';
			}
			else
			{
				return '{"status":"error", "message":"File could not be renamed"}';
			}
		}
		else
		{
			return '{"status":"error", "message":"Permission denied"}';
		}
	}
	
	public function Shorten($get)
	{
		if ($this->conf->urlshort['user'] == '' || $this->conf->urlshort['key'] == '')
		{
			return '{"data": {"url" : "URL Shortening is not configured"}}';
		}
		else
		{
			$url = "http://api.bitly.com/v3/shorten?login=". $this->conf->urlshort['user'] ."&apiKey=". $this->conf->urlshort['key'] ."&longUrl=". $get['link'] ."&format=json";
			
			if (ini_get('allow_url_fopen') == '1')
			{
				$result = file_get_contents($url);
			}
			else if (in_array('curl', get_loaded_extensions()))
			{
				$ch = curl_init();
				$timeout = 5;
				curl_setopt($ch,CURLOPT_URL,$url);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
				$result = curl_exec($ch);
				curl_close($ch);
			}
			else
			{
				return '{"data": {"url" : "Error, neither allow_url_fopen nor cURL is enabled. Enable one of them on the server."}}';
			}
			
			return $result;
		}
	}
	
	public function Upload($post)
	{
		if($this->CheckPermission('Upload',$post['aFile_AD']))
		{
			$file = new tFile($post['aFile_Root']);
			$file->ActiveDirectory = $post['aFile_AD'];
			
			if (isset($this->conf->uploadSize))
			{
				$file->MaxSize = $this->conf->uploadSize;
			}
			
			if ($this->conf->session->Get('aFile_User') != 'root')
			{
				$file->LimitedUploading = $this->conf->limitUploading;
				$file->AllowedFiletypes = $this->conf->filetypes;
			}
			
			if($post['aFile_UploadZip'] == 'true' && $this->CheckPermission('Extract_Zip',$this->conf->user,$post['aFile_AD']))
			{
				$file->ExtractZip = true;
			}
			
			$r = $file->UploadedFile(trim($post['aFile_UploadName']), $_FILES['aFile_UploadFile']);
			
			$text = $r['text'];
		}
		else
		{
			$text = 'Permission denied';
		}
		
		echo '
		<script type="text/javascript">
			window.parent.aFile.uploaded("'. $text .'");
			window.parent.aFile.write();
		</script>';
	}
	
	public function ZipDirectory($get)
	{
		header("Content-Type:application/json; charset=ISO-8859-1");
		
		if($this->CheckPermission('Zip_Directory', $get['ad']))
		{
			$file = new tFile($get['root']);
			$file->ActiveDirectory = $get['ad'];
			
			if($file->ZipDir($get['path'], $get['name']))
			{
				return '{"status":"ok", "message":"Success"}';
			}
			else
			{
				return '{"status":"error", "message":"Directory could not be zipped"}';
			}
		}
		else
		{
			return '{"status":"error", "message":"Permission denied"}';
		}
	}
}

class aFile_Interpreter
{
	private $get;
	private $post;
	
	private $conf;
	private $executor;
	
	private $allowed;
	
	public function __construct(&$confIn, &$getIn, &$postIn, &$dis)
	{
		$this->conf = $confIn;
		$this->executor = $dis;
		$this->get = $getIn;
		$this->post = $postIn;
		
		$this->executor->conf = $confIn;
	}
	
	public function run()
	{	
		$this->SetAllowed();
		
		if(isset($this->get['do']))
		{
			switch($this->get['do'])
			{
				case 'check':
					echo $this->executor->Check();
					break;
				
				case 'delete':
					if($this->ValidRoot($this->get['root']) && $this->allowed)
					{
						echo $this->executor->Delete($this->get);
					}
					break;
				
				case 'edit':
					if($this->ValidRoot($this->get['root']) && $this->allowed)
					{
						echo $this->executor->Edit($this->get);
					}
					break;
				
				case 'extract':
					if($this->ValidRoot($this->get['root']) && $this->allowed)
					{
						echo $this->executor->Extract($this->get);
					}
					break;
				
				case 'list':
					if($this->ValidRoot($this->get['root']))
					{
						echo $this->executor->ListContent($this->get);
					}
					break;
					
				case 'login':
					if ($this->conf->mode == aFile_Config::MODE_AUTH)
					{
						echo $this->executor->Login($this->post);
					}
					break;
				
				case 'logout':
					if ($this->conf->mode == aFile_Config::MODE_AUTH)
					{
						$this->executor->Logout();
					}
					break;
					
				case 'move':
					if($this->ValidRoot($this->get['root']) && $this->allowed)
					{
						echo $this->executor->Move($this->get);
					}
					break;
				
				case 'newdir':
					if($this->ValidRoot($this->get['root']) && $this->allowed)
					{
						echo $this->executor->NewDir($this->get);
					}
					break;
				
				case 'newtxt':
					if($this->ValidRoot($this->get['root']) && $this->allowed)
					{
						echo $this->executor->NewFile($this->post, $this->get);
					}
					break;
					
				case 'rename':
					if($this->ValidRoot($this->get['root']) && $this->allowed)
					{
						echo $this->executor->Rename($this->get);
					}
					break;
					
				case 'shorten':
					echo $this->executor->Shorten($this->get);
					break;
					
				case 'upload':
					if($this->ValidRoot($this->post['aFile_Root']) && $this->allowed)
					{
						$this->executor->Upload($this->post);
					}
					break;
				
				case 'zipdir':
					if($this->ValidRoot($this->get['root']) && $this->allowed)
					{
						echo $this->executor->ZipDirectory($this->get);
					}
					break;
			}
		}
	}
	
	private function SetAllowed()
	{
		if($this->conf->mode == aFile_Config::MODE_LITE)
		{
			$this->allowed = false;
		}
		else if($this->conf->mode == aFile_Config::MODE_AUTH)
		{
			if($this->conf->session->Get('aFile_Login') == 'true' && $this->conf->session->Get('aFile_IP') == $_SERVER['REMOTE_ADDR'])
			{
				$this->allowed = true;
			}
			else
			{
				$this->allowed = false;
			}
		}
	}
	
	private function ValidRoot($root)
	{
		if(isset($root))
		{
			if(in_array($root, $this->conf->roots))
			{
				return true;
			}
		}
		return false;
	}
}

class tSession
{
	const MODE_NATIVE = 0;
	const MODE_CUSTOM = 1;
	
	public $mode = self::MODE_NATIVE;
	public $customName = 'TSESS';
	
	private $key = 'change this key';
	private $customData = null;
	
	public function SetKey($key)
	{
		if (strlen($key) > 24)
		{
			$key = substr($key, 0, 24);
		}
		$this->key = $key;
	}
	
	private function Encrypt($text)
	{
		$td = mcrypt_module_open('tripledes', '', 'ecb', '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $this->key, $iv);
		$encrypted_data = mcrypt_generic($td, $text);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		
		return $encrypted_data;
	}
	
	public function Get($name)
	{
		if ($this->mode == self::MODE_NATIVE)
		{
			return $_SESSION[$name];
		}
		else
		{
			$session = $this->GetCustom();
			$sessarray = json_decode($session, true);
			return rawurldecode($sessarray[$name]);
		}
	}
	
	private function GetCustom()
	{
		if (isset($_COOKIE[$this->customName]))
		{
			$enc = $_COOKIE[$this->customName];
			
			$td = mcrypt_module_open('tripledes', '', 'ecb', '');
			$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
			mcrypt_generic_init($td, $this->key, $iv);
			$dec = mdecrypt_generic($td, $enc);
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
			
			$dec = preg_replace('/[^(\x20-\xFF)]*/','', $dec);
			return $dec;
		}
		else
		{
			return null;
		}
	}
	
	public function Set($name, $value)
	{
		if ($this->mode == self::MODE_NATIVE)
		{
			$_SESSION[$name] = $value;
		}
		else
		{
			if (!headers_sent() && is_string($value))
			{
				$this->SetCustom($name, $value);
			}
		}
	}
	
	private function SetCustom($name, $value)
	{
		if ($this->customData == null)
		{
			$session = $this->GetCustom();
		}
		else
		{
			$session = $this->customData;
		}
		$sessarray = json_decode($session, true);
		$sessarray[$name] = rawurlencode($value);
		$session = json_encode($sessarray);
		
		$this->customData = $session;
	}
	
	public function SetDone()
	{
		if ($this->mode == self::MODE_CUSTOM && !headers_sent())
		{
			if ($this->customData != null)
			{
				$enc = $this->Encrypt($this->customData);
				setcookie($this->customName, $enc);
			}
		}
	}
	
	public function StartSession()
	{
		if ($this->mode == self::MODE_NATIVE)
		{
			session_start();
		}
	}
	
}

?>





