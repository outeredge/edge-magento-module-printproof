<?php

$this->startSetup();

$awaitingApproval = Mage::getModel('sales/order_status');
$awaitingApproval->setStatus(Edge_PrintProof_Model_Proof::STATUS_AWAITING)
    ->setLabel('Awaiting Client Proof Approval')
    ->assignState(Mage_Sales_Model_Order::STATE_NEW)
    ->assignState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
    ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING)
    ->assignState(Mage_Sales_Model_Order::STATE_COMPLETE)
    ->assignState(Mage_Sales_Model_Order::STATE_CLOSED)
    ->assignState(Mage_Sales_Model_Order::STATE_CANCELED)
    ->assignState(Mage_Sales_Model_Order::STATE_HOLDED)
    ->assignState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)
    ->save();

$proofApproved = Mage::getModel('sales/order_status');
$proofApproved->setStatus(Edge_PrintProof_Model_Proof::STATUS_APPROVED)
    ->setLabel('Proof Approved')
    ->assignState(Mage_Sales_Model_Order::STATE_NEW)
    ->assignState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
    ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING)
    ->assignState(Mage_Sales_Model_Order::STATE_COMPLETE)
    ->assignState(Mage_Sales_Model_Order::STATE_CLOSED)
    ->assignState(Mage_Sales_Model_Order::STATE_CANCELED)
    ->assignState(Mage_Sales_Model_Order::STATE_HOLDED)
    ->assignState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)
    ->save();

$proofRejected = Mage::getModel('sales/order_status');
$proofRejected->setStatus(Edge_PrintProof_Model_Proof::STATUS_REJECTED)
    ->setLabel('Proof Rejected')
    ->assignState(Mage_Sales_Model_Order::STATE_NEW)
    ->assignState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
    ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING)
    ->assignState(Mage_Sales_Model_Order::STATE_COMPLETE)
    ->assignState(Mage_Sales_Model_Order::STATE_CLOSED)
    ->assignState(Mage_Sales_Model_Order::STATE_CANCELED)
    ->assignState(Mage_Sales_Model_Order::STATE_HOLDED)
    ->assignState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)
    ->save();

$this->endSetup();