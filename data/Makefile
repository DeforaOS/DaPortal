SUBDIRS	= css fonts icons js themes
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f
TARGETS	= $(OBJDIR)index.php
OBJDIR	=
PREFIX	= /usr/local
DESTDIR	=
RM	= rm -f
LN	= ln -f
MKDIR	= mkdir -m 0755 -p
INSTALL	= install


all: subdirs $(TARGETS)

subdirs:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		([ -d "$(OBJDIR)$$i" ] || $(MKDIR) -- "$(OBJDIR)$$i") && \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/"; \
		else $(MAKE); fi) || exit; done

$(OBJDIR)index.php: index.php.in ../config.sh ../tools/subst.sh
	../tools/subst.sh -P "$(PREFIX)" -- "$(OBJDIR)index.php"

clean:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" clean; \
		else $(MAKE) clean; fi) || exit; done
	../tools/subst.sh -c -P "$(PREFIX)" -- "$(OBJDIR)index.php"

distclean:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" distclean; \
		else $(MAKE) distclean; fi) || exit; done
	../tools/subst.sh -c -P "$(PREFIX)" -- "$(OBJDIR)index.php"
	$(RM) -- $(TARGETS)

install: all
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" install; \
		else $(MAKE) install; fi) || exit; done
	../tools/subst.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data" -i -- "$(OBJDIR)index.php"
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data
	$(INSTALL) -m 0644 daportal.php $(DESTDIR)$(PREFIX)/share/daportal/data/daportal.php

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" uninstall; \
		else $(MAKE) uninstall; fi) || exit; done
	../tools/subst.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data" -u -- "index.php"
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/daportal.php

.PHONY: all subdirs clean distclean install uninstall
