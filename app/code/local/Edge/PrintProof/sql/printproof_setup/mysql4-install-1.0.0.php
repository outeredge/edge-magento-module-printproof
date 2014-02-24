<?php

$this->startSetup();

// Printing Proofs
$this->run("
    CREATE TABLE IF NOT EXISTS `{$this->getTable('printproof/proof')}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(10) unsigned NOT NULL,
        `comments` text NULL DEFAULT NULL,
        `approved` tinyint(1) NOT NULL DEFAULT '0',
        `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `approved_date` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `order_id` (`order_id`),
        CONSTRAINT `printproof_ibfk_1` 
            FOREIGN KEY (`order_id`) 
            REFERENCES `sales_flat_order` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();