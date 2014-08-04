<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Directories
define('APP_PATH'       , 'app/'); // Applications, this is where you code
define('CONF_PATH'      , 'conf/'); // Main system configuration, this is where you declare your apps and base functionnalities
define('DESIGN_PATH'	, 'design/'); // Application design
define('EXT_PATH'       , 'ext/'); // Packages, class, anything that has nothing to do with this framework
define('I18N_PATH'      , 'i18n/'); // System internationnalization
define('PRIV_PATH'		, 'private/'); // Private files, should not be accessible by http
define('PUB_PATH'		, 'public/'); // Public files, should be accessible by http
define('SKIN_PATH'      , 'skin/'); // Public files related to your apps like images, css, scripts. Accessible by http
define('SYS_PATH'       , 'system/'); // System boot sector, no touch
define('VAR_PATH'       , 'system/var/'); // System files like logs, error templates, etc
define('TMP_PATH'		, 'tmp/'); // Temporary files

// File extensions
define('NEX_EXT'        , '.php');

// Char definitions
define('NEX_COMPAT', "'");
define('NEX_QUOTES', '"');
define('NEX_NO_QUOTES', '');
define('NEX_BACKTICK', '`');
define('NEX_EOL', PHP_EOL);
define('NEX_MAIL_EOL', NEX_EOL);

//
// ----------------------------------------------------------------------------
//

// Define the front index name and docroot
define('DOC_ROOT', dirname(realpath(__FILE__)).DIRECTORY_SEPARATOR); // will output something like : /home/nex/    or C:\wamp\www\sas\
define('SCRIPT_ROOT', getcwd().DIRECTORY_SEPARATOR); // Same as DOC_ROOT but path is defined depending of where the include/require came from. Will be the same as DOC_ROOT when index.php is called directly
define('NEX', basename(__FILE__));               // will output something like : index.php

// change to the real docroot if external script allows it or if front controller is a symlink
if( is_link(NEX) or SCRIPT_ROOT != DOC_ROOT and (!defined("NEX_REAL_SCRIPT_ROOT") or NEX_REAL_SCRIPT_ROOT == false) ) chdir(DOC_ROOT);

include(SYS_PATH.'bootstrap'.NEX_EXT);
