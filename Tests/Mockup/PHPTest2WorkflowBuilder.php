<?php

/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * (c) Daniel Kucharski <daniel@xerias.be>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\WorkflowBundle\Tests\Mockup;

use Vespolina\WorkflowBundle\Model\WorkflowBuilderInterface;
use Vespolina\WorkflowBundle\Model\WorkflowConfigurationInterface;
use Vespolina\WorkflowBundle\Model\WorkflowInstanceInterface;


class PHPTest2WorkflowBuilder implements WorkflowBuilderInterface
{
    protected $builderOptions;

    public function __construct($builderOptions = array())
    {
        $this->builderOptions = $builderOptions;
    }

    /**
     * @inheritdoc
     */
    public function build(WorkflowConfigurationInterface $workflowConfiguration, $workflowFactory)
    {

        //This test includes an activity which gets suspended because it needs user input

        //Start => Activity1 (suspend to the database)
        //Reactivate activity1 and test whether the user has set a discount value.
        //If yes -> continue , if no ->suspend again
        //Activity2 => End

        //Activity 1 asks external input which value 'discount' should get
        //Activity 2 sets workflow container value "total" to 1
        //Activity 2 calculates the final value to be billed (and could create the invoice at that point)


        $workflow = new \ezcWorkflow($workflowConfiguration->getName());

        $activity1 = $workflowFactory->createWorkflowActivityInvokerNode(
                            'Vespolina\WorkflowBundle\Tests\Mockup\AskForDiscountValueWorkflowActivity',
                            'ask_discount_value');
        
        $activity2 = $workflowFactory->createWorkflowActivityInvokerNode(
                            'Vespolina\WorkflowBundle\Tests\Mockup\AddOneToTotalWorkflowActivity',
                            'add_one_to_total');

        $activity3 = $workflowFactory->createWorkflowActivityInvokerNode(
                            'Vespolina\WorkflowBundle\Tests\Mockup\BillCustomerWorkflowActivity',
                            'bill_customer');
        $workflow->startNode->addOutNode($activity1);

        $activity1->addOutNode($activity2);
        $activity2->addOutNode($activity3);
        $activity3->addOutNode($workflow->endNode);
       

        return $workflow;
    }
}
