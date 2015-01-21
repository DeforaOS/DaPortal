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
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/modules/pki/content
	$(INSTALL) -m 0644 ca.php $(DESTDIR)$(PREFIX)/daportal/src/modules/pki/content/ca.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/modules/pki/content
	$(INSTALL) -m 0644 caclient.php $(DESTDIR)$(PREFIX)/daportal/src/modules/pki/content/caclient.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/modules/pki/content
	$(INSTALL) -m 0644 caserver.php $(DESTDIR)$(PREFIX)/daportal/src/modules/pki/content/caserver.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/modules/pki/content/ca.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/modules/pki/content/caclient.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/modules/pki/content/caserver.php

.PHONY: all clean distclean install uninstall
