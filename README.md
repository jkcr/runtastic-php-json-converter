# Runtastic PHP JSON Converter

A PHP script to convert a runtastic JSON Account Export to usable GPX Files.

This is a basic script to convert the JSON Files found in the Sport-sessions/GPS-data/ folder to GPX files. 

# Update

Runstastic now includes a folder in the export, located at 'Sport-sessions/GPS-data/', which includes GPX files. So, if your goal is to get a GPX file that can be used in a different program or service, it is no longer necessary to convert the JSON file, as is described below. 

However, they are not in human readable names, so you may still find this script useful.

# Usage

Download an account backup from Runtastic by logging in and going to Export. Download the provided Zip file and extract it. 

php ./runtastic_json_converter.php input_folder output_folder

input_folder = the 'Sport-sessions/GPS-data' folder from your account data

output_folder = the folder to save the generated GPX data

# Output

The script will check for json files and should output them to the output_folder using the format: runtastic_export_{DATE}.gpx

Any errors, a few of which will cause the script to break, will be echoed to output.

# Settings

The script should not overwrite existing files, unless you set $allow_clobber = true

Memory limit is set to -1

If you want to see PHP Warnings, set $debug_php_warnings = true;

# Known Issues

At least for my export, several of the exercises appear to be duplicated. When this happens, the 'id' is appened to the end of the file. 

I've not dug into this too deep to see why this is happening. 


