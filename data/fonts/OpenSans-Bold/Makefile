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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold
	$(INSTALL) -m 0644 OpenSans-Bold-webfont.eot $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.eot
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold
	$(INSTALL) -m 0644 OpenSans-Bold-webfont.svg $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.svg
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold
	$(INSTALL) -m 0644 OpenSans-Bold-webfont.ttf $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.ttf
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold
	$(INSTALL) -m 0644 OpenSans-Bold-webfont.woff $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.woff
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold
	$(INSTALL) -m 0644 stylesheet.css $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold/stylesheet.css

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.eot
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.svg
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.ttf
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.woff
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Bold/stylesheet.css

.PHONY: all clean distclean install uninstall
