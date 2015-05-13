#!/usr/bin/sh
#
# run as (postgresql) superuser

psql -e <<EOF
-- createuser -DRS pharext
CREATE
	USER	"pharext"
	NOCREATEDB
	NOREPLICATION
	NOSUPERUSER
;

-- createdb pharext -O pharext -E UTF8 -l en_US.UTF-8
CREATE 
	DATABASE	"pharext"
	OWNER		"pharext"
	TEMPLATE	"template0"
	ENCODING	'UTF-8'
	LC_CTYPE	'en_US.UTF-8'
	LC_COLLATE	'en_US.UTF-8'
;

\c pharext

CREATE
	EXTENSION	"uuid-ossp"
;

EOF
