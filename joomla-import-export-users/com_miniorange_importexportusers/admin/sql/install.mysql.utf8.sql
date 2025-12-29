
CREATE TABLE IF NOT EXISTS `#__miniorange_importexport_customer_details` (
`id` int(11) UNSIGNED NOT NULL,
`email` VARCHAR(255) NOT NULL,
`password` VARCHAR(255) NOT NULL,
`admin_phone` VARCHAR(255) NOT NULL,
`customer_key` VARCHAR(255) NOT NULL,
`customer_token` VARCHAR(255) NOT NULL,
`api_key` VARCHAR(255) NOT NULL,
`status` VARCHAR(255) NOT NULL,
`login_status` tinyint(1) DEFAULT 0,
`importexport_keys` VARCHAR(255) NOT NULL,
`registration_status` VARCHAR(255) NOT NULL,
`transaction_id` VARCHAR(255) NOT NULL,
`email_count` int(11),
`admin_email` VARCHAR(255)  NOT NULL ,
`sms_count` int(11),
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__miniorange_exportusers` (
`id` int(11) UNSIGNED NOT NULL,
`enable_export_users` tinyint(1) DEFAULT 0,
`uninstall_feedback` int(2),
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

INSERT IGNORE INTO `#__miniorange_importexport_customer_details`(`id`,`login_status`) values (1,0);
INSERT IGNORE INTO `#__miniorange_exportusers`(`id`) values (1) ;


