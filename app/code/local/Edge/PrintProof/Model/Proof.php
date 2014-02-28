<?php

class Edge_PrintProof_Model_Proof extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('printproof/proof');
    }
    
    public function getCommentList()
    {
        $data = new Varien_Data_Collection();
        $comments = unserialize($this->getComments());
        foreach ($comments as $comment){
            
            $comment['date'] = date('jS M Y H:i:s', $comment['date']);
            if (isset($comment['attachment'])){
                $comment['attachment_url'] = Mage::getBaseUrl('media') . 'printproof' . $comment['attachment'];
            }
            
            $object = new Varien_Object();
            $object->setData($comment);
            $data->addItem($object);
        }
        return $data;
    }
    
    public function getReplyUrl($admin=false)
    {
        $url = $admin ? 'printproofadmin/adminhtml_proof/addToExisting' : 'printproof/proof/addToExisting';
        return Mage::getUrl($url, array(
            'order_id' => Mage::app()->getRequest()->getParam('order_id', false),
            'proof_id' => $this->getId()
        ));
    }
    
    public function getApprovalUrl()
    {
        if ($this->getApproved()){
            return Mage::getUrl('printproof/proof/unapprove', array(
                'order_id' => Mage::registry('current_order')->getId(),
                'proof_id' => $this->getId()
            ));
        } else {
            return Mage::getUrl('printproof/proof/approve', array(
                'order_id' => Mage::registry('current_order')->getId(),
                'proof_id' => $this->getId()
            ));
        }
    }
    
    public function getStatus()
    {
        if ($this->getApproved()){
            return 'Approved';
        } else {
            return 'Awaiting Approval';
        }
    }
}
