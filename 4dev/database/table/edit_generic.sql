-- $Id: edit_generic.sql 3158 2010-09-02 02:49:00Z gullevek $
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables, this is the generic table, inheriteded by most edit tables
-- TABLE: edit_generic
-- HISTORY:

-- DROP TABLE edit_generic;
CREATE TABLE edit_generic (
	eg_status	INT,
	date_created	TIMESTAMP WITHOUT TIME ZONE DEFAULT clock_timestamp(),
	date_updated	TIMESTAMP WITHOUT TIME ZONE,
	user_created	VARCHAR(25) DEFAULT CURRENT_USER,
	user_updated	VARCHAR(25)
);
