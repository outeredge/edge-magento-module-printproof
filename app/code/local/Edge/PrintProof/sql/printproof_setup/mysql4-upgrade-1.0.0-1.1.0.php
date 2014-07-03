<?php

$this->startSetup();

$awaitingApproval = Mage::getModel('sales/order_status');
$awaitingApproval->setStatus('awaiting_client_proof_approval')
    ->setLabel('Awaiting Client Proof Approval')
    ->assignState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
    ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING)
    ->save();

$proofApproved = Mage::getModel('sales/order_status');
$proofApproved->setStatus('proofs_approved')
    ->setLabel('Proofs Approved')
    ->assignState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
    ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING)
    ->save();

$this->endSetup();