<?php

/*

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


/**
*
* tFile is a class written in PHP used for multiple types of file operations.
*
*/

class tFile
{
	// The current (active) directory to be operated in
	public $ActiveDirectory = "/";
	// File upload max size (20 MB)
	public $MaxSize = 20971520;
	// Set to true to limit the filetypes available for upload
	public $LimitedUploading = false;
	// Set to true to limit the filetypes returned by ListContent()
	public $LimitedListing = false;
	// Change this to allow more filetypes
	public $AllowedFiletypes = array('jpg','jpeg','gif','png');
	// Whether a zip-file should be extracted or not
	public $ExtractZip = false;
	// File-descriptions
	public $DefaultFileDesc = "No Description";
	public $DefaultLinkDesc = "No Description";
	
	private $iId;
	private $treeRoot;
	private $treePath = "";
	
	function __construct($rootIn)
	{
		$this->treeRoot = $rootIn;
		if (func_num_args() >= 2)
		{
			$this->iId = func_get_arg(1);
		}
		else
		{
			$this->iId = 0;
		}
	}
	
	function __toString()
	{
		$this->SetPath();
		return urldecode($this->ActiveDirectory) . "§?§" . $this->treePath . "§?§" . realpath($this->treePath) . "§?§" . "tFile V3 (v3.1) by Tomas Thelander";
	}
	
	/**
	* Creates a directory
	*
	* @param string $Name , Name of the new directory
	*
	*/
	function CreateDirectory($Name)
	{
		$this->SetPath();
		return @mkdir($this->treePath . $Name, 0755);
	}
	
	/**
	* Extracts a zip-file
	*
	* @param string $File , File to be extracted
	* @param string $Path , Extraction target path
	*
	*/
	function ExtractZip($File, $Path)
	{
		$this->SetPath();
		$zip = new ZipArchive;

		if ($zip->open($this->treePath . $File) === TRUE)
		{
			$this->ActiveDirectory = $Path;
			$this->SetPath();
			$zip->extractTo($this->treePath);
			$zip->close();
			
			$return["status"] = 1;
			$return["text"] = "Zip extracted!";
			return $return;
		}
		else
		{
			$return["status"] = -1;
			$return["text"] = "Failed!";
			return $return;
		}
	}
	
	/**
	* Appends data to a file
	*
	* @param string $sName , Name of the file to be written to
	* @param string $sContent , The data to write
	*
	*/
	function FileAppend($sName, $sContent)
	{
		$return = array();
		$return["status"] = 0;
		$return["text"] = "Unrecognised error";
		
		$this->SetPath();
		
		$sFile = $this->treePath . $sName;
		
		$fh = fopen($sFile,"a");
			
		if (fwrite($fh, $sContent) !== false)
		{
			$return["status"] = 1;
			$return["text"] = "Success";
		}
		
		fclose($fh);
		return $return;
	}
	
	/**
	* Uses recursiveDelete() to delete a file or directory
	*
	* @param string $sFile , Name of the file or directory to be deleted
	*
	*/
	function FileDelete($sFile)
	{
		$sFile = str_replace("..","",$sFile);
		
		$this->SetPath();
		
		$filepath = $this->treePath . $sFile;
		
		return $this->recursiveDelete($filepath);
	}
	
	/**
	* Moves a file
	*
	* @param string $sFile , The file to be moved : 'file.txt'
	* @param string $sTarget , The target path : '/path/to'
	*
	*/
	function FileMove($sFile, $sTarget)
	{
		$this->SetPath();
		$oldfilepath = $this->treePath . $sFile;
		
		$this->ActiveDirectory = $sTarget;
		$this->SetPath();
		$newfilepath = $this->treePath . $sFile;
		
		return @rename($oldfilepath, $newfilepath);
	}
	
	/**
	* Reads data (text) from a file
	*
	* @param string $sName , Name of the file to be read
	*
	* Optional @param bool $bLineArray , If true, it will return the file content as an array, one-line-per-post.
	*
	*/
	function FileRead($sName)
	{
		$return = array();
		$return["status"] = 0;
		$return["text"] = "Unrecognised error";
		
		if (func_num_args() >= 2)
		{
			$bLineArray = func_get_arg(1);
		}
		
		$this->SetPath();
		
		$sFile = $this->treePath . $sName;
		
		if (!is_file($sFile))
		{
			$return["status"] = 0;
			$return["text"] = "Error, the file doesn't exist";
			return $return;
		}
		else
		{
			if (!$bLineArray)
			{
				$contents = @file_get_contents($sFile);
				if ($contents)
				{
					$return["status"] = 1;
					$return["text"] = "Success";
					$return["content"] = $contents;
				}
				else
				{
					$return["status"] = -1;
					$return["text"] = "File read not successful";
				}
				
			}
			else
			{
				$fh = fopen($sFile,"r");
				$i = 0;
				$contents = array();
				while (($contents[$i] = fgets($fh)) !== false)
				{
					$i++;
				}
				
				if (!empty($contents))
				{
					$return["status"] = 1;
					$return["text"] = "Success";
					$return["content"] = $contents;
				}
				else
				{
					$return["status"] = 0;
					$return["text"] = "File read not successful";
				}
				fclose($fh);
			}
			
			return $return;
		}
	}
	
	/**
	* Renames a file or directory
	*
	* @param string $sOldName , The file/directory to be renamed
	* @param string $sNewName , The new name
	*
	*/
	function FileRename($sOldName, $sNewName)
	{
		$this->SetPath();
		
		$sOldPath = $this->treePath . $sOldName;
		$sNewPath = $this->treePath . $sNewName;
		
		return @rename($sOldPath, $sNewPath);
	}
	
	/**
	* Writes data to a file
	*
	* @param string $sName , Name of the file to be written to
	* @param string $sContent , The data to write
	* @param bool $bOverwrite , Whether or not to overwrite the file if it already exists (true = overwrite)
	*
	*/
	function FileWrite($sName, $sContent, $bOverwrite)
	{
		$return = array();
		$return["status"] = -1;
		$return["text"] = "Unrecognised error";
		
		$this->SetPath();
		
		$sFile = $this->treePath . $sName;
		
		if (is_file($sFile) && !$bOverwrite)
		{
			$return["status"] = -1;
			$return["text"] = "Error, the file already exists";
			return $return;
		}
		else
		{
			$fh = fopen($sFile,"w");
			
			if (fwrite($fh, $sContent) !== false)
			{
				$return["status"] = 1;
				$return["text"] = "Success";
			}
			
			fclose($fh);
			return $return;
		}
	}
	
	/**
	* Fetches the current directory from the querystring-variable named TF§dir,
	* 		where § is the ID passed to the constructor. If no ID was passed, it is 0 (zero)
	* Optional @param string $sCustomQS , The name of a custom QueryString-variable that holds the directory
	*		E.g. "t§d" will fetch from t0d=/dir (if ID is default 0), and "t§" will fetch from t0=/dir
	*/
	function GetQS()
	{
		$out = "";
		
		if (func_num_args() >= 1)
		{
			$sCustomQS = func_get_arg(0);
			$arr_QS = explode("§", $sCustomQS);
		}
		else
		{
			$arr_QS = array();
			$arr_QS[0] = "TF";
			$arr_QS[1] = "dir";
		}
		
		$sQS = $arr_QS[0] . $this->iId . $arr_QS[1];
		
		if (isset($_GET[$sQS]))
		{
			$out = $_GET[$sQS];
		}
		
		return $out;
	}
	
	function IsFile($File)
	{
		$this->SetPath();
		return is_file($this->treePath . $File);
	}
	
	/**
	* Convert JSON output from ListContent() to XML
	*
	* @param string $sJSONin , JSON-formatted output from ListContent()
	*/
	function JSON2XML($sJSONin)
	{
		$arr_List = json_decode($sJSONin,true);
		
		if (is_array($arr_List['files']))
		{
			$out = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n<files>\n";
			
			foreach ($arr_List['files'] as $file)
			{
				$out .= "<file>\n";
				$out .= "<name>" . $file['name'] . "</name>\n";
				$out .= "<type>" . $file['type'] . "</type>\n";
				if ($file['type'] == "file")
					$out .= "<size>" . $file['size'] . "</size>\n";
					
				$out .= "</file>\n";
			}
			$out .= "</files>\n";
		}
		elseif (is_array($arr_List['errors']))
		{
			$out = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n<errors>\n";
			
			foreach ($arr_List['errors'] as $err)
			{
				$out .= "<error>\n";
				$out .= "<text>" . $err . "</text>\n";
				$out .= "</error>\n";
			}
			$out .= "</errors>\n";
		}
		else
		{
			$out = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n<errors>\n<error>\n<text>No valid JSON input</text>\n</error>\n</errors>";
		}
		
		//print_r($arr_List);
		return $out;
	}
	
	
	/**
	* List the contents of a directory. The directory-path is made from treeRoot and a current directory contained
	*	in $ActiveDirectory
	*
	*/
	function ListContent()
	{
		$arr_Exceptions = array(".");
		
		$this->SetPath();
		
		$result = array();
		$i = 0;
		
		if (is_dir($this->treePath))
		{
			if ($dh = opendir($this->treePath))
			{
				$result['files'] = array();
				
				while (($file = readdir($dh)) !== false)
				{
					if(!in_array($file, $arr_Exceptions))
					{
						if(is_dir($this->treePath.$file))
						{
							if ($this->treePath == $this->treeRoot && $file == "..")
							{
							}
							else
							{
								$result['files'][$i] = array();
								$result['files'][$i]['name'] = $file;
								$result['files'][$i]['type'] = "dir";
								$i++;
							}
						}
						elseif(is_file($this->treePath.$file) && substr($file,0,1) !== "." && substr($file,-5) !== ".desc" && substr($file,-5) !== ".alnk")
						{
							$exfilen = explode('.', $this->treePath.$file);
							$extension = strtolower($exfilen[sizeof($exfilen)-1]);
							
							if (!$this->LimitedListing || in_array($extension, $this->AllowedFiletypes))
							{
								$stat = stat($this->treePath.$file);
								$result['files'][$i] = array();
								$result['files'][$i]['name'] = $file;
								$result['files'][$i]['type'] = "file";
								$result['files'][$i]['size'] = $stat['size'];
								if (file_exists($this->treePath . $file . '.desc'))
								{
									$result['files'][$i]['desc'] = file_get_contents($this->treePath . $file . '.desc');
								}
								else
								{
									$result['files'][$i]['desc'] = $this->DefaultFileDesc;
								}
								$i++;
							}
						}
						elseif(is_file($this->treePath.$file) && substr($file,0,1) !== "." && substr($file,-5) == ".alnk")
						{
							$exfilen = explode('.', $this->treePath.$file);
							$extension = strtolower($exfilen[sizeof($exfilen)-1]);
							
							if (!$this->LimitedListing || in_array($extension, $this->AllowedFiletypes))
							{
								$link = json_decode(file_get_contents($this->treePath.$file), true);
								
								$result['files'][$i] = array();
								$result['files'][$i]['name'] = $link['name'];
								$result['files'][$i]['filename'] = $file;
								$result['files'][$i]['url'] = $link['url'];
								$result['files'][$i]['type'] = 'link';
								if (file_exists($this->treePath . $file . '.desc'))
								{
									$result['files'][$i]['desc'] = file_get_contents($this->treePath . $file . '.desc');
								}
								else
								{
									$result['files'][$i]['desc'] = $this->DefaultLinkDesc;
								}
								$i++;
							}
						}
					}
				}
				
				usort($result['files'], array("tFile","Sorting"));
			}
			
			else
			{
				$result['errors'][$i] = array();
				$result['errors'][$i] = "Could not open directory";
				$i++;
			}
		}
		
		else
		{
			$result['errors'][$i] = array();
			$result['errors'][$i] = "Path ". $this->treePath ." is not a directory";
			$i++;
		}
		
		return json_encode($result);
	}
	
	/**
	* Delete a file or recursively delete a directory
	*
	* @param string $str Path to file or directory
	*/
	private function recursiveDelete($str)
	{
		if(is_file($str))
		{
			return @unlink($str);
		}
		elseif(is_dir($str))
		{
			$scan = glob(rtrim($str,'/').'/*');
			
			// För att undvika error vid borttagning av tom mapp
			$olderror = error_reporting();
			error_reporting(0);
			
			foreach($scan as $index=>$path){
				$this->recursiveDelete($path);
			}
			
			error_reporting($olderror);
			
			return @rmdir($str);
		}
	}
	
	private function SetPath()
	{
		$current = urldecode($this->ActiveDirectory);
		// Tar bort bakåt-steg och enkel-punkter i sökvägen
		$current = str_replace("..","",$current);
		$current = str_replace("/./","/",$current);
		$current = str_replace("./","/",$current);
		
		
		// Sätter den fulla sökvägen, root (ex ./tree) + current (ex. hej/du) = ./tree/hej/du/
		// Ser också till att det sista tecknet är ett /
		if (substr($current, -1) == "/" || $current == "")
		{
			$this->treePath = $this->treeRoot . $current;
		}
		else
		{
			$this->treePath = $this->treeRoot . $current . "/";
		}
		
		// Tar bort multipla slash
		$this->treePath = preg_replace('#/+#','/',$this->treePath);
	}
	
	private function Sorting($a, $b)
	{
		$typea = ($a['type'] == 'dir') ? 'dir' : 'file';
		$typeb = ($b['type'] == 'dir') ? 'dir' : 'file';
		
		if (($typea == 'dir' && $typeb == 'dir') || ($typea == 'file' && $typeb == 'file'))
		{
			if ($a['type'] == 'file' && $b['type'] == 'link')
				return 1;
			
			if ($a['type'] == 'link' && $b['type'] == 'file')
				return -1;
			
			else
				return strcmp(strtolower($a['name']), strtolower($b['name']));
		}
		
		else if ($typea == 'dir' && $typeb == 'file')
			return -1;
			
		else if ($typea == 'file' && $typeb == 'dir')
			return 1;
			
	}
	
	/**
	* Takes care of an uploaded file, checks it's validity, sets the name and move it to desired location
	*	NOTICE! If a new subdirectory is set to be created, but no file to be uploaded, the directory will still be created
	*
	* @param string $filename , The wanted filename on the uploaded file.
	*		If NULL it will have the original filename
	* @param FILE array $uplfile , The uploaded file to be verified and moved to the correct location, e.g. $_FILES["name"]
	*
	* Optional @param string $nsubdir , Value "yes" or "true" or (bool)true indicates that it should be uploaded to a new subdirectory
	* Optional @param string $subname , The name of the new subdirectory
	*/
	function UploadedFile($filename, $uplfile)
	{
		// Change this to allow larger files
		//$maxsize = ((1024*1024)*20); // 20 Megabytes
		
		////// Don't touch the code below (unless you know what you're doing)
		
		$this->SetPath();
		
		$return = array();
		
		$letters = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6');
		
		// Checks if a new subdir is wanted and tries to create it
		if (func_num_args() >= 3)
		{
			$nsubdir = func_get_arg(2);
			
			if (func_num_args() >= 4)
			{
				$subname = func_get_arg(3);
				
				if (($nsubdir == "true" || $nsubdir == "yes" || $nsubdir == true) && $subname != "")
				{
					if (is_dir($this->treePath . $subname))
					{
						$return["status"] = 0;
						$return["text"] = "The new subdir already exists";
						return $return;
					}
					else 
					{
						mkdir($this->treePath . $subname, 0755);
						$this->treePath = $this->treePath . $subname . "/";
					}
				}
			}
		}
		
		// Aborts if no file was uploaded
		if(empty($uplfile["name"]))
		{
			$return["status"] = 0;
			$return["text"] = "No file has been chosen";
			return $return;
		}
		
		// Checks if the filesize is acceptable
		if($uplfile["size"] > $this->MaxSize)
		{
			$return["status"] = 0;
			$return["text"] = "Error, maxsize is ".(string)($this->MaxSize/1024)." kB.";
			return $return;
		}
		
		// Gets the file-extension
		$exfilen = explode('.', $uplfile['name']);
		$extension = $exfilen[sizeof($exfilen)-1];
		
		// If limited-mode is enabled it compare the file against the allowed types
		if($this->LimitedUploading)
		{
			if(!in_array(strtolower($extension), $this->AllowedFiletypes))
			{
				$return["status"] = 0;
				$return["text"] = "Error, filetype is not allowed";
				return $return;
			}
		}
		
		// Extracts zip if enabled
		if ($extension == "zip" && $this->ExtractZip)
		{
			$zip = new ZipArchive;

			if ($zip->open($uplfile['tmp_name']) === TRUE)
			{
				$zip->extractTo($this->treePath);
				$zip->close();
				
				$return["status"] = 1;
				$return["text"] = "Zip extracted!";
				return $return;
			}

			else
			{
				$return["status"] = 0;
				$return["text"] = "Error, the zip could not be extracted";
				return $return;
			}
		}
		
		// If a filename was passed to the function the file will be named accordingly
		if ($filename !== null && $filename !== "")
		{
			$file = $filename;
		}
		// Or else we leave the original name
		else
		{
			$file = $uplfile["name"];
		}
		
		// Creates a unique name if it is already taken
		while (file_exists($this->treePath.$file))
		{
			$file = $letters[rand(0, count($letters)-1)].$file;
		}
		
		// Moves the uploaded file to the desired destination
		if (is_uploaded_file($uplfile['tmp_name']) && move_uploaded_file($uplfile['tmp_name'],$this->treePath.$file))
		{
			$return["status"] = 1;
			$return["text"] = "The file was uploaded!";
		}
		else
		{
			$return["status"] = 0;
			$return["text"] = "Error, the file was not uploaded";
		}
				
		return $return;
	}
	
	
	/**
	* Creates a zip-file with the contents of a specified directory
	*
	* @param string $DirName , The path to the directory (e.g. '/path/to/Dir' will zip contents of Dir)
	* @param string $Name , The name of the zip-file to be created
	* 
	*/
	function ZipDir($DirName, $Name)
	{
		$temp = $this->ActiveDirectory;
		$this->ActiveDirectory = $DirName;
		$this->SetPath();
		$DirName = $this->treePath;
		$this->ActiveDirectory = $temp;
		$this->SetPath();
		$Zip = new Zipper();
		
		$NamePart = explode('.',$Name);
		$Extension = $NamePart[count($NamePart)-1];
		$NameFist = '';
		
		for ($l = 0; $l < count($NamePart)-1; $l++)
		{
			if ($l != count($NamePart)-2)
			{
				$NameFirst .= $NamePart[$l] . '.';
			}
			else
			{
				$NameFirst .= $NamePart[$l];
			}
		}
		
		$i = 1;
		while(file_exists($this->treePath . $Name))
		{
			$Name = $NameFirst . $i . '.' . $Extension;
			$i++;
		}
		
		if ($Zip->open($this->treePath . $Name, Zipper::CREATE) === true)
		{
			$Zip->addDir($DirName);
			$Zip->close();
			return true;
		}
		else
		{
			return false;
		}
	}
}

/**
*
* Add-on to zip directories
*
*/

class Zipper extends ZipArchive
{
	public function addDir($path)
	{
		$this->addEmptyDir($path);
		$nodes = glob($path . '/*');
		foreach ($nodes as $node)
		{
			if (is_dir($node))
			{
				$this->addDir($node);
			}
			else if (is_file($node))
			{
				$this->addFile($node);
			}
		}
	}
}

?>