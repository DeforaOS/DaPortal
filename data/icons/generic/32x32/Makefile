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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/32x32
	$(INSTALL) -m 0644 index.php $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/32x32/index.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/32x32
	$(INSTALL) -m 0644 stock.png $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/32x32/stock.png

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/32x32/index.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/32x32/stock.png

.PHONY: all clean distclean install uninstall
