PREFIX	= /usr/local
DESTDIR	=
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f


all:

clean:

distclean: clean

install:
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold
	$(INSTALL) -m 0644 OpenSans-Bold-webfont.eot $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.eot
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold
	$(INSTALL) -m 0644 OpenSans-Bold-webfont.svg $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.svg
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold
	$(INSTALL) -m 0644 OpenSans-Bold-webfont.ttf $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.ttf
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold
	$(INSTALL) -m 0644 OpenSans-Bold-webfont.woff $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.woff
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold
	$(INSTALL) -m 0644 stylesheet.css $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold/stylesheet.css

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.eot
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.svg
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.ttf
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold/OpenSans-Bold-webfont.woff
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/fonts/OpenSans-Bold/stylesheet.css

.PHONY: all clean distclean install uninstall
