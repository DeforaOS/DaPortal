TARGETS	= $(OBJDIR)phpinfo.txt $(OBJDIR)phplint.log $(OBJDIR)shlint.log $(OBJDIR)sqlite.db $(OBJDIR)sqlite.db3 $(OBJDIR)tests.log $(OBJDIR)widgets.html
OBJDIR	=
PREFIX	= /usr/local
DESTDIR	=
RM	= rm -f
LN	= ln -f
MKDIR	= mkdir -m 0755 -p
INSTALL	= install


all: $(TARGETS)

$(OBJDIR)phpinfo.txt: php.sh phpinfo.php
	./php.sh -P "$(PREFIX)" -- "$(OBJDIR)phpinfo.txt"

$(OBJDIR)phplint.log: phplint.sh $(OBJDIR)../src/daportal.php
	./phplint.sh -P "$(PREFIX)" -- "$(OBJDIR)phplint.log"

$(OBJDIR)shlint.log: shlint.sh
	./shlint.sh -P "$(PREFIX)" -- "$(OBJDIR)shlint.log"

$(OBJDIR)sqlite.db: ../doc/sql/sqlite.sql database.sh
	./database.sh -P "$(PREFIX)" -- "$(OBJDIR)sqlite.db"

$(OBJDIR)sqlite.db3: ../doc/sql/sqlite.sql database.sh
	./database.sh -P "$(PREFIX)" -- "$(OBJDIR)sqlite.db3"

$(OBJDIR)tests.log: auth.php config.php coverage.php daportal.conf daportal.php database.php engine.php format.php group.php html.php http.php json.php mail.php module.php observer.php pki.php profile.php search.php $(OBJDIR)sqlite.db3 template.php tests.php tests.sh transaction.php user.php
	./tests.sh -P "$(PREFIX)" -- "$(OBJDIR)tests.log"

$(OBJDIR)widgets.html: php.sh widgets.php
	./php.sh -P "$(PREFIX)" -- "$(OBJDIR)widgets.html"

clean:
	$(RM) -- $(phpinfo.txt_OBJS) $(phplint.log_OBJS) $(shlint.log_OBJS) $(sqlite.db_OBJS) $(sqlite.db3_OBJS) $(tests.log_OBJS) $(widgets.html_OBJS)
	./php.sh -c -P "$(PREFIX)" -- "phpinfo.txt"
	./phplint.sh -c -P "$(PREFIX)" -- "phplint.log"
	./shlint.sh -c -P "$(PREFIX)" -- "shlint.log"
	./database.sh -c -P "$(PREFIX)" -- "sqlite.db"
	./database.sh -c -P "$(PREFIX)" -- "sqlite.db3"
	./tests.sh -c -P "$(PREFIX)" -- "tests.log"
	./php.sh -c -P "$(PREFIX)" -- "widgets.html"

distclean: clean
	$(RM) -- $(TARGETS)

install: $(TARGETS)

uninstall:

.PHONY: all clean distclean install uninstall
