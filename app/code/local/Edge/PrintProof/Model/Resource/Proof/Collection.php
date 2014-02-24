<?php

class Edge_PrintProof_Model_Resource_Proof_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	protected function _construct()
    {
        $this->_init('printproof/proof');
    }
}