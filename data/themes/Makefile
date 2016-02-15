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
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/themes
	$(INSTALL) -m 0644 DaPortal.css $(DESTDIR)$(PREFIX)/daportal/data/themes/DaPortal.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/themes
	$(INSTALL) -m 0644 DeforaOS.css $(DESTDIR)$(PREFIX)/daportal/data/themes/DeforaOS.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/themes
	$(INSTALL) -m 0644 DeforaOS.png $(DESTDIR)$(PREFIX)/daportal/data/themes/DeforaOS.png
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/themes
	$(INSTALL) -m 0644 DeforaOS-background.png $(DESTDIR)$(PREFIX)/daportal/data/themes/DeforaOS-background.png
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/themes
	$(INSTALL) -m 0644 DeforaOS-favicon.png $(DESTDIR)$(PREFIX)/daportal/data/themes/DeforaOS-favicon.png
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/themes
	$(INSTALL) -m 0644 EdgeBSD.css $(DESTDIR)$(PREFIX)/daportal/data/themes/EdgeBSD.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/themes
	$(INSTALL) -m 0644 EdgeBSD.png $(DESTDIR)$(PREFIX)/daportal/data/themes/EdgeBSD.png
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/themes
	$(INSTALL) -m 0644 EdgeBSD-favicon.png $(DESTDIR)$(PREFIX)/daportal/data/themes/EdgeBSD-favicon.png
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/themes
	$(INSTALL) -m 0644 EdgeBSD-small.png $(DESTDIR)$(PREFIX)/daportal/data/themes/EdgeBSD-small.png
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/themes
	$(INSTALL) -m 0644 index.php $(DESTDIR)$(PREFIX)/daportal/data/themes/index.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/themes
	$(INSTALL) -m 0644 khorben.css $(DESTDIR)$(PREFIX)/daportal/data/themes/khorben.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/themes
	$(INSTALL) -m 0644 khorben-coldfire.jpg $(DESTDIR)$(PREFIX)/daportal/data/themes/khorben-coldfire.jpg

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/themes/DaPortal.css
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/themes/DeforaOS.css
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/themes/DeforaOS.png
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/themes/DeforaOS-background.png
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/themes/DeforaOS-favicon.png
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/themes/EdgeBSD.css
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/themes/EdgeBSD.png
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/themes/EdgeBSD-favicon.png
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/themes/EdgeBSD-small.png
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/themes/index.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/themes/khorben.css
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/themes/khorben-coldfire.jpg

.PHONY: all clean distclean install uninstall
