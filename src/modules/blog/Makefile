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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/blog
	$(INSTALL) -m 0644 content.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/blog/content.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/blog
	$(INSTALL) -m 0644 module.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/blog/module.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/blog/content.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/blog/module.php

.PHONY: all clean distclean install uninstall
