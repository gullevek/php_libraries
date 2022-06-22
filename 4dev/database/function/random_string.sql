-- create random string with length X

CREATE FUNCTION random_string(randomLength int)
RETURNS text AS
$$
SELECT array_to_string(
	ARRAY(
		SELECT substring(
			'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
			trunc(random() * 62)::int + 1,
			1
		)
		FROM generate_series(1, randomLength) AS gs(x)
	),
	''
)
$$
LANGUAGE SQL
RETURNS NULL ON NULL INPUT
VOLATILE; -- LEAKPROOF;
