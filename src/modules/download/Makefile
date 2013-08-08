SUBDIRS	= content
PREFIX	= /usr/local
DESTDIR	=
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f


all: subdirs

subdirs:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE)) || exit; done

clean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) clean) || exit; done

distclean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) distclean) || exit; done

install:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) install) || exit; done
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/modules/download
	$(INSTALL) -m 0644 content.php $(DESTDIR)$(PREFIX)/daportal/src/modules/download/content.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/modules/download
	$(INSTALL) -m 0644 module.php $(DESTDIR)$(PREFIX)/daportal/src/modules/download/module.php

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) uninstall) || exit; done
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/modules/download/content.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/modules/download/module.php

.PHONY: all subdirs clean distclean install uninstall
