<?php
/**
 * (c) Vespolina Project http://www.vespolina-project.org
 *
 * (c) Daniel Kucharski <daniel@xerias.be>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\WorkflowBundle\Tests\Mockup;

use Vespolina\WorkflowBundle\Model\WorkflowActivity;

class BillCustomerWorkflowActivity extends WorkflowActivity {

    public function execute()
    {

        $discount = $this->workflowContainer->get('discount');
        $total = $this->workflowContainer->get('total');

        if ($discount)
        {
            $totalToBeBilled = $total - ( $total * $discount / 100);
        }else{

            $totalToBeBilled = $total;
        }


        $this->workflowContainer->set('total_to_be_billed', $totalToBeBilled);

        $this->log('Customer needs to be billed for  ' . $totalToBeBilled . ' euro');
    }
}
