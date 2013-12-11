-- $Id: trg_edit_group.sql 3158 2010-09-02 02:49:00Z gullevek $

-- DROP TRIGGER trg_edit_group ON edit_group;
CREATE TRIGGER trg_edit_group
BEFORE INSERT OR UPDATE ON edit_group
FOR EACH ROW EXECUTE PROCEDURE set_generic();
