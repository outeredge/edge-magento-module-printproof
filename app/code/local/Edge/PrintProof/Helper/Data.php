<?php

class Edge_PrintProof_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getProofImage($proof, $link=true)
    {
        $comments = $proof->getCommentList();
        foreach ($comments as $comment){
            if (isset($comment['attachment_url']) && $comment['attachment_url']){

                $extension = explode('.', $comment['attachment_url']);
                switch($extension[count($extension)-1]){
                    case "jpeg":
                    case "jpg":
                    case "gif":
                    case "png":
                        $html = '<img src="' . $comment['attachment_url'] . '" alt="Attachment">';
                        break;
                    case "pdf":
                    case "ai":
                        $html = '<span class="printproof-document-icon">' . strtoupper($extension[count($extension)-1]) . '</span>';
                        if($link){
                            $html.= '<span class="printproof-document-name">' . $comment['attachment'] . '</span>';
                        }
                        break;
                }
                if($link){
                    $html = '<a href="' . $comment['attachment_url'] . '" target="_blank">' . $html . '</a>';
                }
                return $html;
            }
        }
        return 'No Image';
    }

    public function getProofsForOrder($id=false)
    {
        if(!$id){
            $id = Mage::app()->getRequest()->getParam('order_id', false);
        }

        return Mage::getModel('printproof/proof')
            ->getCollection()
            ->addFieldToFilter('order_id', array('eq' => $id));
    }

    public function getProofsForCustomer()
    {
        if(!Mage::getSingleton('customer/session')->isLoggedIn()){
            return;
        }

        $customer = Mage::helper('customer')->getCustomer();

        $collection = Mage::getModel('printproof/proof')
            ->getCollection()
            ->setOrder('creation_date', 'DESC');

        $collection->getSelect()
            ->join(
                array('order' => 'sales_flat_order'),
                'order.entity_id = main_table.order_id AND order.customer_id = ' . $customer->getId(),
                array('order.customer_id', 'order.increment_id')
            )
            ->join(
                array('order_item' => 'sales_flat_order_item'),
                'order_item.item_id = main_table.item_id',
                array('item_name' => 'order_item.name')
            );

        return $collection;
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

        $comment = array(
            'admin' => Mage::app()->getStore()->isAdmin() ? 1 : 0,
            'date' => time()
        );

        if (Mage::app()->getStore()->isAdmin()){
            if (Mage::getStoreConfig('printproof/general/companyname')) {
                $comment['name'] = Mage::getStoreConfig('general/store_information/name');
            } else {
                $admin = Mage::getSingleton('admin/session')->getUser();
                $comment['name'] = $admin->getFirstname() . ' ' . $admin->getLastname();
            }
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

        return true;
    }

    public function canUpdateOrderStatus($order)
    {
        $proofs = Mage::getModel('printproof/proof')
            ->getCollection()
            ->addFieldToFilter('order_id', array('eq' => $order->getId()));

        $valid = true;
        foreach ($proofs as $proof){
            if(!$proof->getApproved() && !$proof->getRejected()){
                Mage::log($proof, null, 'Proof.log');
                $valid = false;
            }
        }

        return $valid;
    }
}