TARGETS	= $(OBJDIR)phplint.log $(OBJDIR)shlint.log $(OBJDIR)sqlite.db $(OBJDIR)sqlite.db3 $(OBJDIR)tests.log
OBJDIR	=
PREFIX	= /usr/local
DESTDIR	=
RM	= rm -f
LN	= ln -f
MKDIR	= mkdir -m 0755 -p
INSTALL	= install


all: $(TARGETS)

$(OBJDIR)phplint.log: phplint.sh $(OBJDIR)../src/daportal.php
	./phplint.sh -P "$(PREFIX)" -- "$(OBJDIR)phplint.log"

$(OBJDIR)shlint.log: shlint.sh
	./shlint.sh -P "$(PREFIX)" -- "$(OBJDIR)shlint.log"

$(OBJDIR)sqlite.db: ../doc/sql/sqlite.sql database.sh
	./database.sh -P "$(PREFIX)" -- "$(OBJDIR)sqlite.db"

$(OBJDIR)sqlite.db3: ../doc/sql/sqlite.sql database.sh
	./database.sh -P "$(PREFIX)" -- "$(OBJDIR)sqlite.db3"

$(OBJDIR)tests.log: auth.php config.php coverage.php daportal.conf daportal.php database.php engine.php format.php group.php html.php http.php json.php mail.php module.php observer.php pki.php profile.php search.php $(OBJDIR)sqlite.db3 template.php tests.php tests.sh user.php
	./tests.sh -P "$(PREFIX)" -- "$(OBJDIR)tests.log"

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
