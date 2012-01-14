<?php
/**
 * (c) 2011 Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Vespolina\WorkflowBundle\Tests\Service;

use DoctrineExtensions\Workflow\WorkflowOptions;
use DoctrineExtensions\Workflow\SchemaBuilder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Vespolina\WorkflowBundle\Model\Workflow;
use Vespolina\WorkflowBundle\Model\WorkflowAgent;
use Vespolina\WorkflowBundle\Model\WorkflowTask;

class WorkflowTest extends WebTestCase
{
    protected $client;

    public function setUp()
    {
        $this->client = $this->createClient();
    }

    public function getKernel(array $options = array())
    {
        if (!static::$kernel) {
            static::$kernel = $this->createKernel($options);
            static::$kernel->boot();
        }

        return static::$kernel;
    }

    public function testCreateWorkflowDBSchema()
    {

        if (true)
        {

            $dbalConn = $this->getKernel()->getContainer()->get('database_connection');
            $options = new WorkflowOptions($prefix = 'wf_');
            $schemaBuilder = new SchemaBuilder($dbalConn);
            $schemaBuilder->dropWorkflowSchema($options);
            $schemaBuilder->createWorkflowSchema($options);
        }

    }

    
    public function testCreateAgentAndTask()
    {

        $workflowTaskService = $this->getKernel()->getContainer()->get('vespolina.workflow_task');

        $workflowAgentJamesBond = new WorkflowAgent(true);
        $workflowAgentJamesBond->setName('James Bond 007');

        $workflowTask = $workflowTaskService->createTask();
        $workflowTask->setName('kill Goldfinger');
        $workflowTask->assignTo($workflowAgentJamesBond);

        $this->assertEquals('James Bond 007',
                            $workflowTask->getAssignedTo()->getName());

        $this->assertTrue($workflowTask->isAssigned());
        $this->assertTrue($workflowTask->getAssignedTo()->isHuman());

    }

    /**
     * @covers Vespolina\WorkflowBundle\Service\WorkflowService::create
     */
    public function test1WorkflowExecution()
    {
        /**
         *
         * Test scenario "test 1", workflow implementation can be found in Mockup\PHPTest1WorkflowBuilder.php
         *
         * In this test we try to have a very simple flow executed in memory (thus without db persistence)
         *
         * Start => Activity1 => Activity2 => End
         *
         * Activity 1 sets the workflow container value "total" to 1
         * Activity 2 sets the workflow container value "total" to 2
         * Activity 3 creates a workflow task to verify the result
         */

        $workflowService = $this->getKernel()->getContainer()->get('vespolina.workflow');
        $workflowTaskService = $this->getKernel()->getContainer()->get('vespolina.workflow_task');

        //The workflow service needs a DBAL connection to the database (Doctrine Extensions > Doctrine Workflow )
        $workflowService->setDbalConnection($this->getKernel()->getContainer()->get('database_connection'));

        $workflowConfiguration = $workflowService->getWorkflowConfiguration('test_1');
        $workflowConfiguration->setBuilderClass('Vespolina\WorkflowBundle\Tests\Mockup\PHPTest1WorkflowBuilder');

        //Save the workflow configuration to the database
        $workflowService->saveConfiguration($workflowConfiguration);

        //Create a workflow execution instance for the template workflow
        $workflowExecution = $workflowService->createWorkflowExecution($workflowConfiguration);

        //Verify that the workflow container holds the name of the workflow definition
        $this->assertEquals($workflowExecution->getWorkflowContainer()->get('workflow.name'), 'test_1');

        $workflowService->execute($workflowExecution);

        //$this->dumpWorkflowExecutionLog($workflowExecution->getLog());

        //The workflow container 'total' value should have value 2
        $this->assertEquals($workflowExecution->getWorkflowContainer()->get('total'), 2);

        //Verify if a task was created by the workflow
        $workflowAgentJamesBond = new WorkflowAgent(true);
        $workflowAgentJamesBond->setName('James Bond 007');

        $tasks = $workflowTaskService->getTasksForWorkflowAgent($workflowAgentJamesBond);
        
        $this->assertGreaterThan(0, count($tasks));

        //For now we assume that it's on the top of the list
        $task = $tasks[0];

        $this->assertEquals($task->getName(), 'verify_results');

        //Verify that James Bond is assigned to the task
        $this->assertEquals($task->getAssignedTo()->getName(), $workflowAgentJamesBond->getName());

        $this->displayWorkflowExecutionLog($workflowExecution);
    }


     public function test2WorkflowExecution()
    {
        /**
         *
         * Test scenario "test 2", workflow implementation can be found in Mockup\PHPTest2WorkflowBuilder.php

         * Start => Activity1 (suspend to the database)
         * Reactivate activity1 and test whether the user has set a discount value.
         * If yes -> continue , if no ->suspend again
         * Activity2 => End
         */

        $workflowService = $this->getKernel()->getContainer()->get('vespolina.workflow');
        $workflowTaskService = $this->getKernel()->getContainer()->get('vespolina.workflow_task');

        //The workflow service needs a DBAL connection to the database (Doctrine Extensions > Doctrine Workflow )
        $workflowService->setDbalConnection($this->getKernel()->getContainer()->get('database_connection'));

        $workflowConfiguration = $workflowService->getWorkflowConfiguration('test_2');
        $workflowConfiguration->setBuilderClass('Vespolina\WorkflowBundle\Tests\Mockup\PHPTest2WorkflowBuilder');

        //Save the workflow configuration to the database
        $workflowService->saveConfiguration($workflowConfiguration);

        //Create a workflow execution instance for the template workflow
        $workflowExecution = $workflowService->createWorkflowExecution($workflowConfiguration);

        //Verify that the workflow container holds the name of the workflow definition
        $this->assertEquals($workflowExecution->getWorkflowContainer()->get('workflow.name'), 'test_2');

        $workflowService->execute($workflowExecution);

        //Verify if the workflow has been suspended (a workflow activity could not be completed)
        $this->assertTrue($workflowExecution->getIsSuspended());


        //Now the UI would ask the user to enter the discount and set the workflow container value
        $workflowExecution->getWorkflowContainer()->set('discount', 5);

        //Here we go again
        $workflowService->execute($workflowExecution);

        $this->displayWorkflowExecutionLog($workflowExecution);



    }

    protected function displayWorkflowExecutionLog($workflowExecution)
    {
        $workflowName = $workflowExecution->getWorkflowContainer()->get('workflow.name');
        $workflowExecutionId = $workflowExecution->getWorkflowRuntimeExecution()->getId();

        echo "\r\nWorkflow execution log: for workflow $workflowName with execution id $workflowExecutionId\r\n";

        foreach($workflowExecution->getLog() as $item)
        {
            echo $item[0] . "\r\n";
        }

    }

}