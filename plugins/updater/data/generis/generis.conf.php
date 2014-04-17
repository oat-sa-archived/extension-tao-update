
//content added during update 2.5 -> 2.6
define('VENDOR_PATH' , GENERIS_BASE_PATH.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR);

define('FILES_PATH',GENERIS_FILES_PATH)

# enable Lock, Extensions may rely on the lock mechanism
define('ENABLE_LOCK', false);

# default will use defautl php session handling
define('PHP_SESSION_HANDLER', 'default');

//End of content added during update 2.5 -> 2.6