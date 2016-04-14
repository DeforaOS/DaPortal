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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/templates
	$(INSTALL) -m 0644 DeforaOS.php $(DESTDIR)$(PREFIX)/share/daportal/src/templates/DeforaOS.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/templates
	$(INSTALL) -m 0644 EdgeBSD.php $(DESTDIR)$(PREFIX)/share/daportal/src/templates/EdgeBSD.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/templates
	$(INSTALL) -m 0644 basic.php $(DESTDIR)$(PREFIX)/share/daportal/src/templates/basic.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/templates
	$(INSTALL) -m 0644 desktop.php $(DESTDIR)$(PREFIX)/share/daportal/src/templates/desktop.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/templates
	$(INSTALL) -m 0644 khorben.php $(DESTDIR)$(PREFIX)/share/daportal/src/templates/khorben.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/templates/DeforaOS.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/templates/EdgeBSD.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/templates/basic.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/templates/desktop.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/templates/khorben.php

.PHONY: all clean distclean install uninstall
