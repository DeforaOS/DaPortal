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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/scm
	$(INSTALL) -m 0644 cvs.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/scm/cvs.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/scm
	$(INSTALL) -m 0644 git.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/scm/git.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/scm/cvs.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/scm/git.php

.PHONY: all clean distclean install uninstall
