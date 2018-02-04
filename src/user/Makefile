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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/user
	$(INSTALL) -m 0644 sql.php $(DESTDIR)$(PREFIX)/share/daportal/src/user/sql.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/user/sql.php

.PHONY: all clean distclean install uninstall
