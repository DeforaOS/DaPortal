SUBDIRS	= fpdf
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
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/formats
	$(INSTALL) -m 0644 atom.php $(DESTDIR)$(PREFIX)/daportal/src/formats/atom.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/formats
	$(INSTALL) -m 0644 csv.php $(DESTDIR)$(PREFIX)/daportal/src/formats/csv.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/formats
	$(INSTALL) -m 0644 fpdf.php $(DESTDIR)$(PREFIX)/daportal/src/formats/fpdf.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/formats
	$(INSTALL) -m 0644 html.php $(DESTDIR)$(PREFIX)/daportal/src/formats/html.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/formats
	$(INSTALL) -m 0644 html5.php $(DESTDIR)$(PREFIX)/daportal/src/formats/html5.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/formats
	$(INSTALL) -m 0644 pdf.php $(DESTDIR)$(PREFIX)/daportal/src/formats/pdf.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/formats
	$(INSTALL) -m 0644 plain.php $(DESTDIR)$(PREFIX)/daportal/src/formats/plain.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/formats
	$(INSTALL) -m 0644 xhtml1.php $(DESTDIR)$(PREFIX)/daportal/src/formats/xhtml1.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/formats
	$(INSTALL) -m 0644 xhtml11.php $(DESTDIR)$(PREFIX)/daportal/src/formats/xhtml11.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/formats
	$(INSTALL) -m 0644 xml.php $(DESTDIR)$(PREFIX)/daportal/src/formats/xml.php

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) uninstall) || exit; done
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/formats/atom.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/formats/csv.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/formats/fpdf.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/formats/html.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/formats/html5.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/formats/pdf.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/formats/plain.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/formats/xhtml1.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/formats/xhtml11.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/formats/xml.php

.PHONY: all subdirs clean distclean install uninstall
