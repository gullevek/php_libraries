-- $Id: trg_edit_page_access.sql 3158 2010-09-02 02:49:00Z gullevek $

-- DROP TRIGGER trg_edit_page_access ON edit_page_access;
CREATE TRIGGER trg_edit_page_access
BEFORE INSERT OR UPDATE ON edit_page_access
FOR EACH ROW EXECUTE PROCEDURE set_generic();
