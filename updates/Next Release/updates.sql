UPDATE `credits` SET `next_payment_date`=`closing_date`;
ALTER TABLE  `credits` ADD  `user_id` INT UNSIGNED NOT NULL;
ALTER TABLE  `credits` ADD INDEX  `user_id` (  `user_id` );
ALTER TABLE  `credits` ADD  `type` ENUM(  'default',  'weekly',  'skipWeek' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'default';