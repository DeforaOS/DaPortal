OBJDIR	=
PREFIX	= /usr/local
DESTDIR	=
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f


all:

clean:

distclean: clean

install:
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/news
	$(INSTALL) -m 0644 content.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/news/content.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/news
	$(INSTALL) -m 0644 module.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/news/module.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/news/content.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/news/module.php

.PHONY: all clean distclean install uninstall
