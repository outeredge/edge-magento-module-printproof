<?php

class Edge_PrintProof_ProofController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();

        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)){
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    public function listAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function viewAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

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
        $proofId = $this->getRequest()->getParam('proof_id', false);

        Mage::helper('printproof')->addToExisting([
            'proof_id' => $proofId,
            'comment' => 'Approved by customer.'
        ]);

        $proof = Mage::getModel('printproof/proof');
        $proof->load($proofId);;

        $proof->setApproved(true);
        $proof->setApprovedDate(time());
        $proof->save();

        Mage::dispatchEvent('printproof_approved_customer', array('proof' => $proof));

        Mage::getSingleton('core/session')->addSuccess('Success! Your proof has been approved.');
        $this->_redirect('printproof/proof/list');
        return;
    }

    public function rejectAction()
    {
        $proofId = $this->getRequest()->getParam('proof_id', false);

        $params = $this->getRequest()->getParams();
        if (isset($params['comment']) && $params['comment']){
            Mage::helper('printproof')->addToExisting($params);
        }

        $proof = Mage::getModel('printproof/proof');
        $proof->load($proofId);

        $proof->setRejected(true);
        $proof->setRejectedDate(time());
        $proof->save();

        Mage::dispatchEvent('printproof_rejected_customer', array('proof' => $proof));
        $this->_redirect('printproof/proof/view', array('proof_id' => $proofId));
        return;
    }
}