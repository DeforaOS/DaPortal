TARGETS	= phplint.log sqlite.db sqlite.db3 tests.log
PREFIX	= /usr/local
DESTDIR	=
RM	= rm -f
LN	= ln -f
MKDIR	= mkdir -m 0755 -p
INSTALL	= install


all: $(TARGETS)

phplint.log: phplint.sh
	./phplint.sh -P "$(PREFIX)" -- "phplint.log"

sqlite.db: ../doc/sql/sqlite.sql database.sh
	./database.sh -P "$(PREFIX)" -- "sqlite.db"

sqlite.db3: ../doc/sql/sqlite.sql database.sh
	./database.sh -P "$(PREFIX)" -- "sqlite.db3"

tests.log: auth.php coverage.php daportal.conf module.php sqlite.db3 tests.php tests.sh user.php
	./tests.sh -P "$(PREFIX)" -- "tests.log"

clean:
	$(RM) -- $(phplint.log_OBJS) $(sqlite.db_OBJS) $(sqlite.db3_OBJS) $(tests.log_OBJS)
	./tests.sh -c -P "$(PREFIX)" -- "tests.log"

distclean: clean
	$(RM) -- $(TARGETS)

install: $(TARGETS)

uninstall:

.PHONY: all clean distclean install uninstall
