<?php

// Parse JSONL files to map DOI to file that has metadata for that file

require_once (dirname(__FILE__) . '/sqlite.php');

//----------------------------------------------------------------------------------------

$basedir = '/Volumes/Expansion/dois';

$dirs = scandir($basedir);

//$dirs = array('updated_2011-11');

print_r($dirs);

$dirs=array(
/*
'updated_2022-03',
'updated_2022-04',
'updated_2022-05',*/
'updated_2022-06',
'updated_2022-07',
'updated_2022-08',
'updated_2022-09',
'updated_2022-10',
'updated_2022-11',
'updated_2022-12',
'updated_2023-01',
'updated_2023-02',
'updated_2023-03',
'updated_2023-04',
'updated_2023-05',
'updated_2023-06',
'updated_2023-07',
'updated_2023-08',
'updated_2023-09',
'updated_2023-10',
'updated_2023-11',
'updated_2023-12',
'updated_2024-01',
'updated_2024-02',
'updated_2024-03',
'updated_2024-04',
'updated_2024-05',
'updated_2024-06',
'updated_2024-07',
'updated_2024-08',
'updated_2024-09',
'updated_2024-10',
'updated_2024-11',
'updated_2024-12',
'updated_2025-01',
);


foreach ($dirs as $dir)
{
	if (preg_match('/updated/', $dir))
	{
		$updated_dir = $basedir . '/' . $dir;
		
		echo $updated_dir . "\n";
		
		$files = scandir($updated_dir);
		
		foreach ($files as $filename)
		{
			if (preg_match('/jsonl.gz/', $filename))
			{
				echo $filename . "\n";
			
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
				$file = @fopen($out_filename, "r") or die("couldn't open $out_filename");
				
				$row_count = 0;
						
				$file_handle = fopen($out_filename, "r");
				while (!feof($file_handle)) 
				{
					$json = fgets($file_handle);
					
					$obj = json_decode($json);
					
					if (isset($obj->id))
					{					
						// print_r($obj);					
						$sql = 'UPDATE doi SET path="' . $dir . '/' . $filename .  '", row="' . $row_count . '" WHERE doi="' . $obj->id . '";';						
						
						// echo $sql . "\n";
						
						db_put($sql);
						
						$row_count++;
					}

				}
				
				fclose($file_handle);
				
				// clean up
				unlink($out_filename);			
			}
	
		}
	
	}
}

?>
