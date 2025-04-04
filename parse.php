<?php

require_once (dirname(__FILE__) . '/sqlite.php');

//----------------------------------------------------------------------------------------
// http://stackoverflow.com/a/5996888/9684
function translate_quoted($string) {
  $search  = array("\\t", "\\n", "\\r");
  $replace = array( "\t",  "\n",  "\r");
  return str_replace($search, $replace, $string);
}

//----------------------------------------------------------------------------------------

$basedir = '/Volumes/Expansion/dois';

$dirs = scandir($basedir);

print_r($dirs);

foreach ($dirs as $dir)
{
	if (preg_match('/updated/', $dir))
	{
		$updated_dir = $basedir . '/' . $dir;
		
		echo $updated_dir . "\n";
		
		$files = scandir($updated_dir);
		
		foreach ($files as $filename)
		{
			if (preg_match('/csv.gz/', $filename))
			{
				$gz_filename = join('/', [$basedir, $dir, $filename]);
				
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
				$headings = array();
				
				$row_count = 0;
				
				$file = @fopen($out_filename, "r") or die("couldn't open $out_filename");
						
				$file_handle = fopen($out_filename, "r");
				while (!feof($file_handle)) 
				{
					$row = fgetcsv(
						$file_handle, 
						0, 
						translate_quoted(','),
						translate_quoted('"') 
						);
						
					$go = is_array($row);
					
					if ($go)
					{
						if ($row_count == 0)
						{
							$headings = $row;		
						}
						else
						{
							$obj = new stdclass;
						
							foreach ($row as $k => $v)
							{
								if ($v != '')
								{
									$obj->{$headings[$k]} = $v;
								}
							}
						
							// print_r($obj);	
							
							$sql = obj_to_sql($obj, 'doi');
							db_put($sql);
						}
					}	
					$row_count++;
				}
				
				fclose($file_handle);
			
			}
	
		}
	
	}
}

?>
