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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/48x48
	$(INSTALL) -m 0644 index.php $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/48x48/index.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/48x48
	$(INSTALL) -m 0644 spinner.gif $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/48x48/spinner.gif
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/48x48
	$(INSTALL) -m 0644 stock.png $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/48x48/stock.png

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/48x48/index.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/48x48/spinner.gif
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/48x48/stock.png

.PHONY: all clean distclean install uninstall
