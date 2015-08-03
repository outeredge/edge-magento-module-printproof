<?php

class Edge_PrintProof_Model_Observer
{
    protected function _sendNotification(Varien_Event_Observer $observer, $templateCode, $sendToAdmin = false)
    {
        $proof   = $observer->getEvent()->getProof();
        $order   = Mage::getModel('sales/order')->load($proof->getOrderId());
        $storeId = $order->getStoreId();

        if ($sendToAdmin) {
            $email = Mage::getStoreConfig('trans_email/ident_'.Mage::getStoreConfig('printproof/general/adminemail',
                        $storeId).'/email', $storeId);
            $name  = Mage::getStoreConfig('trans_email/ident_'.Mage::getStoreConfig('printproof/general/adminemail',
                        $storeId).'/name', $storeId);
        } else {
            $email = $order->getCustomerEmail();
            $name  = $order->getCustomerFirstname().' '.$order->getCustomerLastName();
        }

        $templateConfigPath = 'printproof/email/'.$templateCode;

        $mailTemplate = Mage::getModel('core/email_template');
        $template     = Mage::getStoreConfig($templateConfigPath, $storeId);

        $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
                $template, 'general', $email, $name,
                array(
                    'order' => $order,
                    'proof' => $proof
                )
        );

        return $this;
    }

    public function notifyCustomer(Varien_Event_Observer $observer)
    {
        $proof = $observer->getEvent()->getProof();
        $order = Mage::getModel('sales/order')->load($proof->getOrderId());
        $order->setStatus(Edge_PrintProof_Model_Proof::STATUS_AWAITING);
        $order->save();

        $this->_sendNotification($observer, 'create_notification_template');
        return $this;
    }

    public function approveCustomer(Varien_Event_Observer $observer)
    {
        $this->_updateOrderApproved($observer);
        $this->_sendNotification($observer, 'approve_notification_template');
        return $this;
    }

    public function rejectCustomer(Varien_Event_Observer $observer)
    {
        $this->_updateOrderRejected($observer);
        $this->_sendNotification($observer, 'reject_notification_template');
        return $this;
    }

    public function approveAdmin(Varien_Event_Observer $observer)
    {
        $this->_updateOrderApproved($observer);
        $this->_sendNotification($observer, 'approve_notification_admin_template', true);
        return $this;
    }

    public function rejectAdmin(Varien_Event_Observer $observer)
    {
        $this->_updateOrderRejected($observer);
        $this->_sendNotification($observer, 'reject_notification_admin_template', true);
        return $this;
    }

    protected function _updateOrderApproved($observer)
    {
        $proof = $observer->getEvent()->getProof();
        $order = Mage::getModel('sales/order')->load($proof->getOrderId());
        if (Mage::helper('printproof')->canUpdateOrderStatus($order)) {
            $order->setStatus(Edge_PrintProof_Model_Proof::STATUS_APPROVED);
            $order->save();
        }
    }

    protected function _updateOrderRejected($observer)
    {
        $proof = $observer->getEvent()->getProof();
        $order = Mage::getModel('sales/order')->load($proof->getOrderId());
        if (Mage::helper('printproof')->canUpdateOrderStatus($order)) {
            $order->setStatus(Edge_PrintProof_Model_Proof::STATUS_REJECTED);
            $order->save();
        }
    }

    public function sendAwaitingProofApproval()
    {
        $sendToAdmin  = 0;
        $templateCode = 'printproof_awaiting_proof_follow_up_email';

        $hours = Mage::getStoreConfig('printproof/email/printproof_awaiting_proof_follow_up_email_hours');
        if ($hours == 0) {
            return $this;
        }
        
        $time               = Mage::getModel('core/date')->timestamp(time());
        $lastProofTimestamp = $time - (60 * 60 * $hours);
        $lastProofTime      = date('Y-m-d H:i:s', $lastProofTimestamp);

        //Get all the order waiting proof approval
        $allProofsAwaitingApproval = Mage::getModel('printproof/proof')
            ->getCollection()
            ->addFieldToFilter('approved', array('eq' => 0))
            ->addFieldToFilter('rejected', array('eq' => 0))
            ->addFieldToFilter('creation_date', array('to' => $lastProofTime))
            ->addFieldToFilter('followup_date', array(array('to' => $lastProofTime), array('null' => true)));

        foreach ($allProofsAwaitingApproval as $proof) {
            $order   = Mage::getModel('sales/order')->load($proof->getOrderId());
            $storeId = $order->getStoreId();

            if ($sendToAdmin) {
                $email = Mage::getStoreConfig('trans_email/ident_'.Mage::getStoreConfig('printproof/general/adminemail',
                            $storeId).'/email', $storeId);
                $name  = Mage::getStoreConfig('trans_email/ident_'.Mage::getStoreConfig('printproof/general/adminemail',
                            $storeId).'/name', $storeId);
            } else {
                $email = $order->getCustomerEmail();
                $name  = $order->getCustomerFirstname().' '.$order->getCustomerLastName();
            }

            $templateConfigPath = 'printproof/email/'.$templateCode;

            $mailTemplate = Mage::getModel('core/email_template');
            $template     = Mage::getStoreConfig($templateConfigPath, $storeId);

            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                ->sendTransactional(
                    $template, 'general', $email, $name,
                    array(
                        'order' => $order,
                        'proof' => $proof
                    )
            );
            
            $proof->setFollowupDate(Mage::getModel('core/date')->timestamp(time()));
            $proof->save();
            
            if(Mage::getStoreConfig('printproof/general/enable_log')) {
                Mage::log('Proof reminder sent for proof #' . $proof->getId(), null, 'printproof.log');
            }
        }

        return $this;
    }

    public function setOrderCommentHistory(Varien_Event_Observer $observer)
    {
        $proof = $observer->getEvent()->getProof();
        $order = Mage::getModel('sales/order')->load($proof->getOrderId());

        $comments   = unserialize($proof->getComments());
        $commentMsg = end($comments);

        $order->addStatusHistoryComment('Item: '.$proof->getItemId().' | Comment: '.$commentMsg['comment']);
        $order->save();
    }
}
