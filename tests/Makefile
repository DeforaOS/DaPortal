TARGETS	= phplint.log shlint.log sqlite.db sqlite.db3 tests.log
PREFIX	= /usr/local
DESTDIR	=
RM	= rm -f
LN	= ln -f
MKDIR	= mkdir -m 0755 -p
INSTALL	= install


all: $(TARGETS)

phplint.log: phplint.sh
	./phplint.sh -P "$(PREFIX)" -- "phplint.log"

shlint.log: shlint.sh
	./shlint.sh -P "$(PREFIX)" -- "shlint.log"

sqlite.db: ../doc/sql/sqlite.sql database.sh
	./database.sh -P "$(PREFIX)" -- "sqlite.db"

sqlite.db3: ../doc/sql/sqlite.sql database.sh
	./database.sh -P "$(PREFIX)" -- "sqlite.db3"

tests.log: auth.php config.php coverage.php daportal.conf database.php mail.php module.php sqlite.db3 tests.php tests.sh user.php
	./tests.sh -P "$(PREFIX)" -- "tests.log"

clean:
	$(RM) -- $(phplint.log_OBJS) $(shlint.log_OBJS) $(sqlite.db_OBJS) $(sqlite.db3_OBJS) $(tests.log_OBJS)
	./phplint.sh -c -P "$(PREFIX)" -- "phplint.log"
	./shlint.sh -c -P "$(PREFIX)" -- "shlint.log"
	./database.sh -c -P "$(PREFIX)" -- "sqlite.db"
	./database.sh -c -P "$(PREFIX)" -- "sqlite.db3"
	./tests.sh -c -P "$(PREFIX)" -- "tests.log"

distclean: clean
	$(RM) -- $(TARGETS)

install: $(TARGETS)

uninstall:

.PHONY: all clean distclean install uninstall
