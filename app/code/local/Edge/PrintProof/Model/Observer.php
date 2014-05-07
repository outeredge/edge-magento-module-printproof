<?php

class Edge_PrintProof_Model_Observer
{
    protected function _sendNotification(Varien_Event_Observer $observer, $template, $sendToAdmin=false)
    {
        $proof = $observer->getEvent()->getProof();
        $order = Mage::getModel('sales/order')->load($proof->getOrderId());
        
        if ($sendToAdmin){
            $email = Mage::getStoreConfig('trans_email/ident_general/email');
            $name  = Mage::getStoreConfig('trans_email/ident_general/name');
        } else {
            $email = $order->getCustomerEmail();
            $name  = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastName();
        }
        
        $emailModel = Mage::getModel('core/email_template');
        $emailModel->sendTransactional(
            $template,
            'general',
            $email,
            $name,
            array(
                'order' => $order,
                'proof' => $proof
            )
        );
        
        return $this;
    }
    
    public function notifyCustomer(Varien_Event_Observer $observer)
    {
        $this->_sendNotification($observer, 'printproof_create_email_notification');
        return $this;
    }
    
    public function approveCustomer(Varien_Event_Observer $observer)
    {
        $this->_sendNotification($observer, 'printproof_approve_email_notification');
        return $this;
    }
    
    public function rejectCustomer(Varien_Event_Observer $observer)
    {
        $this->_sendNotification($observer, 'printproof_reject_email_notification');
        return $this;
    }
    
    public function approveAdmin(Varien_Event_Observer $observer)
    {
        $this->_sendNotification($observer, 'printproof_approve_email_notification_admin', true);
        return $this;
    }
    
    public function rejectAdmin(Varien_Event_Observer $observer)
    {
        $this->_sendNotification($observer, 'printproof_reject_email_notification_admin', true);
        return $this;
    }
}