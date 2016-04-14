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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/response
	$(INSTALL) -m 0644 cachepipe.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/response/cachepipe.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/response
	$(INSTALL) -m 0644 error.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/response/error.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/response
	$(INSTALL) -m 0644 page.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/response/page.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/response
	$(INSTALL) -m 0644 pipe.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/response/pipe.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/response
	$(INSTALL) -m 0644 stream.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/response/stream.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/response
	$(INSTALL) -m 0644 string.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/response/string.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/response/cachepipe.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/response/error.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/response/page.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/response/pipe.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/response/stream.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/response/string.php

.PHONY: all clean distclean install uninstall
