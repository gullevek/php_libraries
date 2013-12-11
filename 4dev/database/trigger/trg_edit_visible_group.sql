-- $Id: trg_edit_visible_group.sql 3158 2010-09-02 02:49:00Z gullevek $

-- DROP TRIGGER trg_edit_visible_group ON edit_visible_group;
CREATE TRIGGER trg_edit_visible_group
BEFORE INSERT OR UPDATE ON edit_visible_group
FOR EACH ROW EXECUTE PROCEDURE set_generic();
