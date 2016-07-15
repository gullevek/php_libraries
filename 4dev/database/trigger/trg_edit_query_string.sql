-- DROP TRIGGER trg_edit_query_string ON edit_query_string;
CREATE TRIGGER trg_edit_query_string
BEFORE INSERT OR UPDATE ON edit_query_string
FOR EACH ROW EXECUTE PROCEDURE set_generic();
