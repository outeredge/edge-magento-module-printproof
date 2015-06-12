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

    public function createProofAction()
    {
        $params = $this->getRequest()->getParams();
        $proof = Mage::getModel('printproof/proof');
        $admin = Mage::getSingleton('admin/session')->getUser();

        if (Mage::getStoreConfig('printproof/general/companyname')) {
            $name = Mage::getStoreConfig('general/store_information/name');
        } else {
            $name = $admin->getFirstname() . ' ' . $admin->getLastname();
        }

        $comment = array(
            'admin' => 1,
            'name' => $name,
            'date' => Mage::getModel('core/date')->timestamp(time())
        );
        if (isset($params['comment'])){
            $comment['comment'] = $params['comment'];
        }
        if (!empty($_FILES) && isset($_FILES['attachment'])){
            $attachment = Mage::helper('printproof')->saveAttachment();
            if ($attachment){
                $comment['attachment'] = $attachment;
            }
        }

        $proof->setOrderId($params['order_id']);
        $proof->setItemId($params['item_id']);
        $proof->setComments(serialize(array($comment)));
        $proof->save();
        
        Mage::dispatchEvent('printproof_create_adminhtml', array('proof' => $proof));
        $this->_redirect('adminhtml/sales_order/view', array('order_id'  => $params['order_id']));
        return;
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
        $proof->setApprovedDate(Mage::getModel('core/date')->timestamp(time()));
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
        $proof->setRejectedDate(Mage::getModel('core/date')->timestamp(time()));
        $proof->save();

        Mage::dispatchEvent('printproof_rejected_customer', array('proof' => $proof));
        $this->_redirect('adminhtml/sales_order/view', array('order_id'  => $params['order_id']));
        return;
    }
}
