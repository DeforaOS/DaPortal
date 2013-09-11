PREFIX	= /usr/local
DESTDIR	=
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f


all:

clean:

distclean: clean

install:
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/icons/generic/48x48
	$(INSTALL) -m 0644 spinner.gif $(DESTDIR)$(PREFIX)/daportal/data/icons/generic/48x48/spinner.gif
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/icons/generic/48x48
	$(INSTALL) -m 0644 index.php $(DESTDIR)$(PREFIX)/daportal/data/icons/generic/48x48/index.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/icons/generic/48x48/spinner.gif
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/icons/generic/48x48/index.php

.PHONY: all clean distclean install uninstall
