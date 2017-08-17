# SQL for modifying database_role table to support Vertica and Mapr Database types
ALTER TABLE `database_role` CHANGE `database_type` `database_type` ENUM('mysql','hive','mapr','vertica')
CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'mysql';