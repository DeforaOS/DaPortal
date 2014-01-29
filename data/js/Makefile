PREFIX	= /usr/local
DESTDIR	=
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f


all:

clean:

distclean: clean

install:
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/js
	$(INSTALL) -m 0644 DaPortal.js $(DESTDIR)$(PREFIX)/daportal/data/js/DaPortal.js
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/js
	$(INSTALL) -m 0644 index.php $(DESTDIR)$(PREFIX)/daportal/data/js/index.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/js
	$(INSTALL) -m 0644 jquery.js $(DESTDIR)$(PREFIX)/daportal/data/js/jquery.js

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/js/DaPortal.js
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/js/index.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/js/jquery.js

.PHONY: all clean distclean install uninstall
