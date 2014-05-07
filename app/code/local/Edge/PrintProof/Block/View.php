<?php

class Edge_PrintProof_Block_View extends Mage_Core_Block_Template
{
    public function getProof()
    {
        $proof = Mage::getModel('printproof/proof')->load(Mage::app()->getRequest()->getParam('proof_id', false));
        $order = Mage::getModel('sales/order')->load($proof->getOrderId());

        $proof->setIncrementId($order->getIncrementId());
        
        return $proof;
    }
}