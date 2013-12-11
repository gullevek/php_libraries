-- $Id: trg_edit_scheme.sql 3158 2010-09-02 02:49:00Z gullevek $

-- DROP TRIGGER trg_edit_scheme ON edit_scheme;
CREATE TRIGGER trg_edit_scheme
BEFORE INSERT OR UPDATE ON edit_scheme
FOR EACH ROW EXECUTE PROCEDURE set_generic();
