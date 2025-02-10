-- ------
-- BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
-- DontLetItDie implementation : © Cutch <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----
-- dbmodel.sql
-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here
-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.
-- Example 1: create a standard "card" table to be used with the "Deck" tools (see example game "hearts"):
CREATE TABLE IF NOT EXISTS `game` (
    `game_id` BIGINT unsigned NOT NULL AUTO_INCREMENT,
    `selected_building` varchar(20) NULL,
    `day` int(1) UNSIGNED NOT NULL DEFAULT 1,
    `first_player_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `wood_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `stone_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `bone_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `meat_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `meat_cooked_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `fish_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `fish_cooked_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `dino_egg_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `dino_egg_cooked_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `berry_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `berry_cooked_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `fiber_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `hide_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `trap_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `herbs_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `stew_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `fkp_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    `gem_count` int(1) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`game_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
CREATE TABLE IF NOT EXISTS `card` (
    `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `card_name` varchar(20) NOT NULL,
    `card_type` varchar(16) NOT NULL,
    `card_type_arg` int(11) NOT NULL,
    `card_location` varchar(16) NOT NULL,
    `card_location_arg` int(11) NOT NULL,
    PRIMARY KEY (`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
-- CREATE TABLE IF NOT EXISTS `gather_card` (
--     `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--     `card_type` varchar(16) NOT NULL,
--     `card_type_arg` int(11) NOT NULL,
--     `card_location` varchar(16) NOT NULL,
--     `card_location_arg` int(11) NOT NULL,
--     PRIMARY KEY (`card_id`)
-- ) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
-- CREATE TABLE IF NOT EXISTS `harvest_card` (
--     `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--     `card_type` varchar(16) NOT NULL,
--     `card_type_arg` int(11) NOT NULL,
--     `card_location` varchar(16) NOT NULL,
--     `card_location_arg` int(11) NOT NULL,
--     PRIMARY KEY (`card_id`)
-- ) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
-- CREATE TABLE IF NOT EXISTS `hunt_card` (
--     `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--     `card_type` varchar(16) NOT NULL,
--     `card_type_arg` int(11) NOT NULL,
--     `card_location` varchar(16) NOT NULL,
--     `card_location_arg` int(11) NOT NULL,
--     PRIMARY KEY (`card_id`)
-- ) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
-- CREATE TABLE IF NOT EXISTS `explore_card` (
--     `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--     `card_type` varchar(16) NOT NULL,
--     `card_type_arg` int(11) NOT NULL,
--     `card_location` varchar(16) NOT NULL,
--     `card_location_arg` int(11) NOT NULL,
--     PRIMARY KEY (`card_id`)
-- ) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
-- CREATE TABLE IF NOT EXISTS `day_event_card` (
--     `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--     `card_type` varchar(16) NOT NULL,
--     `card_type_arg` int(11) NOT NULL,
--     `card_location` varchar(16) NOT NULL,
--     `card_location_arg` int(11) NOT NULL,
--     PRIMARY KEY (`card_id`)
-- ) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
-- CREATE TABLE IF NOT EXISTS `night_event_card` (
--     `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--     `card_type` varchar(16) NOT NULL,
--     `card_type_arg` int(11) NOT NULL,
--     `card_location` varchar(16) NOT NULL,
--     `card_location_arg` int(11) NOT NULL,
--     PRIMARY KEY (`card_id`)
-- ) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
-- CREATE TABLE IF NOT EXISTS `hindrance_event_card` (
--     `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
--     `card_type` varchar(16) NOT NULL,
--     `card_type_arg` int(11) NOT NULL,
--     `card_location` varchar(16) NOT NULL,
--     `card_location_arg` int(11) NOT NULL,
--     PRIMARY KEY (`card_id`)
-- ) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
ALTER TABLE `player`
ADD `character` INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `player`
ADD `stamina` int(1) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `player`
ADD `max_stamina` int(1) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `player`
ADD `health` int(1) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `player`
ADD `max_health` int(1) UNSIGNED NOT NULL DEFAULT 0;