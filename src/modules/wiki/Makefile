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
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/modules/wiki
	$(INSTALL) -m 0644 content.php $(DESTDIR)$(PREFIX)/daportal/src/modules/wiki/content.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/modules/wiki
	$(INSTALL) -m 0644 module.php $(DESTDIR)$(PREFIX)/daportal/src/modules/wiki/module.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/modules/wiki/content.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/modules/wiki/module.php

.PHONY: all clean distclean install uninstall
