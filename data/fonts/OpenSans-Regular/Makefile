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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular
	$(INSTALL) -m 0644 OpenSans-Regular-webfont.eot $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular/OpenSans-Regular-webfont.eot
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular
	$(INSTALL) -m 0644 OpenSans-Regular-webfont.svg $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular/OpenSans-Regular-webfont.svg
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular
	$(INSTALL) -m 0644 OpenSans-Regular-webfont.ttf $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular/OpenSans-Regular-webfont.ttf
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular
	$(INSTALL) -m 0644 OpenSans-Regular-webfont.woff $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular/OpenSans-Regular-webfont.woff
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular
	$(INSTALL) -m 0644 stylesheet.css $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular/stylesheet.css

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular/OpenSans-Regular-webfont.eot
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular/OpenSans-Regular-webfont.svg
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular/OpenSans-Regular-webfont.ttf
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular/OpenSans-Regular-webfont.woff
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/fonts/OpenSans-Regular/stylesheet.css

.PHONY: all clean distclean install uninstall
