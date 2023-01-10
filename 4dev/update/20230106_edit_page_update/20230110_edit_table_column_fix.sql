-- Fixes for column types

-- edit group
ALTER TABLE edit_group ALTER name TYPE VARCHAR;
-- edit language
ALTER TABLE edit_language ALTER short_name TYPE VARCHAR;
ALTER TABLE edit_language ALTER long_name TYPE VARCHAR;
ALTER TABLE edit_language ALTER iso_name TYPE VARCHAR;
-- edit menu group
ALTER TABLE edit_menu_group ALTER name TYPE VARCHAR;
ALTER TABLE edit_menu_group ALTER flag TYPE VARCHAR;
-- edit page
ALTER TABLE edit_page ALTER filename TYPE VARCHAR;
ALTER TABLE edit_page ALTER name TYPE VARCHAR;
-- edit query string
ALTER TABLE edit_query_string ALTER name TYPE VARCHAR;
ALTER TABLE edit_query_string ALTER value TYPE VARCHAR;
-- edit scheme
ALTER TABLE edit_scheme ALTER name TYPE VARCHAR;
ALTER TABLE edit_scheme ALTER header_color TYPE VARCHAR;
ALTER TABLE edit_scheme ALTER css_file TYPE VARCHAR;
ALTER TABLE edit_scheme ALTER template TYPE VARCHAR;
-- edit visible group
ALTER TABLE edit_visible_group ALTER name TYPE VARCHAR;
ALTER TABLE edit_visible_group ALTER flag TYPE VARCHAR;
