-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- groups pages together to one page group to which the user is then subscribed
-- TABLE: edit_page_access
-- HISTORY:

-- DROP TABLE edit_page_access;
CREATE TABLE edit_page_access (
	edit_page_access_id	SERIAL PRIMARY KEY,
	enabled	SMALLINT NOT NULL DEFAULT 0,
	edit_group_id	INT NOT NULL,
	edit_page_id	INT NOT NULL,
	edit_access_right_id	INT NOT NULL,
	FOREIGN KEY (edit_group_id) REFERENCES edit_group (edit_group_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE
) INHERITS (edit_generic) WITHOUT OIDS;

INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
	(SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
	1,
	(SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
	(SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
	2,
	(SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
	);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
	(SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
	3,
	(SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
	(SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
	4,
	(SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
	(SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
	5,
	(SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
	(SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
	6,
	(SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin'
		);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
	(SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
	7,
	(SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin'
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
	(SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
	8,
	(SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
	(SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
	9,
	(SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
