PREFIX	= /usr/local
DESTDIR	=
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f


all:

clean:

distclean: clean

install:
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/modules/group
	$(INSTALL) -m 0644 module.php $(DESTDIR)$(PREFIX)/daportal/src/modules/group/module.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/modules/group/module.php

.PHONY: all clean distclean install uninstall
