<?php

class Edge_PrintProof_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getProofsForOrder($id=false)
    {
        if(!$id){
            $id = Mage::app()->getRequest()->getParam('order_id', false);
        }
        
        return Mage::getModel('printproof/proof')
            ->getCollection()
            ->addFieldToFilter('order_id', array('eq' => $id));
    }
    
    public function saveAttachment($proofId=false)
    {
        $path = Mage::getBaseDir('media') . '/printproof/';
        if (!file_exists(($path))){
            mkdir($path);
        }
        
        $name = 'attachment';
        if ($proofId){
            $name.= '_' . $proofId;
        }

        if (isset($_FILES[$name]) && (file_exists($_FILES[$name]['tmp_name']))){

            try {
                $uploader = new Varien_File_Uploader($name);
                $image = $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png','eps','pdf','ai','psd'))
                    ->setAllowRenameFiles(true)
                    ->setFilesDispersion(true)
                    ->save($path, $_FILES[$name]['name']);
                
                return $image['file'];

            } catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getTraceAsString());
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                return false;
            }
        }

        return false;
    }
    
    public function addToExisting($params)
    {
        $proof = Mage::getModel('printproof/proof');
        $proof->load($params['proof_id']);
        
        if (!$proof->getData()){
            // Failed to find the proof
            return;
        }
        
        $comment = array('date' => time());
        
        if (Mage::app()->getStore()->isAdmin()){
            $admin = Mage::getSingleton('admin/session')->getUser();
            $comment['name'] = $admin->getFirstname() . ' ' . $admin->getLastname();
        } else {
            // Get customer name
            $comment['name'] = Mage::helper('customer')->getCustomerName();
        }
        
        
        if (isset($params['comment'])){
            $comment['comment'] = $params['comment'];
        }
        if (!empty($_FILES) && isset($_FILES['attachment_' . $proof->getId()])){
            $attachment = Mage::helper('printproof')->saveAttachment($proof->getId());
            if ($attachment){
                $comment['attachment'] = $attachment;
            }
        }
        
        $updatedComments = unserialize($proof->getComments());
        $updatedComments[] = $comment;
        
        $proof->setComments(serialize($updatedComments));
        $proof->save();
        
        if (Mage::app()->getStore()->isAdmin()){
            Mage::dispatchEvent('printproof_update_adminhtml', array('proof' => $proof));
        } else {
            Mage::dispatchEvent('printproof_update_customer', array('proof' => $proof));
        }
        
        return true;
    }
    
    public function getProofHtml($proof)
    {
        $html = '<div class="proof">';
        
        if ($proof->getApproved()){
            $html.= '<ul class="messages"><li class="success-msg">Accepted</li></ul>';
        } else {
            $html.= '<ul class="messages"><li class="error-msg">Not Accepted</li></ul>';
        }
        
        if (Mage::app()->getStore()->isAdmin()){
            $action = Mage::getUrl('printproofadmin/admin/addToExisting', array(
                'order_id' => Mage::app()->getRequest()->getParam('order_id', false)
            ));
        } else {
            $action = Mage::getUrl('printproof/proof/addToExisting', array(
                'order_id' => Mage::app()->getRequest()->getParam('order_id', false)
            ));
            
            if ($proof->getApproved() == 0){
                $approveUrl = Mage::getUrl('printproof/proof/approve', array(
                    'order_id'      => Mage::app()->getRequest()->getParam('order_id', false),
                    'proof_id'   => $proof->getId()
                ));
                $html.= '<a href="' . $approveUrl . '" class="button button-approve">' . Mage::helper('printproof')->__('Approve') . '</a>';
            } else{
                $unapproveUrl = Mage::getUrl('printproof/proof/unapprove', array(
                    'order_id'      => Mage::app()->getRequest()->getParam('order_id', false),
                    'proof_id'   => $proof->getId()
                ));
                $html.= '<a href="' . $unapproveUrl . '" class="button button-unapprove">' . Mage::helper('printproof')->__('Unapprove') . '</a>';
            }
        }
        
        $comments = unserialize($proof->getComments());
        
        if (!empty($comments)){
            foreach ($comments as $comment){
                $html.= '<div class="comment">';
                $html.= '<div class="name"><strong>' . $comment['name'] . '</strong><br/>' . date('jS M Y H:i:s', $comment['date']) . '</div>';
                $html.= '<div class="text">' . $comment['comment'] . '</div>';
                $html.= '<div class="attachment"><a href="' . Mage::getBaseUrl('media') . 'printproof' . $comment['attachment'] . '">';
                $html.= '<img src="'. Mage::getBaseUrl('media') . 'printproof' . $comment['attachment'] . '" alt="" />';
                $html.= '</a></div>';
                $html.= '</div>';
            }
        }
        
        $html.= '<form action="' . $action . '" method="POST" enctype="multipart/form-data">';
        $html.= '<input type="hidden" name="proof_id" value="' . $proof->getId() . '"/>';
        $html.= '<input type="hidden" name="form_key" value="' . Mage::getSingleton('core/session')->getFormKey() . '"/>';
        $html.= '<div class="comment">';
        $html.= '<div class="name"><button type="submit" class="button">Add Comment</button></div>';
        $html.= '<div class="text"><textarea name="comment" class="textarea" style="width: 100%;"></textarea></div>';
        $html.= '<div class="attachment"><input type="file" name="attachment_' . $proof->getId() . '" /></div>';
        $html.= '</div>';
        $html.= '</form>';
        
        $html.= '</div>';
        return $html;
    }
    
    public function sendNotificationToCustomer()
    {
        $email = Mage::getModel('core/email_template');
        $email->loadDefault('printproof_email_notification');
        
        $email->send('mike@outeredgeuk.com', 'Michael Windell');
    }
}