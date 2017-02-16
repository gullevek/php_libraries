-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- cms tables; generic basic table
-- TABLE: generic
-- HISTORY:

-- DROP TABLE edit_generic;
CREATE TABLE generic (
	date_created	TIMESTAMP WITHOUT TIME ZONE DEFAULT clock_timestamp(),
	date_updated	TIMESTAMP WITHOUT TIME ZONE
);
