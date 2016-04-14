SUBDIRS	= content
OBJDIR	=
PREFIX	= /usr/local
DESTDIR	=
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f


all: subdirs

subdirs:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		([ -d "$(OBJDIR)$$i" ] || $(MKDIR) -- "$(OBJDIR)$$i") && \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/"; \
		else $(MAKE); fi) || exit; done

clean:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" clean; \
		else $(MAKE) clean; fi) || exit; done

distclean:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" distclean; \
		else $(MAKE) distclean; fi) || exit; done

install:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" install; \
		else $(MAKE) install; fi) || exit; done
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/pki
	$(INSTALL) -m 0644 content.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/pki/content.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/pki
	$(INSTALL) -m 0644 module.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/pki/module.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/pki
	$(INSTALL) -m 0644 openssl.cnf.in $(DESTDIR)$(PREFIX)/share/daportal/src/modules/pki/openssl.cnf.in

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" uninstall; \
		else $(MAKE) uninstall; fi) || exit; done
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/pki/content.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/pki/module.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/pki/openssl.cnf.in

.PHONY: all subdirs clean distclean install uninstall
