drop table if exists t_ticket;

CREATE TABLE `t_ticket` (
  `tick_id` int(11) NOT NULL PRIMARY KEY auto_increment,
  `tick_title` varchar(100) COLLATE utf8_bin NOT NULL,
  `tick_content` varchar(3000) COLLATE utf8_bin NOT NULL,
  `tick_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;