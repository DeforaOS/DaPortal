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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16
	$(INSTALL) -m 0644 gtk-cancel.png $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16/gtk-cancel.png
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16
	$(INSTALL) -m 0644 gtk-no.png $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16/gtk-no.png
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16
	$(INSTALL) -m 0644 gtk-ok.png $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16/gtk-ok.png
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16
	$(INSTALL) -m 0644 gtk-yes.png $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16/gtk-yes.png
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16
	$(INSTALL) -m 0644 index.php $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16/index.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16
	$(INSTALL) -m 0644 stock.png $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16/stock.png

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16/gtk-cancel.png
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16/gtk-no.png
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16/gtk-ok.png
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16/gtk-yes.png
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16/index.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic/16x16/stock.png

.PHONY: all clean distclean install uninstall
