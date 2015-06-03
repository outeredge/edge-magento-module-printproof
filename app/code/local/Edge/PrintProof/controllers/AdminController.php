<?php

class Edge_PrintProof_AdminController extends Mage_Adminhtml_Controller_Action
{
    protected $_publicActions = array('list', 'createProof', 'addToExisting');

    public function listAction()
    {
        $this->loadLayout();
        $html = $this->getLayout()->createBlock('printproof/adminhtml_sales_order_view_tab_proof')->toHtml();
        $this->getResponse()->setBody($html);
    }

    public function addToExistingAction()
    {
        $params = $this->getRequest()->getParams();
        if (Mage::helper('printproof')->addToExisting($params)) {
            $this->_redirect('adminhtml/sales_order/view', array('order_id'  => $params['order_id']));
            return;
        }

        Mage::getSingleton('adminhtml/session')->addError('An error occured whilst attempting to update the proof.');
        $this->_redirect('adminhtml/sales_order/view', array('order_id'  => $params['order_id']));
        return;
    }

    public function approveProofAction()
    {
        $params = $this->getRequest()->getParams();
        $proof  = Mage::getModel('printproof/proof');
        $admin  = Mage::getSingleton('admin/session')->getUser();

        $proof->load($params['proof_id']);
        $proof->setApproved(true);
        $proof->setApprovedDate(time());
        $proof->save();

        Mage::dispatchEvent('printproof_approved_customer', array('proof' => $proof));
        $this->_redirect('adminhtml/sales_order/view', array('order_id'  => $params['order_id']));
        return;
    }

    public function rejectProofAction()
    {
        $params = $this->getRequest()->getParams();
        $proof  = Mage::getModel('printproof/proof');
        $admin  = Mage::getSingleton('admin/session')->getUser();

        $proof->load($params['proof_id']);
        $proof->setRejected(true);
        $proof->setRejectedDate(time());
        $proof->save();

        Mage::dispatchEvent('printproof_rejected_customer', array('proof' => $proof));
        $this->_redirect('adminhtml/sales_order/view', array('order_id'  => $params['order_id']));
        return;
    }
}
