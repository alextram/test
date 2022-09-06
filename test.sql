CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(150) NOT NULL,
  `dept` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL
) ENGINE=InnoDB;