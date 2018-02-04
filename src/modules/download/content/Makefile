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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/download/content
	$(INSTALL) -m 0644 file.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/download/content/file.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/download/content
	$(INSTALL) -m 0644 folder.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/download/content/folder.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/download/content/file.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/download/content/folder.php

.PHONY: all clean distclean install uninstall
