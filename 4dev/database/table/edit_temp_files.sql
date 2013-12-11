-- $Id: edit_temp_files.sql 4382 2013-02-18 07:27:24Z gullevek $
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/08
-- DESCRIPTION:
-- edit interface temporary files, list of all files in edit (admin) directory
-- TABLE: temp_files
-- HISTORY:

-- DROP TABLE temp_files;
CREATE TABLE temp_files (
	filename	VARCHAR(250)
);
