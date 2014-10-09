/* $Id$ */
/* This file is part of DeforaOS Web DaPortal */
/* Copyright (c) 2014 Pierre Pronchery <khorben@defora.org> */



/* user */
INSERT INTO daportal_user (username, password, enabled, fullname, email) VALUES ('user', '$1$?0p*PI[G$kbHyE5VE/S32UrV88Unz/1', '1', 'Joe User', 'user@domain.tld');


/* news */
INSERT INTO daportal_content (module_id, user_id, title, content, enabled, public) VALUES ('5', '2', 'Sample news', 'Some content for the sample news', '1', '1');
INSERT INTO daportal_content (module_id, user_id, title, content, enabled, public) VALUES ('5', '2', 'Sample private news', 'Some content for the sample private news', '1', '0');


/* project */
INSERT INTO daportal_content (module_id, user_id, title, content, enabled, public) VALUES ('8', '2', 'Sample project', 'Description for the project', '1', '1');
INSERT INTO daportal_project (project_id, synopsis, scm, cvsroot) VALUES ('3', 'Synopsis for the project', '', '');
