#
# Modifying pages fe_users
#
CREATE TABLE fe_users (
	mobilephone varchar(255) DEFAULT '' NOT NULL,
	phone_business varchar(255) DEFAULT '' NOT NULL,
	date_of_birth INT(11) DEFAULT 0 NOT NULL,
    tx_jwfeusermanager_newsletter_subscribed TINYINT(1) UNSIGNED DEFAULT '1' NOT NULL,
	tx_jwfeusermanager_lastupdated INT(11) DEFAULT 0 NOT NULL,
    tx_jwfeusermanager_additional_text_1 text DEFAULT '' NOT NULL,
    tx_jwfeusermanager_additional_text_2 text DEFAULT '' NOT NULL,
    tx_jwfeusermanager_additional_text_3 text DEFAULT '' NOT NULL,
    tx_jwfeusermanager_additional_text_4 text DEFAULT '' NOT NULL,
    tx_jwfeusermanager_additional_text_5 text DEFAULT '' NOT NULL,
    tx_jwfeusermanager_additional_boolean_1 TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
    tx_jwfeusermanager_additional_boolean_2 TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
    tx_jwfeusermanager_additional_boolean_3 TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
    tx_jwfeusermanager_additional_boolean_4 TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
    tx_jwfeusermanager_additional_boolean_5 TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
    tx_jwfeusermanager_additional_bitfield_1 INT(11) UNSIGNED DEFAULT '0' NOT NULL,
    tx_jwfeusermanager_additional_bitfield_2 INT(11) UNSIGNED DEFAULT '0' NOT NULL,
    tx_jwfeusermanager_additional_bitfield_3 INT(11) UNSIGNED DEFAULT '0' NOT NULL,
    tx_jwfeusermanager_additional_bitfield_4 INT(11) UNSIGNED DEFAULT '0' NOT NULL,
    tx_jwfeusermanager_additional_bitfield_5 INT(11) UNSIGNED DEFAULT '0' NOT NULL,
);
CREATE TABLE fe_groups (
	image tinytext
);

CREATE TABLE tx_jwfeusermanager_editorfield (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	
	title varchar(255) DEFAULT '' NOT NULL,
	type varchar(255) DEFAULT '0' NOT NULL,
	db_field varchar(255) DEFAULT '' NOT NULL,
	db_mode varchar(255) DEFAULT '' NOT NULL,
	required tinyint(4) unsigned DEFAULT '0' NOT NULL,
	content text,
    selectoption_entries text,
	image_path varchar(255) DEFAULT '' NOT NULL,
	image_filename varchar(255) DEFAULT '' NOT NULL,
	redirect_page int(11) DEFAULT '0' NOT NULL,
	password_generator tinyint(4) unsigned DEFAULT '0' NOT NULL,
	
	email_mode varchar(255) DEFAULT '' NOT NULL,
	email_subject varchar(255) DEFAULT '' NOT NULL,
	email_recipient varchar(255) DEFAULT '' NOT NULL,
	email_content text,
	
	parent_ce int(11) DEFAULT '0' NOT NULL,
	
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,
	
	PRIMARY KEY (uid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)
);