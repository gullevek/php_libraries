--
SELECT
eu.cuid, eu.username,
eu.lock_until, eu.lock_after,
CASE WHEN (
(eu.lock_until IS NULL
OR (eu.lock_until IS NOT NULL AND NOW() >= eu.lock_until))
AND (eu.lock_after IS NULL
OR (eu.lock_after IS NOT NULL AND NOW() <= eu.lock_after))
) THEN 0::INT ELSE 1::INT END locked_period
FROM edit_user eu
WHERE eu.username = 'empty';

UPDATE edit_user SET
lock_until = NOW() + '1 day'::interval
WHERE username = 'empty';
UPDATE edit_user SET
lock_after = NOW() - '1 day'::interval
WHERE username = 'empty';


UPDATE edit_user SET
lock_until = NOW() - '1 day'::interval
WHERE username = 'empty';
UPDATE edit_user SET
lock_after = NOW() + '1 day'::interval
WHERE username = 'empty';

UPDATE edit_user SET lock_until = NULL, lock_after = NULL WHERE username = 'empty';

--
SELECT
eu.cuid, eu.username,
eu.login_user_id, login_user_id_set_date, eu.login_user_id_last_revalidate,
(eu.login_user_id_last_revalidate + eu.login_user_id_revalidate_after)::DATE AS reval_date, NOW()::DATE,
eu.login_user_id_valid_from, eu.login_user_id_valid_until,
eu.login_user_id_revalidate_after,
CASE WHEN (
(eu.login_user_id_valid_from IS NULL
OR (eu.login_user_id_valid_from IS NOT NULL AND NOW() >= eu.login_user_id_valid_from))
AND (eu.login_user_id_valid_until IS NULL
OR (eu.login_user_id_valid_until IS NOT NULL AND NOW() <= eu.login_user_id_valid_until))
) THEN 1::INT ELSE 0::INT END AS login_user_id_valid_date,
CASE WHEN eu.login_user_id_revalidate_after IS NOT NULL
AND eu.login_user_id_revalidate_after > '0 days'::INTERVAL
AND (eu.login_user_id_last_revalidate + eu.login_user_id_revalidate_after)::DATE <= NOW()::DATE
THEN 1::INT ELSE 0::INT END AS login_user_id_revalidate
FROM edit_user eu
WHERE eu.username = 'empty';

-- init
UPDATE edit_user SET login_user_id = random_string(5) WHERE username = 'empty';

-- outside valid
UPDATE edit_user SET
login_user_id_valid_from = NOW() - '1 day'::interval
WHERE username = 'empty';
UPDATE edit_user SET
login_user_id_valid_until = NOW() + '1 day'::interval
WHERE username = 'empty';
-- inside valid
UPDATE edit_user SET
login_user_id_valid_from = NOW() + '1 day'::interval
WHERE username = 'empty';
UPDATE edit_user SET
login_user_id_valid_until = NOW() - '1 day'::interval
WHERE username = 'empty';

-- revalidate must
UPDATE edit_user SET
login_user_id_last_revalidate = NOW() - '1 day'::interval,
login_user_id_revalidate_after = '1 day'::interval
WHERE username = 'empty';
-- revalidate not yet
UPDATE edit_user SET
login_user_id_last_revalidate = NOW(),
login_user_id_revalidate_after = '6 day'::interval
WHERE username = 'empty';


UPDATE edit_user SET login_user_id_set_date = NULL, login_user_id_last_revalidate = NULL, login_user_id_valid_from = NULL, login_user_id_valid_until = NULL, login_user_id_revalidate_after = NULL WHERE username = 'empty';
