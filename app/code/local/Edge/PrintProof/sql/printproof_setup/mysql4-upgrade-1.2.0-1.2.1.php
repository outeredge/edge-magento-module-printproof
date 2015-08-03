<?php

$this->startSetup();

// Printing Proofs
$this->run("
    ALTER TABLE `{$this->getTable('printproof/proof')}`
        ADD COLUMN `followup_date` timestamp NULL DEFAULT NULL;
");

$this->endSetup();