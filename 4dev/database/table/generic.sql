-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- cms tables; generic basic table
-- TABLE: generic
-- HISTORY:

-- DROP TABLE edit_generic;
CREATE TABLE generic (
	row_status	INT,
	date_created	TIMESTAMP WITHOUT TIME ZONE DEFAULT clock_timestamp(),
	date_updated	TIMESTAMP WITHOUT TIME ZONE,
	user_created	VARCHAR(25) DEFAULT CURRENT_USER,
	user_updated	VARCHAR(25)
);
