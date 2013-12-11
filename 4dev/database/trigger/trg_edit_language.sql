-- $Id: trg_edit_language.sql 3158 2010-09-02 02:49:00Z gullevek $

-- DROP TRIGGER trg_edit_language ON edit_language;
CREATE TRIGGER trg_edit_language
BEFORE INSERT OR UPDATE ON edit_language
FOR EACH ROW EXECUTE PROCEDURE set_generic();
