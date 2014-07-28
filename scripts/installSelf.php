<?php

require_once dirname(__FILE__).'/../../tao/includes/raw_start.php';

$ext = common_ext_ExtensionsManager::singleton()->getExtensionById('taoUpdate');

$installer = new tao_install_ExtensionInstaller($ext);
$installer->install();

