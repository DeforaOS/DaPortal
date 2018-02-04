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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content
	$(INSTALL) -m 0644 bug.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content/bug.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content
	$(INSTALL) -m 0644 bugreply.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content/bugreply.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content
	$(INSTALL) -m 0644 download.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content/download.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content
	$(INSTALL) -m 0644 screenshot.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content/screenshot.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content/bug.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content/bugreply.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content/download.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content/screenshot.php

.PHONY: all clean distclean install uninstall
