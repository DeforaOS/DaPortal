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
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/modules/project/content
	$(INSTALL) -m 0644 bug.php $(DESTDIR)$(PREFIX)/daportal/src/modules/project/content/bug.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/modules/project/content
	$(INSTALL) -m 0644 bugreply.php $(DESTDIR)$(PREFIX)/daportal/src/modules/project/content/bugreply.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/modules/project/content/bug.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/modules/project/content/bugreply.php

.PHONY: all clean distclean install uninstall
