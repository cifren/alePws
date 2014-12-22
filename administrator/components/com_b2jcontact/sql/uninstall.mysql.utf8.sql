-- Extension specific tables
DROP TABLE IF EXISTS `#__b2jcontact_sessions`;
DROP TABLE IF EXISTS `#__b2jcontact_settings`;
DROP TABLE IF EXISTS `#__b2jcontact_details`;

-- Assets
DELETE FROM `#__assets` WHERE `name` = 'com_b2jcontact';

-- Installed extension
DELETE FROM `#__extensions` WHERE `element` = 'com_b2jcontact';

-- Installed modules
DELETE FROM `#__extensions` WHERE `element` = 'mod_b2jcontact';

-- Administrator menu item and Site menu items
DELETE FROM `#__menu` WHERE `link` LIKE '%com_b2jcontact%';

-- Site modules
DELETE FROM `#__modules` WHERE `module` = 'mod_b2jcontact';

-- Joomla auto updater
DELETE FROM `#__update_sites` WHERE `name` LIKE '%b2jcontact%';

