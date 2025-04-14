<?php

// Get metadata for a DOI

require_once (dirname(__FILE__) . '/sqlite.php');

$basedir = '/Volumes/Expansion/dois';

function doi_to_metadata($doi)
{
	global $basedir;

	$metadata = null;
	
	$sql = 'SELECT * FROM doi WHERE doi="' . strtolower($doi) . '" LIMIT 1';
	
	$data = db_get($sql);
	
	// print_r($data);
	
	if (count($data) == 1)
	{
		$path = $data[0]->path;
		
		$gz_filename = $basedir . '/' . $data[0]->path;
		
		echo $gz_filename . "\n";
		
		$metadata = search_archive($gz_filename, $doi);		
	}
	
	return ($metadata);
}

function search_archive($gz_filename, $doi)
{
	$metadata = null;
	
	// https://stackoverflow.com/a/17685755
	
	// Raising this value may increase performance
	$buffer_size = 4096; // read 4kb at a time
	$out_filename = str_replace('.gz', '', $gz_filename); 
	
	// Open our files (in binary mode)
	$file = gzopen($gz_filename, 'rb');
	$outfile = fopen($out_filename, 'wb'); 
	
	// Keep repeating until the end of the input file
	while (!gzeof($file)) {
		// Read buffer-size bytes
		// Both fwrite and gzread and binary-safe
		fwrite($outfile, gzread($file, $buffer_size));
	}
	
	// Files are done, close files
	fclose($outfile);
	gzclose($file);		
	
	// Parse	
	$file = @fopen($out_filename, "r") or die("couldn't open $out_filename");
	
	$file_handle = fopen($out_filename, "r");
	while (!feof($file_handle)) 
	{
		$json = fgets($file_handle);
		
		$obj = json_decode($json);
		
		if (isset($obj->id) && ($obj->id == $doi))
		{
			$metadata = $obj;					
		}

	}
	
	fclose($file_handle);
	
	// clean up
	unlink($out_filename);	
	
	return $metadata;
}

$doi = '10.3204/desy-proc-2010-04/p83';
$doi = '10.14456/thnhmj.2021.3';

$metadata = doi_to_metadata($doi);

echo json_encode($metadata) . "\n";

?>
