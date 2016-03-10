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
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/icons/Tango
	$(INSTALL) -m 0644 index.php $(DESTDIR)$(PREFIX)/daportal/data/icons/Tango/index.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/icons/Tango/index.php

.PHONY: all clean distclean install uninstall
