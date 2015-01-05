<?php

/*
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * 
 */
$extpath = dirname(__FILE__).DIRECTORY_SEPARATOR;
$taopath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'tao'.DIRECTORY_SEPARATOR;
	
return array(
	'name' => 'taoUpdate',
	'description' => 'TAO Update extensions 2.6 => 2.7 http://www.taotesting.com',
	'version' => '1.0',
    'license' => 'GPL-2.0',
	'author' => 'Open Assessment Technologies',
	'requires' => array(
	    'tao' => '2.6',
	    'taoItems' => '2.6',
	    'taoTests' => '2.6',
	    'taoGroups' => '2.6',
	    'taoDelivery' => '2.6',
	),
	'models' => array(),
	'install' => array(
		'rdf' => array(
		),
		'checks' => array(
	
		),
		'php' => array(
		)
	),
    'uninstall' => array(
    ),	
    'managementRole' => 'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole',
    'acl' => array(
    	array('grant', 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole',array('ext'=>'taoUpdate','mod' => 'data')),
        array('grant', 'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole',array('ext'=>'taoUpdate'))
     ),
	'classLoaderPackages' => array( 
		dirname(__FILE__).'/actions/',
		dirname(__FILE__).'/helpers/'
	 ),
 	'constants' => array(
	 	# actions directory
		"DIR_ACTIONS"			=> $extpath."actions".DIRECTORY_SEPARATOR,
	
		# models directory
		"DIR_MODELS"			=> $extpath."models".DIRECTORY_SEPARATOR,
	
		# views directory
		"DIR_VIEWS"				=> $extpath."views".DIRECTORY_SEPARATOR,
	
		# helpers directory
		"DIR_HELPERS"			=> $extpath."helpers".DIRECTORY_SEPARATOR,
	
		# default module name
		'DEFAULT_MODULE_NAME'	=> 'Update',
	
		#default action name
		'DEFAULT_ACTION_NAME'	=> 'maintenance',
	
		#BASE PATH: the root path in the file system (usually the document root)
		'BASE_PATH'				=> $extpath,
 	    
 	    'BASE_DATA'				=> $extpath.'data'.DIRECTORY_SEPARATOR,
	
		#BASE URL (usually the domain root)
		'BASE_URL'				=> ROOT_URL . '/taoUpdate',
	
		#BASE WWW the web resources path
		'BASE_WWW'				=> ROOT_URL . '/taoUpdate/views/',
 	        
 
	 	#TAO extension Paths
		'TAOBASE_WWW'			=> ROOT_URL  . '/tao/views/',
		'TAOVIEW_PATH'			=> $taopath.'views'.DIRECTORY_SEPARATOR,
		'TAO_TPL_PATH'			=> $taopath.'views'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR,
	)
);
?>
