SUBDIRS	= content scm
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

install: all
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" install; \
		else $(MAKE) install; fi) || exit; done
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project
	$(INSTALL) -m 0644 module.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/module.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project
	$(INSTALL) -m 0644 content.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project
	$(INSTALL) -m 0644 scm.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/scm.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project
	$(INSTALL) -m 0644 user.php $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/user.php

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" uninstall; \
		else $(MAKE) uninstall; fi) || exit; done
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/module.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/content.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/scm.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/modules/project/user.php

.PHONY: all subdirs clean distclean install uninstall
