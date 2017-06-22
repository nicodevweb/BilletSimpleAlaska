drop table if exists t_ticket;
drop table if exists t_comment;
drop table if exists t_user;

CREATE TABLE t_ticket (
	tick_id integer NOT NULL PRIMARY KEY auto_increment,
	tick_title varchar(100) COLLATE utf8_bin NOT NULL,
	tick_content varchar(30000) COLLATE utf8_bin NOT NULL,
	tick_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) engine=innodb character SET utf8 collate utf8_unicode_ci;

create table t_user (
	usr_id integer NOT NULL PRIMARY KEY auto_increment,
	usr_name varchar(50) NOT NULL,
	usr_password varchar(88) NOT NULL,
	usr_salt varchar(23) NOT NULL,
	usr_role varchar(50) NOT NULL 
) engine=innodb character SET utf8 collate utf8_unicode_ci;

CREATE TABLE t_comment (
	com_id integer NOT NULL PRIMARY KEY auto_increment,
	com_author varchar(100) COLLATE utf8_bin NOT NULL,
	com_content varchar(3000) COLLATE utf8_bin NOT NULL,
	com_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	tick_id integer NOT NULL,
	usr_id integer NOT NULL,
	constraint fk_com_tick foreign key(tick_id) references t_ticket(tick_id),
	constraint fk_com_usr foreign key(usr_id) references t_user(usr_id)
) engine=innodb character SET utf8 collate utf8_unicode_ci;