OBJDIR	=
PREFIX	= /usr/local
DESTDIR	=
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f


all:

clean:

distclean: clean

install: all
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/engines
	$(INSTALL) -m 0644 cgi.php $(DESTDIR)$(PREFIX)/share/daportal/src/engines/cgi.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/engines
	$(INSTALL) -m 0644 cli.php $(DESTDIR)$(PREFIX)/share/daportal/src/engines/cli.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/engines
	$(INSTALL) -m 0644 clihttp.php $(DESTDIR)$(PREFIX)/share/daportal/src/engines/clihttp.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/engines
	$(INSTALL) -m 0644 clilog.php $(DESTDIR)$(PREFIX)/share/daportal/src/engines/clilog.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/engines
	$(INSTALL) -m 0644 daportal.php $(DESTDIR)$(PREFIX)/share/daportal/src/engines/daportal.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/engines
	$(INSTALL) -m 0644 dummy.php $(DESTDIR)$(PREFIX)/share/daportal/src/engines/dummy.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/engines
	$(INSTALL) -m 0644 email.php $(DESTDIR)$(PREFIX)/share/daportal/src/engines/email.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/engines
	$(INSTALL) -m 0644 gtk.php $(DESTDIR)$(PREFIX)/share/daportal/src/engines/gtk.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/engines
	$(INSTALL) -m 0644 http.php $(DESTDIR)$(PREFIX)/share/daportal/src/engines/http.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/engines
	$(INSTALL) -m 0644 httpfriendly.php $(DESTDIR)$(PREFIX)/share/daportal/src/engines/httpfriendly.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/engines/cgi.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/engines/cli.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/engines/clihttp.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/engines/clilog.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/engines/daportal.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/engines/dummy.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/engines/email.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/engines/gtk.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/engines/http.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/engines/httpfriendly.php

.PHONY: all clean distclean install uninstall
