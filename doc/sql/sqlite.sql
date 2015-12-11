/* $Id$ */
/* This file is part of DeforaOS Web DaPortal */
/* Copyright (c) 2011-2015 Pierre Pronchery <khorben@defora.org> */



PRAGMA encoding="UTF-8";

DROP TABLE daportal_blog_content;
DROP TABLE daportal_blog_user;
DROP TABLE daportal_caclient;
DROP TABLE daportal_caserver;
DROP TABLE daportal_ca;
DROP TABLE daportal_download;
DROP TABLE daportal_category_content;
DROP TABLE daportal_bookmark;
DROP TABLE daportal_probe_host;
DROP TABLE daportal_bug_reply;
DROP TABLE daportal_bug_reply_enum_state;
DROP TABLE daportal_bug_reply_enum_type;
DROP TABLE daportal_bug_reply_enum_priority;
DROP TABLE daportal_bug;
DROP TABLE daportal_bug_enum_state;
DROP TABLE daportal_bug_enum_type;
DROP TABLE daportal_bug_enum_priority;
DROP TABLE daportal_project_screenshot;
DROP TABLE daportal_project_download;
DROP TABLE daportal_project_user;
DROP TABLE daportal_project;
DROP TABLE daportal_top;
DROP TABLE daportal_comment;
DROP TABLE daportal_content_lang;
DROP VIEW daportal_content_public;
DROP VIEW daportal_content_enabled;
DROP TABLE daportal_content;
DROP TABLE daportal_auth_variable;
DROP TABLE daportal_user_group;
DROP TABLE daportal_user_reset;
DROP TABLE daportal_user_register;
DROP VIEW daportal_user_enabled;
DROP TABLE daportal_user;
DROP VIEW daportal_group_enabled;
DROP TABLE daportal_group;
DROP TABLE daportal_lang;
DROP TABLE daportal_sql_profile;
DROP TABLE daportal_profile;
DROP TABLE daportal_config;
DROP TABLE daportal_config_enum_type;
DROP VIEW daportal_module_enabled;
DROP TABLE daportal_module;


BEGIN TRANSACTION;


CREATE TABLE daportal_module (
	module_id INTEGER PRIMARY KEY,
	name VARCHAR(255) UNIQUE,
	enabled BOOLEAN NOT NULL DEFAULT FALSE
);

INSERT INTO daportal_module (name, enabled) VALUES ('admin', '1');
INSERT INTO daportal_module (name, enabled) VALUES ('search', '1');

CREATE VIEW daportal_module_enabled AS
SELECT module_id, name, enabled
FROM daportal_module
WHERE enabled='1';


CREATE TABLE daportal_config (
	config_id INTEGER PRIMARY KEY,
	module_id INTEGER NOT NULL,
	title VARCHAR(255) NOT NULL,
	type VARCHAR(255) NOT NULL,
	name VARCHAR(255) NOT NULL,
	value_bool BOOLEAN DEFAULT NULL,
	value_int INTEGER DEFAULT NULL,
	value_string VARCHAR(255) DEFAULT NULL,
	UNIQUE (module_id, name),
	FOREIGN KEY (module_id) REFERENCES daportal_module (module_id)
);
CREATE TABLE daportal_config_enum_type (
	name VARCHAR(255)
);
INSERT INTO daportal_config_enum_type (name) VALUES ('bool');
INSERT INTO daportal_config_enum_type (name) VALUES ('int');
INSERT INTO daportal_config_enum_type (name) VALUES ('string');


CREATE TABLE daportal_profile (
	profile_id INTEGER PRIMARY KEY,
	timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	load1 INTEGER NOT NULL,
	load5 INTEGER NOT NULL,
	load15 INTEGER NOT NULL,
	time INTEGER NOT NULL,
	mem_usage INTEGER NOT NULL,
	mem_usage_real INTEGER NOT NULL,
	mem_peak INTEGER NOT NULL,
	mem_peak_real INTEGER NOT NULL
);

CREATE TABLE daportal_sql_profile (
	sql_profile_id INTEGER PRIMARY KEY,
	timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	time INTEGER NOT NULL,
	query VARCHAR(255) NOT NULL
);


CREATE TABLE daportal_lang (
	lang_id VARCHAR(2) PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	enabled BOOLEAN NOT NULL DEFAULT FALSE
);

INSERT INTO daportal_lang (lang_id, name, enabled) VALUES ('en', 'English', '1');
INSERT INTO daportal_lang (lang_id, name, enabled) VALUES ('fr', 'Français', '1');
INSERT INTO daportal_lang (lang_id, name, enabled) VALUES ('de', 'Deutsch', '1');


CREATE TABLE daportal_group (
	group_id INTEGER PRIMARY KEY,
	groupname VARCHAR(255) NOT NULL UNIQUE,
	enabled BOOLEAN NOT NULL DEFAULT FALSE
);

INSERT INTO daportal_module (name, enabled) VALUES ('group', '1');
INSERT INTO daportal_group (group_id, groupname, enabled) VALUES ('0', 'nogroup', '1');

CREATE VIEW daportal_group_enabled AS
SELECT group_id, groupname, enabled
FROM daportal_group
WHERE enabled='1';


CREATE TABLE daportal_user (
	user_id INTEGER PRIMARY KEY,
	username VARCHAR(255) NOT NULL UNIQUE,
	group_id INTEGER NOT NULL DEFAULT 0,
	password VARCHAR(255),
	enabled BOOLEAN NOT NULL DEFAULT FALSE,
	admin BOOLEAN NOT NULL DEFAULT FALSE,
	fullname VARCHAR(255) NOT NULL DEFAULT '',
	email VARCHAR(255) NOT NULL,
	FOREIGN KEY (group_id) REFERENCES daportal_group (group_id)
);

INSERT INTO daportal_module (name, enabled) VALUES ('user', '1');
INSERT INTO daportal_user (user_id, username, password, enabled, fullname, email) VALUES ('0', 'Anonymous', '!', '1', 'Anonymous user', '');
INSERT INTO daportal_user (username, password, enabled, admin, fullname, email) VALUES ('admin', '$1$?0p*PI[G$kbHyE5VE/S32UrV88Unz/1', '1', '1', 'Administrator', 'username@domain.tld');

CREATE VIEW daportal_user_enabled AS
SELECT user_id, username, group_id, password, enabled, admin, fullname, email
FROM daportal_user
WHERE enabled='1';

CREATE TABLE daportal_user_register (
	user_register_id INTEGER PRIMARY KEY,
	user_id INTEGER NOT NULL,
	token VARCHAR(255) UNIQUE NOT NULL,
	timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (user_id) REFERENCES daportal_user (user_id)
);

CREATE TABLE daportal_user_reset (
	user_reset_id INTEGER PRIMARY KEY,
	user_id INTEGER NOT NULL,
	token VARCHAR(255) UNIQUE NOT NULL,
	timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (user_id) REFERENCES daportal_user (user_id)
);

CREATE TABLE daportal_user_group (
	user_group_id INTEGER PRIMARY KEY,
	user_id INTEGER NOT NULL,
	group_id INTEGER NOT NULL,
	FOREIGN KEY (user_id) REFERENCES daportal_user (user_id),
	FOREIGN KEY (group_id) REFERENCES daportal_group (group_id),
	UNIQUE (user_id, group_id)
);


CREATE TABLE daportal_auth_variable (
	auth_variable_id INTEGER PRIMARY KEY,
	user_id INTEGER NOT NULL,
	variable VARCHAR(255) NOT NULL,
	value VARCHAR(255) NOT NULL,
	FOREIGN KEY (user_id) REFERENCES daportal_user (user_id),
	UNIQUE (user_id, variable)
);


CREATE TABLE daportal_content (
	content_id INTEGER PRIMARY KEY,
	timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	module_id INTEGER,
	user_id INTEGER,
	group_id INTEGER DEFAULT '0',
	title VARCHAR(255),
	content TEXT,
	enabled BOOLEAN NOT NULL DEFAULT FALSE,
	public BOOLEAN NOT NULL DEFAULT FALSE,
	FOREIGN KEY (module_id) REFERENCES daportal_module (module_id),
	FOREIGN KEY (user_id) REFERENCES daportal_user (user_id),
	FOREIGN KEY (group_id) REFERENCES daportal_group (group_id)
);

CREATE INDEX daportal_content_title_index ON daportal_content (title);
CREATE INDEX daportal_content_user_id_index ON daportal_content (user_id);

CREATE VIEW daportal_content_enabled AS
SELECT daportal_content.content_id AS content_id,
daportal_content.timestamp AS timestamp,
daportal_content.module_id AS module_id,
daportal_module.name AS module,
daportal_content.user_id AS user_id,
daportal_user.username AS username,
daportal_content.group_id AS group_id,
daportal_group.groupname AS groupname,
daportal_content.title AS title, daportal_content.content AS content,
daportal_content.enabled AS enabled, daportal_content.public AS public
FROM daportal_content, daportal_module, daportal_user, daportal_group
WHERE daportal_content.module_id=daportal_module.module_id
AND daportal_content.user_id=daportal_user.user_id
AND daportal_content.group_id=daportal_group.group_id
AND daportal_module.enabled='1'
AND daportal_user.enabled='1'
AND daportal_content.enabled='1';

CREATE VIEW daportal_content_public AS
SELECT daportal_content.content_id AS content_id,
daportal_content.timestamp AS timestamp,
daportal_content.module_id AS module_id,
daportal_module.name AS module,
daportal_content.user_id AS user_id,
daportal_user.username AS username,
daportal_content.group_id AS group_id,
daportal_group.groupname AS groupname,
daportal_content.title AS title, daportal_content.content AS content,
daportal_content.enabled AS enabled, daportal_content.public AS public
FROM daportal_content, daportal_module, daportal_user, daportal_group
WHERE daportal_content.module_id=daportal_module.module_id
AND daportal_content.user_id=daportal_user.user_id
AND daportal_module.enabled='1'
AND daportal_content.group_id=daportal_group.group_id
AND daportal_user.enabled='1'
AND daportal_content.enabled='1'
AND daportal_content.public='1';

CREATE TABLE daportal_content_lang (
	content_lang_id INTEGER PRIMARY KEY,
	content_id INTEGER NOT NULL,
	lang_id VARCHAR(2),
	title VARCHAR(255),
	content TEXT,
	FOREIGN KEY (content_id) REFERENCES daportal_content (content_id),
	FOREIGN KEY (lang_id) REFERENCES daportal_lang (lang_id)
);


/* module: news */
INSERT INTO daportal_module (name, enabled) VALUES ('news', '1');


/* module: comment */
CREATE TABLE daportal_comment (
	comment_id INTEGER UNIQUE NOT NULL,
	parent INTEGER,
	FOREIGN KEY (comment_id) REFERENCES daportal_content (content_id),
	FOREIGN KEY (parent) REFERENCES daportal_content (content_id)
);

INSERT INTO daportal_module (name, enabled) VALUES ('comment', '0');


/* module: top */
CREATE TABLE daportal_top (
	top_id INTEGER PRIMARY KEY,
	name VARCHAR(255),
	link VARCHAR(255)
);

INSERT INTO daportal_module (name, enabled) VALUES ('top', '0');


/* module: project */
CREATE TABLE daportal_project (
	project_id INTEGER UNIQUE NOT NULL,
	synopsis VARCHAR(255) NOT NULL,
	scm VARCHAR(255) NOT NULL DEFAULT 'cvs',
	cvsroot VARCHAR(255) NOT NULL,
	FOREIGN KEY (project_id) REFERENCES daportal_content (content_id)
);

CREATE TABLE daportal_project_download (
	project_download_id INTEGER PRIMARY KEY,
	project_id INTEGER NOT NULL,
	download_id INTEGER NOT NULL,
	FOREIGN KEY (project_id) REFERENCES daportal_project (project_id),
	FOREIGN KEY (download_id) REFERENCES daportal_download (content_id)
);

CREATE TABLE daportal_project_screenshot (
	project_screenshot_id INTEGER PRIMARY KEY,
	project_id INTEGER NOT NULL,
	download_id INTEGER NOT NULL,
	FOREIGN KEY (project_id) REFERENCES daportal_project (project_id),
	FOREIGN KEY (download_id) REFERENCES daportal_download (content_id)
);

CREATE TABLE daportal_project_user (
	project_user_id INTEGER PRIMARY KEY,
	project_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	admin BOOLEAN NOT NULL DEFAULT FALSE,
	FOREIGN KEY (project_id) REFERENCES daportal_project (project_id),
	FOREIGN KEY (user_id) REFERENCES daportal_user (user_id)
);

CREATE TABLE daportal_bug_enum_state (
	name VARCHAR(255)
);
INSERT INTO daportal_bug_enum_state (name) VALUES ('New');
INSERT INTO daportal_bug_enum_state (name) VALUES ('Assigned');
INSERT INTO daportal_bug_enum_state (name) VALUES ('Closed');
INSERT INTO daportal_bug_enum_state (name) VALUES ('Fixed');
INSERT INTO daportal_bug_enum_state (name) VALUES ('Implemented');
INSERT INTO daportal_bug_enum_state (name) VALUES ('Re-opened');

CREATE TABLE daportal_bug_enum_type (
	name VARCHAR(255)
);
INSERT INTO daportal_bug_enum_type (name) VALUES ('Major');
INSERT INTO daportal_bug_enum_type (name) VALUES ('Minor');
INSERT INTO daportal_bug_enum_type (name) VALUES ('Functionality');
INSERT INTO daportal_bug_enum_type (name) VALUES ('Feature');

CREATE TABLE daportal_bug_enum_priority (
	name VARCHAR(255)
);
INSERT INTO daportal_bug_enum_priority (name) VALUES ('Urgent');
INSERT INTO daportal_bug_enum_priority (name) VALUES ('High');
INSERT INTO daportal_bug_enum_priority (name) VALUES ('Medium');
INSERT INTO daportal_bug_enum_priority (name) VALUES ('Low');

CREATE TABLE daportal_bug (
	bug_id INTEGER PRIMARY KEY,
	content_id INTEGER UNIQUE NOT NULL,
	project_id INTEGER,
	state VARCHAR(255) DEFAULT 'New',
	type VARCHAR(255),
	priority VARCHAR(255) DEFAULT 'Medium',
	assigned INTEGER,
	FOREIGN KEY (content_id) REFERENCES daportal_content (content_id),
	FOREIGN KEY (project_id) REFERENCES daportal_project (project_id),
	FOREIGN KEY (state) REFERENCES daportal_bug_enum_state (name),
	FOREIGN KEY (type) REFERENCES daportal_bug_enum_type (name),
	FOREIGN KEY (priority) REFERENCES daportal_bug_enum_type (name)
);

CREATE TABLE daportal_bug_reply_enum_state (
	name VARCHAR(255)
);
INSERT INTO daportal_bug_reply_enum_state (name) VALUES ('New');
INSERT INTO daportal_bug_reply_enum_state (name) VALUES ('Assigned');
INSERT INTO daportal_bug_reply_enum_state (name) VALUES ('Closed');
INSERT INTO daportal_bug_reply_enum_state (name) VALUES ('Fixed');
INSERT INTO daportal_bug_reply_enum_state (name) VALUES ('Implemented');
INSERT INTO daportal_bug_reply_enum_state (name) VALUES ('Re-opened');

CREATE TABLE daportal_bug_reply_enum_type (
	name VARCHAR(255)
);
INSERT INTO daportal_bug_reply_enum_type (name) VALUES ('Major');
INSERT INTO daportal_bug_reply_enum_type (name) VALUES ('Minor');
INSERT INTO daportal_bug_reply_enum_type (name) VALUES ('Functionality');
INSERT INTO daportal_bug_reply_enum_type (name) VALUES ('Feature');

CREATE TABLE daportal_bug_reply_enum_priority (
	name VARCHAR(255)
);
INSERT INTO daportal_bug_reply_enum_priority (name) VALUES ('Urgent');
INSERT INTO daportal_bug_reply_enum_priority (name) VALUES ('High');
INSERT INTO daportal_bug_reply_enum_priority (name) VALUES ('Medium');
INSERT INTO daportal_bug_reply_enum_priority (name) VALUES ('Low');

CREATE TABLE daportal_bug_reply (
	bug_reply_id INTEGER PRIMARY KEY,
	content_id INTEGER UNIQUE NOT NULL,
	bug_id INTEGER NOT NULL,
	state VARCHAR(255),
	type VARCHAR(255),
	priority VARCHAR(255),
	assigned INTEGER,
	FOREIGN KEY (content_id) REFERENCES daportal_content (content_id),
	FOREIGN KEY (bug_id) REFERENCES daportal_bug (bug_id),
	FOREIGN KEY (state) REFERENCES daportal_bug_reply_enum_state (name),
	FOREIGN KEY (type) REFERENCES daportal_bug_reply_enum_type (name),
	FOREIGN KEY (priority) REFERENCES daportal_bug_reply_enum_type (name)
);

INSERT INTO daportal_module (name, enabled) VALUES ('project', '1');


/* module: probe */
CREATE TABLE daportal_probe_host (
	host_id INTEGER UNIQUE NOT NULL,
	FOREIGN KEY (host_id) REFERENCES daportal_content (content_id)
);
INSERT INTO daportal_module (name, enabled) VALUES ('probe', '0');


/* module: bookmark */
CREATE TABLE daportal_bookmark (
	bookmark_id INTEGER UNIQUE NOT NULL,
	url VARCHAR(256),
	FOREIGN KEY (bookmark_id) REFERENCES daportal_content (content_id)
);

INSERT INTO daportal_module (name, enabled) VALUES ('bookmark', '0');


/* module: category */
CREATE TABLE daportal_category_content (
	category_content_id INTEGER PRIMARY KEY,
	category_id INTEGER NOT NULL,
	content_id INTEGER NOT NULL,
	FOREIGN KEY (category_id) REFERENCES daportal_content (content_id),
	FOREIGN KEY (content_id) REFERENCES daportal_content (content_id)
);

INSERT INTO daportal_module (name, enabled) VALUES ('category', '0');


/* module: download */
CREATE TABLE daportal_download (
	download_id INTEGER PRIMARY KEY,
	content_id INTEGER UNIQUE NOT NULL,
	parent INTEGER,
	mode SMALLINT DEFAULT '420',
	FOREIGN KEY (content_id) REFERENCES daportal_content (content_id),
	FOREIGN KEY (parent) REFERENCES daportal_download (download_id)
);

INSERT INTO daportal_module (name, enabled) VALUES ('download', '1');


/* module: article */
INSERT INTO daportal_module (name, enabled) VALUES ('article', '1');


/* module: wiki */
INSERT INTO daportal_module (name, enabled) VALUES ('wiki', '1');


/* module: webmail */
INSERT INTO daportal_module (name, enabled) VALUES ('webmail', '0');


/* module: pki */
CREATE TABLE daportal_ca (
	ca_id INTEGER UNIQUE NOT NULL,
	parent INTEGER DEFAULT NULL,
	country CHAR(2),
	state VARCHAR(255),
	locality VARCHAR(255),
	organization VARCHAR(255),
	section VARCHAR(255),
	email VARCHAR(255),
	signed BOOLEAN DEFAULT FALSE,
	FOREIGN KEY (ca_id) REFERENCES daportal_content (content_id),
	FOREIGN KEY (parent) REFERENCES daportal_ca (ca_id)
);

CREATE TABLE daportal_caclient (
	caclient_id INTEGER UNIQUE NOT NULL,
	parent INTEGER,
	country CHAR(2),
	state VARCHAR(255),
	locality VARCHAR(255),
	organization VARCHAR(255),
	section VARCHAR(255),
	email VARCHAR(255),
	signed BOOLEAN DEFAULT FALSE,
	FOREIGN KEY (caclient_id) REFERENCES daportal_content (content_id),
	FOREIGN KEY (parent) REFERENCES daportal_ca (ca_id)
);

CREATE TABLE daportal_caserver (
	caserver_id INTEGER UNIQUE NOT NULL,
	parent INTEGER,
	country CHAR(2),
	state VARCHAR(255),
	locality VARCHAR(255),
	organization VARCHAR(255),
	section VARCHAR(255),
	email VARCHAR(255),
	signed BOOLEAN DEFAULT FALSE,
	FOREIGN KEY (caserver_id) REFERENCES daportal_content (content_id),
	FOREIGN KEY (parent) REFERENCES daportal_ca (ca_id)
);

INSERT INTO daportal_module (name, enabled) VALUES ('pki', '1');


/* module: browser */
INSERT INTO daportal_module (name, enabled) VALUES ('browser', '1');


/* module: translate */
INSERT INTO daportal_module (name, enabled) VALUES ('translate', '0');


/* module: blog */
CREATE TABLE daportal_blog_user (
	blog_user_id INTEGER UNIQUE NOT NULL,
	theme VARCHAR(255),
	FOREIGN KEY (blog_user_id) REFERENCES daportal_user (user_id)
);

CREATE TABLE daportal_blog_content (
	blog_content_id INTEGER UNIQUE NOT NULL,
	comment BOOLEAN DEFAULT FALSE,
	FOREIGN KEY (blog_content_id) REFERENCES daportal_content (content_id)
);

INSERT INTO daportal_module (name, enabled) VALUES ('blog', '1');


/* module: manual */
INSERT INTO daportal_module (name, enabled) VALUES ('manual', '1');


/* module: salt */
INSERT INTO daportal_module (name, enabled) VALUES ('salt', '1');

COMMIT;
