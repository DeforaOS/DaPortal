TARGETS	= $(OBJDIR)phpinfo.txt $(OBJDIR)phplint.log $(OBJDIR)shlint.log $(OBJDIR)sqlite.db $(OBJDIR)sqlite.db3 $(OBJDIR)tests.log $(OBJDIR)widgets.html
OBJDIR	=
PREFIX	= /usr/local
DESTDIR	=
RM	= rm -f
LN	= ln -f
MKDIR	= mkdir -m 0755 -p
INSTALL	= install


all: $(TARGETS)

$(OBJDIR)phpinfo.txt: $(OBJDIR)sqlite.db3 php.sh phpinfo.php
	./php.sh -P "$(PREFIX)" -- "$(OBJDIR)phpinfo.txt"

$(OBJDIR)phplint.log: phplint.sh $(OBJDIR)../src/daportal.php
	./phplint.sh -P "$(PREFIX)" -- "$(OBJDIR)phplint.log"

$(OBJDIR)shlint.log: shlint.sh
	./shlint.sh -P "$(PREFIX)" -- "$(OBJDIR)shlint.log"

$(OBJDIR)sqlite.db: ../doc/sql/sqlite.sql database.sh
	./database.sh -P "$(PREFIX)" -- "$(OBJDIR)sqlite.db"

$(OBJDIR)sqlite.db3: ../doc/sql/sqlite.sql database.sh
	./database.sh -P "$(PREFIX)" -- "$(OBJDIR)sqlite.db3"

$(OBJDIR)tests.log: auth.php browser.php config.php content.php coverage.php daportal.conf daportal.php database.php engine.php format.php group.php html.php http.php json.php mail.php module.php observer.php pki.php pool.php profile.php search.php $(OBJDIR)sqlite.db3 template.php tests.php tests.sh transaction.php user.php
	./tests.sh -P "$(PREFIX)" -- "$(OBJDIR)tests.log"

$(OBJDIR)widgets.html: $(OBJDIR)sqlite.db3 php.sh widgets.php
	./php.sh -P "$(PREFIX)" -- "$(OBJDIR)widgets.html"

clean:
	$(RM) -- $(phpinfo.txt_OBJS) $(phplint.log_OBJS) $(shlint.log_OBJS) $(sqlite.db_OBJS) $(sqlite.db3_OBJS) $(tests.log_OBJS) $(widgets.html_OBJS)
	./php.sh -c -P "$(PREFIX)" -- "$(OBJDIR)phpinfo.txt"
	./phplint.sh -c -P "$(PREFIX)" -- "$(OBJDIR)phplint.log"
	./shlint.sh -c -P "$(PREFIX)" -- "$(OBJDIR)shlint.log"
	./database.sh -c -P "$(PREFIX)" -- "$(OBJDIR)sqlite.db"
	./database.sh -c -P "$(PREFIX)" -- "$(OBJDIR)sqlite.db3"
	./tests.sh -c -P "$(PREFIX)" -- "$(OBJDIR)tests.log"
	./php.sh -c -P "$(PREFIX)" -- "$(OBJDIR)widgets.html"

distclean: clean
	$(RM) -- $(TARGETS)

install: $(TARGETS)

uninstall:

.PHONY: all clean distclean install uninstall
