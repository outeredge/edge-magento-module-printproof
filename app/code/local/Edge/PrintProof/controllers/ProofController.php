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
        $this->approve(true);
    }
    
    public function unapproveAction()
    {
        $this->approve(false);
    }
    
    protected function approve($approve)
    {
        $proof = Mage::getModel('printproof/proof');
        $proof->load($this->getRequest()->getParam('proof_id', false));
        
        $proof->setApproved($approve);
        $proof->save();
        
        $this->_redirect('sales/order/view', array(
            'order_id' => $this->getRequest()->getParam('order_id', false)
        ));
        return;
    }
}