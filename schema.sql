DROP TABLE IF EXISTS `planets`;
CREATE TABLE `planets` (
  `uuid` varchar(36) NOT NULL,
  `display_name` varchar(256) DEFAULT NULL,
  `key` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`uuid`),
  UNIQUE KEY `uuid_unique` (`uuid`),
  KEY `uuid_index` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `planet_temperatures`;
CREATE TABLE `planet_temperatures` (
  `uuid` varchar(36) CHARACTER SET latin1 NOT NULL COMMENT 'UUID of the "planet" (machine)',
  `chip_name` varchar(32) CHARACTER SET latin1 NOT NULL COMMENT 'Technical name that lm-sensors gives out for your device',
  `temp_number` int(2) NOT NULL COMMENT '0, 1, etc',
  `display_name` varchar(36) CHARACTER SET latin1 NOT NULL COMMENT 'Name that you wish for the adapter to be shown as',
  `last_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'DateTime of last update',
  `adapter` varchar(24) CHARACTER SET latin1 NOT NULL COMMENT 'Type of interface the chip is using, "ISA Adapter", "PCI Adapter", etc',
  `temp_input` decimal(6,3) unsigned DEFAULT NULL COMMENT 'Current temperature for the chip',
  `temp_max` decimal(6,3) unsigned DEFAULT NULL COMMENT 'Max temperature before computer _should_ turn off',
  `temp_max_hyst` decimal(6,3) unsigned DEFAULT NULL COMMENT 'Hysteresis (margin of error for the sensors)',
  `temp_crit` decimal(6,3) unsigned DEFAULT NULL COMMENT 'Temperature when the computer definitely should turn off',
  `temp_crit_hyst` decimal(6,3) unsigned DEFAULT NULL,
  `temp_emergency` decimal(6,3) unsigned DEFAULT NULL COMMENT '"You should run and go turn it off right now" temperature',
  `temp_emergency_hyst` decimal(6,3) unsigned DEFAULT NULL,
  UNIQUE KEY `uuid_chip_name_temp_number` (`uuid`,`chip_name`,`temp_number`),
  CONSTRAINT `planet_temperatures_ibfk_2` FOREIGN KEY (`uuid`) REFERENCES `planets` (`uuid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Temperatures for each "planet" (machine)';
