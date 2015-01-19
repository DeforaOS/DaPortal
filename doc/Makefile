SUBDIRS	= apache sql
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f
TARGETS	= $(OBJDIR)daportal.1 $(OBJDIR)daportal.html $(OBJDIR)daportal.conf $(OBJDIR)daportal.conf.5 $(OBJDIR)daportal.conf.html $(OBJDIR)daportal.xml $(OBJDIR)install.html $(OBJDIR)install.xml $(OBJDIR)internals.html $(OBJDIR)internals-print.pdf
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

$(OBJDIR)daportal.1: $(OBJDIR)daportal.xml
	./docbook.sh -P "$(PREFIX)" -- "$(OBJDIR)daportal.1"

$(OBJDIR)daportal.html: daportal.css.xml $(OBJDIR)daportal.xml manual.css.xml
	./docbook.sh -P "$(PREFIX)" -- "$(OBJDIR)daportal.html"

$(OBJDIR)daportal.conf: daportal.conf.in
	../tools/subst.sh -P "$(PREFIX)" -- "$(OBJDIR)daportal.conf"

$(OBJDIR)daportal.conf.5: daportal.conf.xml
	./docbook.sh -P "$(PREFIX)" -- "$(OBJDIR)daportal.conf.5"

$(OBJDIR)daportal.conf.html: daportal.conf.css.xml daportal.conf.xml manual.css.xml
	./docbook.sh -P "$(PREFIX)" -- "$(OBJDIR)daportal.conf.html"

$(OBJDIR)daportal.xml: daportal.xml.in
	../tools/subst.sh -P "$(PREFIX)" -- "$(OBJDIR)daportal.xml"

$(OBJDIR)install.html: $(OBJDIR)install.xml install.xsl
	./docbook.sh -P "$(PREFIX)" -- "$(OBJDIR)install.html"

$(OBJDIR)install.xml: install.xml.in
	../tools/subst.sh -P "$(PREFIX)" -- "$(OBJDIR)install.xml"

$(OBJDIR)internals.html: internals.xml internals.xsl
	./docbook.sh -P "$(PREFIX)" -- "$(OBJDIR)internals.html"

$(OBJDIR)internals-print.pdf: internals-print.xml internals-print.xsl
	./docbook.sh -P "$(PREFIX)" -- "$(OBJDIR)internals-print.pdf"

clean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) clean) || exit; done
	$(RM) -- $(daportal.1_OBJS) $(daportal.html_OBJS) $(daportal.conf_OBJS) $(daportal.conf.5_OBJS) $(daportal.conf.html_OBJS) $(daportal.xml_OBJS) $(install.html_OBJS) $(install.xml_OBJS) $(internals.html_OBJS) $(internals-print.pdf_OBJS)
	./docbook.sh -c -P "$(PREFIX)" -- "daportal.1"
	./docbook.sh -c -P "$(PREFIX)" -- "daportal.html"
	../tools/subst.sh -c -P "$(PREFIX)" -- "daportal.conf"
	./docbook.sh -c -P "$(PREFIX)" -- "daportal.conf.5"
	./docbook.sh -c -P "$(PREFIX)" -- "daportal.conf.html"
	../tools/subst.sh -c -P "$(PREFIX)" -- "daportal.xml"
	./docbook.sh -c -P "$(PREFIX)" -- "install.html"
	../tools/subst.sh -c -P "$(PREFIX)" -- "install.xml"
	./docbook.sh -c -P "$(PREFIX)" -- "internals.html"
	./docbook.sh -c -P "$(PREFIX)" -- "internals-print.pdf"

distclean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) distclean) || exit; done
	$(RM) -- $(daportal.1_OBJS) $(daportal.html_OBJS) $(daportal.conf_OBJS) $(daportal.conf.5_OBJS) $(daportal.conf.html_OBJS) $(daportal.xml_OBJS) $(install.html_OBJS) $(install.xml_OBJS) $(internals.html_OBJS) $(internals-print.pdf_OBJS)
	./docbook.sh -c -P "$(PREFIX)" -- "daportal.1"
	./docbook.sh -c -P "$(PREFIX)" -- "daportal.html"
	../tools/subst.sh -c -P "$(PREFIX)" -- "daportal.conf"
	./docbook.sh -c -P "$(PREFIX)" -- "daportal.conf.5"
	./docbook.sh -c -P "$(PREFIX)" -- "daportal.conf.html"
	../tools/subst.sh -c -P "$(PREFIX)" -- "daportal.xml"
	./docbook.sh -c -P "$(PREFIX)" -- "install.html"
	../tools/subst.sh -c -P "$(PREFIX)" -- "install.xml"
	./docbook.sh -c -P "$(PREFIX)" -- "internals.html"
	./docbook.sh -c -P "$(PREFIX)" -- "internals-print.pdf"
	$(RM) -- $(TARGETS)

install: $(TARGETS)
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) install) || exit; done
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -i -- "$(OBJDIR)daportal.1"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -i -- "$(OBJDIR)daportal.html"
	../tools/subst.sh -P "$(DESTDIR)$(PREFIX)/share/doc/DaPortal" -i -- "$(OBJDIR)daportal.conf"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -i -- "$(OBJDIR)daportal.conf.5"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -i -- "$(OBJDIR)daportal.conf.html"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -i -- "$(OBJDIR)install.html"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -i -- "$(OBJDIR)internals.html"
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/doc/DaPortal
	$(INSTALL) -m 0644 daportal-http.conf $(DESTDIR)$(PREFIX)/share/doc/DaPortal/daportal-http.conf

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) uninstall) || exit; done
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -u -- "daportal.1"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -u -- "daportal.html"
	../tools/subst.sh -P "$(DESTDIR)$(PREFIX)/share/doc/DaPortal" -u -- "daportal.conf"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -u -- "daportal.conf.5"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -u -- "daportal.conf.html"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -u -- "install.html"
	./docbook.sh -P "$(DESTDIR)$(PREFIX)" -u -- "internals.html"
	$(RM) -- $(DESTDIR)$(PREFIX)/share/doc/DaPortal/daportal-http.conf

.PHONY: all subdirs clean distclean install uninstall
