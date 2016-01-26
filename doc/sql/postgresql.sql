/* $Id$ */
/* This file is part of DeforaOS Web DaPortal */
/* Copyright (c) 2011-2015 Pierre Pronchery <khorben@defora.org> */



BEGIN TRANSACTION;


CREATE TABLE daportal_module (
	module_id SERIAL PRIMARY KEY,
	name VARCHAR(255) UNIQUE NOT NULL,
	enabled BOOLEAN NOT NULL DEFAULT false
);

INSERT INTO daportal_module (name, enabled) VALUES ('admin', '1');
INSERT INTO daportal_module (name, enabled) VALUES ('search', '1');

CREATE VIEW daportal_module_enabled AS
SELECT module_id, name, enabled
FROM daportal_module
WHERE enabled='1';


CREATE TABLE daportal_config (
	config_id SERIAL PRIMARY KEY,
	module_id INTEGER NOT NULL REFERENCES daportal_module (module_id) ON DELETE CASCADE,
	title VARCHAR(255),
	type VARCHAR(255) CHECK (type IN ('bool', 'int', 'string')) NOT NULL,
	name VARCHAR(255) NOT NULL,
	value_bool BOOLEAN DEFAULT NULL,
	value_int INTEGER DEFAULT NULL,
	value_string VARCHAR(255) DEFAULT NULL
);


CREATE TABLE daportal_profile (
	profile_id SERIAL PRIMARY KEY,
	timestamp TIMESTAMP NOT NULL DEFAULT now(),
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
	sql_profile_id SERIAL PRIMARY KEY,
	timestamp TIMESTAMP NOT NULL DEFAULT now(),
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
	group_id SERIAL PRIMARY KEY,
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
	user_id SERIAL PRIMARY KEY,
	username VARCHAR(255) NOT NULL UNIQUE,
	group_id INTEGER NOT NULL DEFAULT 0 REFERENCES daportal_group (group_id) ON DELETE RESTRICT,
	"password" VARCHAR(255),
	enabled BOOLEAN NOT NULL DEFAULT FALSE,
	admin BOOLEAN NOT NULL DEFAULT FALSE,
	fullname VARCHAR(255) NOT NULL DEFAULT '',
	email VARCHAR(255) NOT NULL
);

CREATE INDEX daportal_user_username_index ON daportal_user (username) WHERE enabled='1';

INSERT INTO daportal_module (name, enabled) VALUES ('user', '1');
INSERT INTO daportal_user (user_id, username, password, enabled, email) VALUES ('0', 'Anonymous', '!', '1', '');
INSERT INTO daportal_user (username, password, enabled, admin, email) VALUES ('admin', '$1$?0p*PI[G$kbHyE5VE/S32UrV88Unz/1', '1', '1', 'username@domain.tld');

CREATE VIEW daportal_user_enabled AS
SELECT user_id, username, group_id, password, enabled, admin, fullname, email
FROM daportal_user
WHERE enabled='1';

CREATE TABLE daportal_user_register (
	user_register_id SERIAL PRIMARY KEY,
	user_id INTEGER UNIQUE NOT NULL REFERENCES daportal_user (user_id) ON DELETE CASCADE,
	token VARCHAR(255) UNIQUE NOT NULL,
	"timestamp" TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX daportal_user_register_token_index ON daportal_user_register (token);


CREATE TABLE daportal_user_reset (
	user_reset_id SERIAL PRIMARY KEY,
	user_id INTEGER UNIQUE NOT NULL REFERENCES daportal_user (user_id) ON DELETE CASCADE,
	token VARCHAR(255) UNIQUE NOT NULL,
	"timestamp" TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX daportal_user_reset_token_index ON daportal_user_reset (token);


CREATE TABLE daportal_user_group (
	user_group_id SERIAL PRIMARY KEY,
	user_id INTEGER NOT NULL REFERENCES daportal_user (user_id),
	group_id INTEGER NOT NULL REFERENCES daportal_group (group_id),
	UNIQUE (user_id, group_id)
);


CREATE TABLE daportal_auth_variable (
	auth_variable_id SERIAL PRIMARY KEY,
	user_id INTEGER NOT NULL REFERENCES daportal_user (user_id) ON DELETE CASCADE,
	variable VARCHAR(255) NOT NULL,
	value VARCHAR(255) NOT NULL,
	UNIQUE (user_id, variable)
);


CREATE TABLE daportal_content (
	content_id SERIAL PRIMARY KEY,
	timestamp TIMESTAMP NOT NULL DEFAULT now(),
	module_id INTEGER NOT NULL REFERENCES daportal_module (module_id) ON DELETE RESTRICT,
	user_id INTEGER NOT NULL REFERENCES daportal_user (user_id) ON DELETE RESTRICT,
	group_id INTEGER NOT NULL DEFAULT 0 REFERENCES daportal_group (group_id) ON DELETE RESTRICT,
	title VARCHAR(255) NOT NULL,
	content TEXT NOT NULL,
	enabled BOOLEAN NOT NULL DEFAULT FALSE,
	public BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE INDEX daportal_content_timestamp_desc_index ON daportal_content (timestamp DESC);
CREATE INDEX daportal_content_module_id_index ON daportal_content (module_id);
CREATE INDEX daportal_content_user_id_index ON daportal_content (user_id);
CREATE INDEX daportal_content_title_index ON daportal_content (title) WHERE enabled='1' AND public='1';
CREATE INDEX daportal_content_title_lower_index ON daportal_content (LOWER(title)) WHERE enabled='1' AND public='1';
CREATE INDEX daportal_content_content_index ON daportal_content (content) WHERE enabled='1' AND public='1';
CREATE INDEX daportal_content_content_lower_index ON daportal_content (LOWER(content)) WHERE enabled='1' AND public='1';

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
	content_lang_id SERIAL PRIMARY KEY,
	content_id INTEGER REFERENCES daportal_content (content_id),
	lang_id VARCHAR(2) REFERENCES daportal_lang (lang_id),
	title VARCHAR(255),
	content TEXT
);


/* module: news */
INSERT INTO daportal_module (name, enabled) VALUES ('news', '1');


/* module: comment */
CREATE TABLE daportal_comment (
	comment_id INTEGER UNIQUE NOT NULL REFERENCES daportal_content (content_id) ON DELETE CASCADE,
	parent INTEGER NOT NULL REFERENCES daportal_content (content_id) ON DELETE CASCADE
);

INSERT INTO daportal_module (name, enabled) VALUES ('comment', '0');


/* module: top */
CREATE TABLE daportal_top (
	top_id SERIAL PRIMARY KEY,
	name VARCHAR(255),
	link VARCHAR(255)
);

INSERT INTO daportal_module (name, enabled) VALUES ('top', '0');


/* module: project */
CREATE TABLE daportal_project (
	project_id INTEGER UNIQUE NOT NULL REFERENCES daportal_content (content_id) ON DELETE CASCADE,
	synopsis VARCHAR(255) NOT NULL,
	scm VARCHAR(255) NOT NULL DEFAULT 'cvs',
	cvsroot VARCHAR(255) NOT NULL
);

CREATE TABLE daportal_project_download (
	project_download_id SERIAL PRIMARY KEY,
	project_id INTEGER NOT NULL REFERENCES daportal_project (project_id),
	download_id INTEGER NOT NULL REFERENCES daportal_content (content_id)
);

CREATE TABLE daportal_project_screenshot (
	project_screenshot_id SERIAL PRIMARY KEY,
	project_id INTEGER NOT NULL REFERENCES daportal_project (project_id),
	download_id INTEGER NOT NULL REFERENCES daportal_content (content_id)
);

CREATE TABLE daportal_project_user (
	project_user_id SERIAL PRIMARY KEY,
	project_id INTEGER NOT NULL REFERENCES daportal_project (project_id) ON DELETE CASCADE,
	user_id INTEGER NOT NULL REFERENCES daportal_user (user_id) ON DELETE CASCADE,
	admin BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE daportal_bug (
	bug_id SERIAL PRIMARY KEY,
	content_id INTEGER UNIQUE NOT NULL REFERENCES daportal_content (content_id) ON DELETE CASCADE,
	project_id INTEGER REFERENCES daportal_project (project_id) ON DELETE CASCADE,
	state varchar(11) CHECK (state IN ('New', 'Assigned', 'Closed', 'Fixed', 'Implemented', 'Re-opened')) NOT NULL DEFAULT 'New',
	type varchar(13) CHECK (type IN ('Major', 'Minor', 'Functionality', 'Feature')) NOT NULL,
	priority VARCHAR(6) CHECK (priority IN ('Urgent', 'High', 'Medium', 'Low')) NOT NULL DEFAULT 'Medium',
	assigned INTEGER DEFAULT NULL REFERENCES daportal_user (user_id) ON DELETE SET NULL
);
CREATE TABLE daportal_bug_reply (
	bug_reply_id SERIAL PRIMARY KEY,
	content_id INTEGER UNIQUE NOT NULL REFERENCES daportal_content (content_id) ON DELETE CASCADE,
	bug_id INTEGER NOT NULL REFERENCES daportal_bug (bug_id) ON DELETE CASCADE,
	state varchar(11) CHECK (state IN ('New', 'Assigned', 'Closed', 'Fixed', 'Implemented', 'Re-opened')),
	type varchar(13) CHECK (type IN ('Major', 'Minor', 'Functionality', 'Feature')),
	priority VARCHAR(6) CHECK (priority IN ('Urgent', 'High', 'Medium', 'Low')),
	assigned INTEGER DEFAULT NULL REFERENCES daportal_user (user_id) ON DELETE SET NULL
);

INSERT INTO daportal_module (name, enabled) VALUES ('project', '1');


/* module: probe */
CREATE TABLE daportal_probe_host (
	host_id INTEGER NOT NULL UNIQUE REFERENCES daportal_content (content_id) ON DELETE CASCADE
);
INSERT INTO daportal_module (name, enabled) VALUES ('probe', '0');


/* module: bookmark */
CREATE TABLE daportal_bookmark (
	bookmark_id INTEGER NOT NULL UNIQUE REFERENCES daportal_content (content_id) ON DELETE CASCADE,
	url VARCHAR(256)
);

INSERT INTO daportal_module (name, enabled) VALUES ('bookmark', '0');


/* module: category */
CREATE TABLE daportal_category_content (
	category_content_id SERIAL PRIMARY KEY,
	category_id INTEGER NOT NULL REFERENCES daportal_content (content_id) ON DELETE CASCADE,
	content_id INTEGER NOT NULL REFERENCES daportal_content (content_id) ON DELETE CASCADE
);

INSERT INTO daportal_module (name, enabled) VALUES ('category', '0');


/* module: download */
CREATE TABLE daportal_download (
	download_id SERIAL PRIMARY KEY,
	content_id INTEGER NOT NULL REFERENCES daportal_content (content_id) ON DELETE RESTRICT, -- not UNIQUE allows hard links
	parent INTEGER REFERENCES daportal_download (download_id) ON DELETE RESTRICT,
	mode SMALLINT DEFAULT '420'
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
	ca_id INTEGER NOT NULL UNIQUE REFERENCES daportal_content (content_id) ON DELETE CASCADE,
	parent INTEGER DEFAULT NULL REFERENCES daportal_ca (ca_id),
	country CHAR(2),
	state VARCHAR(255),
	locality VARCHAR(255),
	organization VARCHAR(255),
	section VARCHAR(255),
	email VARCHAR(255),
	signed BOOLEAN DEFAULT FALSE
);

CREATE TABLE daportal_caclient (
	caclient_id INTEGER NOT NULL UNIQUE REFERENCES daportal_content (content_id) ON DELETE CASCADE,
	parent INTEGER DEFAULT NULL REFERENCES daportal_ca (ca_id),
	country CHAR(2),
	state VARCHAR(255),
	locality VARCHAR(255),
	organization VARCHAR(255),
	section VARCHAR(255),
	email VARCHAR(255),
	signed BOOLEAN DEFAULT FALSE
);

CREATE TABLE daportal_caserver (
	caserver_id INTEGER NOT NULL UNIQUE REFERENCES daportal_content (content_id) ON DELETE CASCADE,
	parent INTEGER DEFAULT NULL REFERENCES daportal_ca (ca_id),
	country CHAR(2),
	state VARCHAR(255),
	locality VARCHAR(255),
	organization VARCHAR(255),
	section VARCHAR(255),
	email VARCHAR(255),
	signed BOOLEAN DEFAULT FALSE
);

INSERT INTO daportal_module (name, enabled) VALUES ('pki', '1');


/* module: browser */
INSERT INTO daportal_module (name, enabled) VALUES ('browser', '1');


/* module: translate */
INSERT INTO daportal_module (name, enabled) VALUES ('translate', '0');


/* module: blog */
CREATE TABLE daportal_blog_user (
	blog_user_id INTEGER UNIQUE NOT NULL REFERENCES daportal_user (user_id),
	theme VARCHAR(255)
);

CREATE TABLE daportal_blog_content (
	blog_content_id INTEGER UNIQUE NOT NULL REFERENCES daportal_content (content_id),
	comment BOOLEAN DEFAULT FALSE
);

INSERT INTO daportal_module (name, enabled) VALUES ('blog', '1');


/* module: manual */
INSERT INTO daportal_module (name, enabled) VALUES ('manual', '1');


/* module: salt */
INSERT INTO daportal_module (name, enabled) VALUES ('salt', '1');


/* module: donate */
INSERT INTO daportal_module (name, enabled) VALUES ('donate', '1');

COMMIT;
