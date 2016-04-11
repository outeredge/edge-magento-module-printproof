<?php

class Edge_PrintProof_Block_Adminhtml_Sales_Order_View_Tab_Proof
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('printproof/proof.phtml');
    }

    public function getOrder()
    {
        $order = Mage::registry('current_order');
        if($order instanceof Mage_Sales_Model_Order){
            return $order;
        }

        $order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id', false));
        return $order;
    }

    public function getOrderId()
    {
        $orderId = false;
        $order = $this->getOrder();
        if($order){
            $orderId = $order->getId();
        }
        if(!$orderId){
            $orderId = Mage::app()->getRequest()->getParam('order_id', false);
        }
        return $orderId;
    }

    public function getTabLabel()
    {
        return Mage::helper('printproof')->__('Printing Proofs');
    }

    public function getTabTitle()
    {
        return Mage::helper('printproof')->__('Printing Proofs');
    }

    public function getTabClass()
    {
        return 'ajax only';
    }

    public function getClass()
    {
        return $this->getTabClass();
    }

    public function getTabUrl()
    {
        return $this->getUrl('printproofadmin/admin/list', array('_current' => true));
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->addJs('css/printproof.js');
        return parent::_prepareLayout();
    }
}
