<?php

// Directories
define('APP_PATH'       , 'app/'); // Container for apps code, design and i18n
define('CONF_PATH'      , 'conf/'); // Main system configuration, this is where you declare your apps and base functionnalities
define('VENDOR_PATH'    , 'vendor/'); // Packages, components, classes, anything that has nothing to do with framework's base
define('MEDIA_PATH'		, 'media/'); // Public files uploaded, should be accessible by http
define('ASSETS_PATH'    , 'assets/'); // front controller and your assets (images, JavaScript, CSS, etc.)
define('SYS_PATH'       , 'system/'); // System boot sector, no touch
define('VAR_PATH'       , 'var/'); // System files like logs, error templates, etc
define('TMP_PATH'		, 'tmp/'); // Temporary files

// File extensions
define('NEX_EXT'        , '.php');

// Folder separ
define('DS', DIRECTORY_SEPARATOR);

define('DOC_ROOT', dirname(realpath(__FILE__)).DS); // will output something like : /home/nex/    or C:\wamp\www\sas\
define('SCRIPT_ROOT', getcwd().DS); // Same as DOC_ROOT but path is defined depending of where the include/require came from. Will be the same as DOC_ROOT when index.php is called directly

// Char definitions
define('NEX_COMPAT', "'");
define('NEX_QUOTES', '"');
define('NEX_NO_QUOTES', '');
define('NEX_BACKTICK', '`');
define('NEX_EOL', PHP_EOL);
define('NEX_MAIL_EOL', NEX_EOL);


// Url base without domain
// Ex: /dir/ or /dir1/dir2/
define('NEX_BASE_URL', (isset($_SERVER['SCRIPT_NAME']) ? substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], "/")+1) : ''));

// Define max upload size
$_nex_maxsize = ini_get('upload_max_filesize');
$_nex_type = substr($_nex_maxsize, -1);
$_nex_value = substr($_nex_maxsize, 0, -1);

//Transform into bytes
switch(strtoupper($_nex_type))
{
	case 'P': $_nex_value *= 1024;
	case 'T': $_nex_value *= 1024;
	case 'G': $_nex_value *= 1024;
	case 'M': $_nex_value *= 1024;
	case 'K': $_nex_value *= 1024; break;
}
// Define constant max upload size constant
define("NEX_MAX_UPLOAD_SIZE", $_nex_value);
unset($_nex_maxsize, $_nex_type, $_nex_value);
