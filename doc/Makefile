SUBDIRS	= apache sql
TARGETS	= daportal.1 daportal.html daportal.conf daportal.conf.5 daportal.conf.html install.html internals.html internals-print.pdf
PREFIX	= /usr/local
DESTDIR	=
RM	= rm -f
LN	= ln -f
MKDIR	= mkdir -m 0755 -p
INSTALL	= install


all: subdirs $(TARGETS)

subdirs:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE)) || exit; done

daportal.1: daportal.xml
	./docbook.sh -P "$(PREFIX)" -- "daportal.1"

daportal.html: daportal.xml
	./docbook.sh -P "$(PREFIX)" -- "daportal.html"

daportal.conf: daportal.conf.in
	../tools/subst.sh -P "$(PREFIX)" -- "daportal.conf"

daportal.conf.5: daportal.conf.xml
	./docbook.sh -P "$(PREFIX)" -- "daportal.conf.5"

daportal.conf.html: daportal.conf.xml
	./docbook.sh -P "$(PREFIX)" -- "daportal.conf.html"

install.html: install.xml install.xsl
	./docbook.sh -P "$(PREFIX)" -- "install.html"

internals.html: internals.xml internals.xsl
	./docbook.sh -P "$(PREFIX)" -- "internals.html"

internals-print.pdf: internals-print.xml internals-print.xsl
	./docbook.sh -P "$(PREFIX)" -- "internals-print.pdf"

clean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) clean) || exit; done
	$(RM) -- $(daportal.1_OBJS) $(daportal.html_OBJS) $(daportal.conf_OBJS) $(daportal.conf.5_OBJS) $(daportal.conf.html_OBJS) $(install.html_OBJS) $(internals.html_OBJS) $(internals-print.pdf_OBJS)
	./docbook.sh -c -P "$(PREFIX)" -- "internals-print.pdf"

distclean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) distclean) || exit; done
	$(RM) -- $(daportal.1_OBJS) $(daportal.html_OBJS) $(daportal.conf_OBJS) $(daportal.conf.5_OBJS) $(daportal.conf.html_OBJS) $(install.html_OBJS) $(internals.html_OBJS) $(internals-print.pdf_OBJS)
	./docbook.sh -c -P "$(PREFIX)" -- "internals-print.pdf"
	$(RM) -- $(TARGETS)

install: $(TARGETS)
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) install) || exit; done
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -i -- "daportal.1"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -i -- "daportal.html"
	../tools/subst.sh -P "$(DESTDIR)$(PREFIX)/share/doc/DaPortal" -i -- "daportal.conf"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -i -- "daportal.conf.5"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -i -- "daportal.conf.html"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -i -- "install.html"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -i -- "internals.html"

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) uninstall) || exit; done
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -u -- "daportal.1"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -u -- "daportal.html"
	../tools/subst.sh -P "$(DESTDIR)$(PREFIX)/share/doc/DaPortal" -u -- "daportal.conf"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -u -- "daportal.conf.5"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -u -- "daportal.conf.html"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -u -- "install.html"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -u -- "internals.html"

.PHONY: all subdirs clean distclean install uninstall
