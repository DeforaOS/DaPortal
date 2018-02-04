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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/doc/DaPortal/php
	$(INSTALL) -m 0644 php.ini $(DESTDIR)$(PREFIX)/share/doc/DaPortal/php/php.ini

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/doc/DaPortal/php/php.ini

.PHONY: all clean distclean install uninstall
