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
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/auth
	$(INSTALL) -m 0644 env.php $(DESTDIR)$(PREFIX)/daportal/src/auth/env.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/auth
	$(INSTALL) -m 0644 http.php $(DESTDIR)$(PREFIX)/daportal/src/auth/http.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/auth
	$(INSTALL) -m 0644 session.php $(DESTDIR)$(PREFIX)/daportal/src/auth/session.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/auth
	$(INSTALL) -m 0644 unix.php $(DESTDIR)$(PREFIX)/daportal/src/auth/unix.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/auth/env.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/auth/http.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/auth/session.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/auth/unix.php

.PHONY: all clean distclean install uninstall
