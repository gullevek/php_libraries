-- $Id: trg_edit_page.sql 3158 2010-09-02 02:49:00Z gullevek $

-- DROP TRIGGER trg_edit_page ON edit_page;
CREATE TRIGGER trg_edit_page
BEFORE INSERT OR UPDATE ON edit_page
FOR EACH ROW EXECUTE PROCEDURE set_generic();
