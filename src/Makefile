SUBDIRS	= auth database engines formats modules system templates
TARGETS	= $(OBJDIR)daportal.php
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

$(OBJDIR)daportal.php: daportal.php.in ../config.sh ../tools/subst.sh
	../tools/subst.sh -P "$(PREFIX)" -- "$(OBJDIR)daportal.php"

clean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) clean) || exit; done
	$(RM) -- $(daportal.php_OBJS)
	../tools/subst.sh -c -P "$(PREFIX)" -- "daportal.php"

distclean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) distclean) || exit; done
	$(RM) -- $(daportal.php_OBJS)
	../tools/subst.sh -c -P "$(PREFIX)" -- "daportal.php"
	$(RM) -- $(TARGETS)

install: $(TARGETS)
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) install) || exit; done
	../tools/subst.sh -P "$(DESTDIR)$(PREFIX)/daportal/src" -i -- "$(OBJDIR)daportal.php"

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) uninstall) || exit; done
	../tools/subst.sh -P "$(DESTDIR)$(PREFIX)/daportal/src" -u -- "daportal.php"

.PHONY: all subdirs clean distclean install uninstall
