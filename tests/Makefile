TARGETS	= phplint.log sqlite.db sqlite.db3
PREFIX	= /usr/local
DESTDIR	=
RM	= rm -f
LN	= ln -f
MKDIR	= mkdir -p
INSTALL	= install


all: $(TARGETS)

phplint.log: phplint.sh
	./phplint.sh -P "$(PREFIX)" -- "phplint.log"

sqlite.db: ../doc/sql/sqlite.sql database.sh
	./database.sh -P "$(PREFIX)" -- "sqlite.db"

sqlite.db3: ../doc/sql/sqlite.sql database.sh
	./database.sh -P "$(PREFIX)" -- "sqlite.db3"

clean:
	$(RM) -- $(phplint.log_OBJS) $(sqlite.db_OBJS) $(sqlite.db3_OBJS)

distclean: clean
	$(RM) -- $(TARGETS)

install: $(TARGETS)

uninstall:

.PHONY: all clean distclean install uninstall
