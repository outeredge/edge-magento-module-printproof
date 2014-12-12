<?php

$this->startSetup();

// Printing Proofs
$this->run("
    ALTER TABLE `{$this->getTable('printproof/proof')}`
        ADD COLUMN `rejected_date` timestamp NULL DEFAULT NULL;
");

$this->endSetup();