<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author "Lionel Lecaque, <lionel@taotesting.com>"
 * @license GPLv2
 * @package package_name
 * @subpackage 
 *
 */

error_reporting(E_ALL);


$vendor_dir = dirname(__FILE__) . '/../vendor';

require_once 'SplClassLoader.php';


$classLoader = new SplClassLoader('OatBox', $vendor_dir );
$classLoader->register();

define('ROOT_PATH', dirname(__FILE__) . '/../');

OatBox\Common\Log\Dispatcher::singleton()->init(array(
	array(
		'class'			=> 'SingleFileAppender',
		'threshold'		=> \OatBox\Common\Logger::TRACE_LEVEL,
		'file'			=>  ROOT_PATH.'log/update.log',
)));


OatBox\Common\Logger::d('test');

$config = new OatBox\Common\Config(dirname(__FILE__).'/config.json');
$constants = $config->get($config::CONSTANTS);
$config->loadConstants($constants);


$request = new OatBox\Controller\Request();
$controller = new OatBox\Controller\Controller($request);
$controller->loadModule();


