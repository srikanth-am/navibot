CREATE TABLE `user_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(4) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `emp_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tester_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activation_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `user_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `domains` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `query_string` tinyint(4) NOT NULL DEFAULT 0,
  `total_urls` int(11) NOT NULL DEFAULT 0,
  `total_sitemaps` int(11) NOT NULL DEFAULT 0,
  `s_time` datetime DEFAULT NULL,
  `e_time` datetime DEFAULT NULL,
  `t_utilized` time DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `url_status` int(11) NOT NULL DEFAULT 0,
  `temp_status` int(11) NOT NULL DEFAULT 0,
  `url_progress` int(11) NOT NULL DEFAULT 0,
  `temp_progress` int(11) NOT NULL DEFAULT 0,
  `current_progress` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `http_status` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_domain` (`url`,`query_string`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 CREATE TABLE `sitemaps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain_id` bigint(20) unsigned DEFAULT NULL,
  `is_crawled` tinyint(4) NOT NULL DEFAULT 0,
  `http_status` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_domain` (`url`,`domain_id`),
  KEY `sitemaps_domain_id_foreign` (`domain_id`),
  CONSTRAINT `sitemaps_domain_id_foreign` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `urls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `domain_id` bigint(20) unsigned DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template` int(11) DEFAULT NULL,
  `is_crawled` tinyint(4) NOT NULL DEFAULT 0,
  `http_status` int(11) NOT NULL DEFAULT 200,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_url` (`domain_id`,`url`,`type`),
  UNIQUE KEY `unique_domainid_url_type` (`domain_id`,`url`,`type`),
  CONSTRAINT `urls_domain_id_foreign` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

CREATE TABLE `sales_domains` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rmve_qry_str` tinyint(4) NOT NULL DEFAULT '1',
  `total_urls` int(11) NOT NULL DEFAULT '0',
  `total_sitemaps` int(11) NOT NULL DEFAULT '0',
  `s_time` datetime DEFAULT NULL,
  `e_time` datetime DEFAULT NULL,
  `t_utilized` time DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `url_status` int(11) NOT NULL DEFAULT '0',
  `temp_status` int(11) NOT NULL DEFAULT '0',
  `url_progress` int(11) NOT NULL DEFAULT '0',
  `temp_progress` int(11) NOT NULL DEFAULT '0',
  `current_progress` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `http_status` int(11) NOT NULL DEFAULT '0',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_unique_domain` (`url`,`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 CREATE TABLE `sales_sitemaps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain_id` bigint(20) unsigned DEFAULT NULL,
  `is_crawled` tinyint(4) NOT NULL DEFAULT '0',
  `http_status` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_domain` (`url`,`domain_id`),
  KEY `sitemaps_domain_id_foreign` (`domain_id`),
  CONSTRAINT `sales_sitemaps_domain_id_foreign` FOREIGN KEY (`domain_id`) REFERENCES `sales_domains` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 CREATE TABLE `sales_urls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `domain_id` bigint(20) unsigned DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template` int(11) DEFAULT NULL,
  `is_crawled` tinyint(4) NOT NULL DEFAULT '0',
  `http_status` int(11) NOT NULL DEFAULT '200',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_unique_domainid_urls_type` (`url`,`type`,`domain_id`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE sales_domains DROP INDEX sales_unique_domain;

ALTER TABLE sales_domains ADD UNIQUE `sales_unique_domain`(`url`, `created_by`);


GRANT ALL ON *.* to root@'192.168.0.67' IDENTIFIED BY 'd3(Tg[L/absCRV8%';



DB::table('users')->insert(['name'=>'Srikanth Manivannan','email'=>'srikanth.manivannan@amnet-systems.com','password'=>Hash::make('123'), 'role_id'=>1, 'active'=>1])
DB::table('user_roles')->insert(['role'=>'Admin','is_default'=>1,'status'=>1]);
DB::table('user_roles')->insert(['role'=>'Production','is_default'=>1,'status'=>1]);
DB::table('user_roles')->insert(['role'=>'Sales','is_default'=>1,'status'=>1]);
