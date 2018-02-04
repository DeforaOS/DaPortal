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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/content
	$(INSTALL) -m 0644 multi.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/content/multi.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/content
	$(INSTALL) -m 0644 result.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/content/result.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/content/multi.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/content/result.php

.PHONY: all clean distclean install uninstall
