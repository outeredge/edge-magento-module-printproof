<?php

class Edge_PrintProof_Model_Proof extends Mage_Core_Model_Abstract
{
    const STATUS_AWAITING = 'proof_awaiting';
    const STATUS_APPROVED = 'proof_approved';
    const STATUS_REJECTED = 'proof_rejected';

    public function _construct()
    {
        parent::_construct();
        $this->_init('printproof/proof');
    }

    public function getCommentList()
    {
        $data = array();
        $comments = unserialize($this->getComments());
        foreach ($comments as $comment){
            $comment['date'] = date('jS M Y H:i:s', $comment['date']);
            $data[] = $comment;
        }
        return $data;
    }

    public function getReplyUrl($admin=false)
    {
        $url = $admin ? 'printproofadmin/admin/addToExisting' : 'printproof/proof/addToExisting';
        return Mage::getUrl($url, array(
            'order_id' => Mage::app()->getRequest()->getParam('order_id', false),
            'proof_id' => $this->getId()
        ));
    }

    public function getRejectUrl()
    {
        return Mage::getUrl('printproof/proof/reject');
    }

    public function getApprovalUrl()
    {
        return Mage::getUrl('printproof/proof/approve', array(
            'order_id' => $this->getOrderId(),
            'proof_id' => $this->getId()
        ));
    }

    public function getStatus()
    {
        if ($this->getApproved()){
            return 'Approved';
        } elseif ($this->getRejected()) {
            return 'Rejected';
        } else {
            return 'Awaiting Approval';
        }
    }
}
