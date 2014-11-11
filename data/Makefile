SUBDIRS	= css fonts icons js themes
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
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE)) || exit; done

$(OBJDIR)index.php: index.php.in ../config.sh ../tools/subst.sh
	../tools/subst.sh -P "$(PREFIX)" -- "$(OBJDIR)index.php"

clean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) clean) || exit; done
	$(RM) -- $(index.php_OBJS)
	../tools/subst.sh -c -P "$(PREFIX)" -- "index.php"

distclean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) distclean) || exit; done
	$(RM) -- $(index.php_OBJS)
	../tools/subst.sh -c -P "$(PREFIX)" -- "index.php"
	$(RM) -- $(TARGETS)

install: $(TARGETS)
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) install) || exit; done
	../tools/subst.sh -P "$(DESTDIR)$(PREFIX)/daportal/data" -i -- "$(OBJDIR)index.php"

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) uninstall) || exit; done
	../tools/subst.sh -P "$(DESTDIR)$(PREFIX)/daportal/data" -u -- "index.php"

.PHONY: all subdirs clean distclean install uninstall
