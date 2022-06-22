-- DROP TRIGGER IF EXISTS trg_edit_user ON edit_user;
CREATE TRIGGER trg_edit_user
BEFORE INSERT OR UPDATE ON edit_user
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();

-- DROP TRIGGER IF EXISTS trg_edit_user_set_login_user_id_set_date ON edit_user;
CREATE TRIGGER trg_edit_user_set_login_user_id_set_date
BEFORE INSERT OR UPDATE ON edit_user
FOR EACH ROW EXECUTE PROCEDURE set_login_user_id_set_date();
