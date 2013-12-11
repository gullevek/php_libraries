-- $Id: generic.sql 3158 2010-09-02 02:49:00Z gullevek $
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- cms tables; generic basic table
-- TABLE: generic
-- HISTORY:

-- DROP TABLE edit_generic;
CREATE TABLE generic (
	row_status	INT,
	date_created	TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
	date_updated	TIMESTAMP WITHOUT TIME ZONE,
	user_created	VARCHAR(25) DEFAULT CURRENT_USER,
	user_updated	VARCHAR(25)
);
