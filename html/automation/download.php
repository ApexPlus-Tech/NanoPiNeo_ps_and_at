<?php
class GoodZipArchive extends ZipArchive 
{
	//@author Nicolas Heimann
	public function __construct($a=false, $b=false) { $this->create_func($a, $b);  }
	
	public function create_func($input_folder=false, $output_zip_file=false)
	{
		if($input_folder && $output_zip_file)
		{
			$res = $this->open($output_zip_file, ZipArchive::CREATE);
			if($res === TRUE) 	{ $this->addDir($input_folder, basename($input_folder)); $this->close(); }
			else  				{ echo 'Could not create a zip archive. Contact Admin.'; }
		}
	}
	
    // Add a Dir with Files and Subdirs to the archive
    public function addDir($location, $name) {
        $this->addEmptyDir($name);
        $this->addDirDo($location, $name);
    }

    // Add Files & Dirs to archive 
    private function addDirDo($location, $name) {
        $name .= '/';         $location .= '/';
      // Read all Files in Dir
        $dir = opendir ($location);
        while ($file = readdir($dir))    {
            if ($file == '.' || $file == '..') continue;
          // Rekursiv, If dir: GoodZipArchive::addDir(), else ::File();
            $do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
            $this->$do($location . $file, $name . $file);
        }
    } 
}

session_start();
$f=fopen("/var/www/automation/download.txt",'r') or die("Could not open file.");
$line=fgets($f);
$line=str_replace(array("\n","\r"),'', $line);
$folderName=$line;
$zipFileName='/var/www/automation/boards/'.$folderName.'.zip';
$zipFile=new GoodZipArchive('/var/www/automation/boards/'. $folderName,$zipFileName)or die("Could not create zip file");
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename = "'.basename($zipFileName).'"');
header('Content-Length: ' . filesize($zipFileName));
readfile($zipFileName);
unlink($zipFileName);
?>
