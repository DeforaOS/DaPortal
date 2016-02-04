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
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/system/response
	$(INSTALL) -m 0644 error.php $(DESTDIR)$(PREFIX)/daportal/src/system/response/error.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/system/response
	$(INSTALL) -m 0644 page.php $(DESTDIR)$(PREFIX)/daportal/src/system/response/page.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/system/response
	$(INSTALL) -m 0644 stream.php $(DESTDIR)$(PREFIX)/daportal/src/system/response/stream.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/system/response
	$(INSTALL) -m 0644 string.php $(DESTDIR)$(PREFIX)/daportal/src/system/response/string.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/system/response/error.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/system/response/page.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/system/response/stream.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/system/response/string.php

.PHONY: all clean distclean install uninstall
