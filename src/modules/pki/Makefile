SUBDIRS	= content
OBJDIR	=
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
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/modules/pki
	$(INSTALL) -m 0644 content.php $(DESTDIR)$(PREFIX)/daportal/src/modules/pki/content.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/modules/pki
	$(INSTALL) -m 0644 module.php $(DESTDIR)$(PREFIX)/daportal/src/modules/pki/module.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/doc/DaPortal
	$(INSTALL) -m 0644 openssl.cnf $(DESTDIR)$(PREFIX)/share/doc/DaPortal/openssl.cnf

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) uninstall) || exit; done
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/modules/pki/content.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/modules/pki/module.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/doc/DaPortal/openssl.cnf

.PHONY: all subdirs clean distclean install uninstall
