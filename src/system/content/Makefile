PREFIX	= /usr/local
DESTDIR	=
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f


all:

clean:

distclean: clean

install:
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/system/content
	$(INSTALL) -m 0644 multi.php $(DESTDIR)$(PREFIX)/daportal/src/system/content/multi.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/system/content
	$(INSTALL) -m 0644 result.php $(DESTDIR)$(PREFIX)/daportal/src/system/content/result.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/system/content/multi.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/system/content/result.php

.PHONY: all clean distclean install uninstall
