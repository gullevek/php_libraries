-- DROP TRIGGER IF EXISTS trg_edit_group ON edit_group;
CREATE TRIGGER trg_edit_group
BEFORE INSERT OR UPDATE ON edit_group
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();

-- DROP TRIGGER IF EXISTS trg_set_edit_group_uid ON edit_group;
CREATE TRIGGER trg_set_edit_group_uid
BEFORE INSERT OR UPDATE ON edit_group
FOR EACH ROW EXECUTE PROCEDURE set_edit_group_uid();
