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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons/DeforaOS
	$(INSTALL) -m 0644 index.php $(DESTDIR)$(PREFIX)/share/daportal/data/icons/DeforaOS/index.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/DeforaOS/index.php

.PHONY: all clean distclean install uninstall
