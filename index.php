<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

require('definitions.inc.php');

define('NEX', basename(__FILE__)); // will output something like : index.php

// System version
define('NEX_VERSION', '3.0.0');

// change to the real docroot if a symlink
if( is_link(NEX) ) chdir(DOC_ROOT);

include(SYS_PATH.'bootstrap'.NEX_EXT);
