SUBDIRS	= fpdf
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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/formats
	$(INSTALL) -m 0644 atom.php $(DESTDIR)$(PREFIX)/share/daportal/src/formats/atom.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/formats
	$(INSTALL) -m 0644 csv.php $(DESTDIR)$(PREFIX)/share/daportal/src/formats/csv.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/formats
	$(INSTALL) -m 0644 fpdf.php $(DESTDIR)$(PREFIX)/share/daportal/src/formats/fpdf.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/formats
	$(INSTALL) -m 0644 gtk.php $(DESTDIR)$(PREFIX)/share/daportal/src/formats/gtk.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/formats
	$(INSTALL) -m 0644 html.php $(DESTDIR)$(PREFIX)/share/daportal/src/formats/html.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/formats
	$(INSTALL) -m 0644 html5.php $(DESTDIR)$(PREFIX)/share/daportal/src/formats/html5.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/formats
	$(INSTALL) -m 0644 json.php $(DESTDIR)$(PREFIX)/share/daportal/src/formats/json.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/formats
	$(INSTALL) -m 0644 pdf.php $(DESTDIR)$(PREFIX)/share/daportal/src/formats/pdf.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/formats
	$(INSTALL) -m 0644 plain.php $(DESTDIR)$(PREFIX)/share/daportal/src/formats/plain.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/formats
	$(INSTALL) -m 0644 xhtml1.php $(DESTDIR)$(PREFIX)/share/daportal/src/formats/xhtml1.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/formats
	$(INSTALL) -m 0644 xhtml11.php $(DESTDIR)$(PREFIX)/share/daportal/src/formats/xhtml11.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/formats
	$(INSTALL) -m 0644 xml.php $(DESTDIR)$(PREFIX)/share/daportal/src/formats/xml.php

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" uninstall; \
		else $(MAKE) uninstall; fi) || exit; done
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/formats/atom.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/formats/csv.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/formats/fpdf.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/formats/gtk.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/formats/html.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/formats/html5.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/formats/json.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/formats/pdf.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/formats/plain.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/formats/xhtml1.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/formats/xhtml11.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/formats/xml.php

.PHONY: all subdirs clean distclean install uninstall
