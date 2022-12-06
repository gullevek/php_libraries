-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- generic basic table with date and uid column
-- TABLE: generic
-- HISTORY:

-- DROP TABLE generic;
CREATE TABLE generic (
    date_created TIMESTAMP WITHOUT TIME ZONE DEFAULT clock_timestamp(),
    date_updated TIMESTAMP WITHOUT TIME ZONE,
    uid VARCHAR
);
