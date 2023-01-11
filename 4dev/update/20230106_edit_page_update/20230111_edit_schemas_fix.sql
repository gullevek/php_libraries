-- Fix for edit_schemes.php DB settings

-- will not change file name only visual name
UPDATE edit_page SET name = 'Edit Schemas' WHERE filename = 'edit_schemes.php';

-- will change BOTH, must have file name renamed too
UPDATE edit_page SET name = 'Edit Schemas', filename = 'edit_schemas.php' WHERE filename = 'edit_schemes.php';
