<?php

class Edge_PrintProof_Model_Proof extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('printproof/proof');
    }
}
