<?php

class Edge_PrintProof_ProofController extends Mage_Core_Controller_Front_Action
{
    public function addToExistingAction()
    {
        $params = $this->getRequest()->getParams();
        if (Mage::helper('printproof')->addToExisting($params)){
            $this->_redirect('sales/order/view', array('order_id'  => $params['order_id']));
            return;
        }
        
        Mage::getSingleton('core/session')->addError('An error occured whilst attempting to update the proof.');
        $this->_redirect('sales/order/view', array('order_id'  => $params['order_id']));
        return;
    }
    
    public function approveAction()
    {
        $proof = Mage::getModel('printproof/proof');
        $proof->load($this->getRequest()->getParam('proof_id', false));
        
        $proof->setApproved(true);
        $proof->save();
        
        Mage::dispatchEvent('printproof_approved_customer', array('proof' => $proof));
        
        $this->_redirect('sales/order/view', array(
            'order_id' => $this->getRequest()->getParam('order_id', false)
        ));
        return;
    }
    
    public function rejectAction()
    {
        $proof = Mage::getModel('printproof/proof');
        $proof->load($this->getRequest()->getParam('proof_id', false));
        
        $proof->setRejected(true);
        $proof->save();
        
        Mage::dispatchEvent('printproof_rejected_customer', array('proof' => $proof));
        
        $this->_redirect('sales/order/view', array(
            'order_id' => $this->getRequest()->getParam('order_id', false)
        ));
        return;
    }
}