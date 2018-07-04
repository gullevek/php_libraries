DROP TRIGGER trg_edit_menu_group ON edit_menu_group;
CREATE TRIGGER trg_edit_menu_group
BEFORE INSERT OR UPDATE ON edit_menu_group
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
