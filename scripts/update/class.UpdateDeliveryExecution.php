<?php
/**
 * 
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
 *
 */


class taoUpdate_scripts_update_UpdateDeliveryExecution extends tao_scripts_Runner {
    
    public function run() {
        
        // load the constants
        common_ext_ExtensionsManager::singleton()->getExtensionById('taoWfDelivery');
        
        $compilationClass = new core_kernel_classes_Class(CLASS_COMPILEDDELIVERY);
        foreach ($compilationClass->getInstances(true) as $compiledDelivery) {
            $workflow = $this->getWorkflow($compiledDelivery);
            $executions = wfEngine_models_classes_ProcessExecutionService::singleton()->getProcessExecutionsByDefinition($workflow);
            foreach ($executions as $execution) {
                $this->createDeliveryExecution($execution, $compiledDelivery);
            }
        }
    }
    
    private function getWorkflow(core_kernel_classes_Resource $compiledDelivery) {
        $runtime = $compiledDelivery->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_COMPILEDDELIVERY_RUNTIME));
        $inParameterCollection = $runtime->getPropertyValuesCollection(new core_kernel_classes_Property(PROPERTY_CALLOFSERVICES_ACTUALPARAMETERIN));
    
        $propActualParamConstantValue = new core_kernel_classes_Property(PROPERTY_ACTUALPARAMETER_CONSTANTVALUE);
        $propActualParamFormalParam = new core_kernel_classes_Property(PROPERTY_ACTUALPARAMETER_FORMALPARAMETER);
        $propFormalParamName = new core_kernel_classes_Property(PROPERTY_FORMALPARAMETER_NAME);
    
        foreach ($inParameterCollection->getIterator() as $inParameter){
    
            $formalParameter = $inParameter->getUniquePropertyValue($propActualParamFormalParam);
            if ($formalParameter->getUri() == 'http://www.tao.lu/middleware/wfEngine.rdf#FormalParamProcessDefinition') {
                $inParameterConstant = $inParameter->getOnePropertyValue($propActualParamConstantValue);
                if (!is_null($inParameterConstant) && $inParameterConstant instanceof core_kernel_classes_Resource) {
                    return $inParameterConstant;
                } else {
                    throw new common_exception_InconsistentData('missing workflow constant for service '.$service->getUri());
                }
            }
        }
        throw new common_exception_InconsistentData('no workflow parameter for '.$service->getUri());
    }
    
    private function createDeliveryExecution(core_kernel_classes_Resource $processExecution, $compiledDelivery) {
        //retrieve the data
        $status = $processExecution->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_PROCESSINSTANCES_STATUS));
        $deStatus = in_array($status->getUri(), array(INSTANCE_PROCESSSTATUS_FINISHED, INSTANCE_PROCESSSTATUS_CLOSED, INSTANCE_PROCESSSTATUS_STOPPED))
            ? INSTANCE_DELIVERYEXEC_FINISHED
            : INSTANCE_DELIVERYEXEC_ACTIVE;
        $activityExecutionService = wfEngine_models_classes_ActivityExecutionService::singleton();
        
        $activitiesExecutions = $processExecution->getPropertyValues(new core_kernel_classes_Property(PROPERTY_PROCESSINSTANCES_ACTIVITYEXECUTIONS));
        $processExecutionCreation = null;
        $user = null;
        foreach ($activitiesExecutions as $activityExecution) {
            $activityExecution = new core_kernel_classes_Resource($activityExecution);
            $values = $activityExecution->getPropertiesValues(array(
                PROPERTY_ACTIVITY_EXECUTION_TIME_CREATED,
                PROPERTY_ACTIVITY_EXECUTION_CURRENT_USER
            ));
            if (count($values[PROPERTY_ACTIVITY_EXECUTION_TIME_CREATED]) != 1 || count($values[PROPERTY_ACTIVITY_EXECUTION_CURRENT_USER]) != 1 ) {
                throw new common_exception_InconsistentData('Invalid activity execution '.$activityExecution->getUri());
            }
            $activityUser = current($values[PROPERTY_ACTIVITY_EXECUTION_CURRENT_USER]);
            if (is_null($user)) {
                $user = $activityUser;
            } else {
                if (!$user->equals($activityUser)) {
                    throw new common_exception_InconsistentData('Several users in a single delivery process execution');
                }
            }
            $creationTime = current($values[PROPERTY_ACTIVITY_EXECUTION_TIME_CREATED]);
            if (is_null($processExecutionCreation) || $processExecutionCreation > $creationTime) {
                $processExecutionCreation = $creationTime;
            }
        }
        
        //store the data
        $executionClass = new core_kernel_classes_Class(CLASS_DELVIERYEXECUTION);
        $execution = $executionClass->createInstanceWithProperties(array(
            RDFS_LABEL                           => $processExecution->getLabel(),
            PROPERTY_DELVIERYEXECUTION_DELIVERY  => $compiledDelivery,
            PROPERTY_DELVIERYEXECUTION_SUBJECT   => $user,
            PROPERTY_DELVIERYEXECUTION_START     => $processExecutionCreation,
            PROPERTY_DELVIERYEXECUTION_STATUS    => $deStatus
        ));
        
        
        // link process execution to delviery execution
        $serviceService = tao_models_classes_service_state_Service::singleton();
        $serviceService->set($user->getUri(), $execution->getUri(), $processExecution->getUri());
    }
}