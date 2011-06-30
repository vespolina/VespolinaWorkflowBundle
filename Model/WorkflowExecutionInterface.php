<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * (c) Daniel Kucharski <daniel@xerias.be>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\WorkflowBundle\Model;

use Vespolina\WorkflowBundle\Model\WorkflowActivityInterface;

interface WorkflowExecutionInterface
{
    /**
     * Get the workflow configuration name
     *
     * @abstract
     * @return string
     */
    function getConfigurationName();

    /**
     * Returns whether or not the workflow has been suspended
     *
     */
    function getIsSuspended();

    /**
     * Get the workflow execution log
     */
    function getLog();

    /**
     * Get the current status of the overall workflow execution
     *
     * @abstract
     * @return void
     */
    function getStatus();

    /**
     * Return the workflow container
     * @return Vespolina\WorkflowBundle\Model\WorkflowContainerInterface
     */
    function getWorkflowContainer();

    /**
     * Get the runtime definition instance which is used to build this workflow execution instance
     */
    function getWorkflowRuntimeDefinition();
    
    function getWorkflowRuntimeExecution();

    /**
     * Get the workflow execution id
     */
    function getWorkflowExecutionId();


    /**
     * Log a message coming from a workflow activity
     */
    function logWorkflowActivityMessage(WorkflowActivityInterface $workflowActivity, $message, $type);

    /**
     * Set the workflow configuration name
     *
     * @abstract
     * @param  $name
     * @return void
     */
    function setConfigurationName($name);


    function setIsSuspended($isSuspended);

    /**
     * Set the workflow runtime definition
     *
     * @abstract
     * @param  $workflowRuntimeDefinition
     * @return void
     */
    function setWorkflowRuntimeDefinition($workflowRuntimeDefinition);

    /**
     * Set the workflow runtime execution of the definition
     *
     * @abstract
     * @param  $workflowRuntimeDefinition
     * @return void
     */
    function setWorkflowRuntimeExecution($workflowRuntimeExecution);

    /**
     * Set the workflow execution id
     *
     * @abstract
     * @param  $worfklowExecutionId
     * @return void
     */
    function setWorkflowExecutionId($worfklowExecutionId);
}
