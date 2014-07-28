<?php
common_Logger::d('Add SysAdminRole to '.LOCAL_NAMESPACE. '#superUser');
$superUser = new core_kernel_classes_Resource(LOCAL_NAMESPACE.'#superUser');
$property = new core_kernel_classes_Property('http://www.tao.lu/Ontologies/generis.rdf#userRoles');
$superUser->setPropertyValue($property, 'http://www.tao.lu/Ontologies/TAO.rdf#SysAdminRole');

