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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/Rodent
	$(INSTALL) -m 0644 48x48.css $(DESTDIR)$(PREFIX)/share/daportal/data/icons/Rodent/48x48.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/Rodent
	$(INSTALL) -m 0644 index.php $(DESTDIR)$(PREFIX)/share/daportal/data/icons/Rodent/index.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/Rodent
	$(INSTALL) -m 0644 scalable.css $(DESTDIR)$(PREFIX)/share/daportal/data/icons/Rodent/scalable.css

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/Rodent/48x48.css
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/Rodent/index.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/Rodent/scalable.css

.PHONY: all clean distclean install uninstall
