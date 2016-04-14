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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/css
	$(INSTALL) -m 0644 DaPortal.css $(DESTDIR)$(PREFIX)/share/daportal/data/css/DaPortal.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/css
	$(INSTALL) -m 0644 icons.css $(DESTDIR)$(PREFIX)/share/daportal/data/css/icons.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/css
	$(INSTALL) -m 0644 index.php $(DESTDIR)$(PREFIX)/share/daportal/data/css/index.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/css
	$(INSTALL) -m 0644 manual.css $(DESTDIR)$(PREFIX)/share/daportal/data/css/manual.css

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/css/DaPortal.css
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/css/icons.css
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/css/index.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/css/manual.css

.PHONY: all clean distclean install uninstall
