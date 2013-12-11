-- $Id: trg_edit_user.sql 3158 2010-09-02 02:49:00Z gullevek $

-- DROP TRIGGER trg_edit_user ON edit_user;
CREATE TRIGGER trg_edit_user
BEFORE INSERT OR UPDATE ON edit_user
FOR EACH ROW EXECUTE PROCEDURE set_generic();
