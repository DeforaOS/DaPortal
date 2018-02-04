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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/24x24
	$(INSTALL) -m 0644 collapsed.png $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/24x24/collapsed.png
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/24x24
	$(INSTALL) -m 0644 expanded.png $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/24x24/expanded.png
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/24x24
	$(INSTALL) -m 0644 index.php $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/24x24/index.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/24x24
	$(INSTALL) -m 0644 stock.png $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/24x24/stock.png

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/24x24/collapsed.png
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/24x24/expanded.png
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/24x24/index.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/24x24/stock.png

.PHONY: all clean distclean install uninstall
