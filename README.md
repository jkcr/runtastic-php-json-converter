# Runtastic PHP JSON Converter

A PHP script to convert a runtastic JSON Account Export to usable GPX Files.

Recently, Runtastic decided to shutdown their website, which as far as I know, was the only way to easily export a GPX file of your runtastic activity. 

They do allow you to backup your account, using the export feature, but the export they provide uses a non-standard JSON format for your tracks and the file names are encoded strings, rather than human readable.

This is a basic script to convert the JSON Files found in the Sport-sessions/GPS-data/ folder to GPX files. 

# Usage

php ./runtastic_json_converter.php input_folder output_folder

input_folder = the 'Sport-sessions/GPS-data' folder from your account data

output_folder = the folder to save the generated GPX data

# Settings

The script should not overwrite existing files, unless you set $allow_clobber = true

# Known Issues

- I have not tested heart beat, although if it is present it may work.

- Elevation, temperature, calories, and other data is not currently supported. Only latitude, longitude, time, and possibly heart beat






