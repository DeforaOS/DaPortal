OBJDIR	=
PREFIX	= /usr/local
DESTDIR	=
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f


all:

clean:

distclean: clean

install: all
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/doc/DaPortal/apache
	$(INSTALL) -m 0644 httpd-php5.conf $(DESTDIR)$(PREFIX)/share/doc/DaPortal/apache/httpd-php5.conf
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/doc/DaPortal/apache
	$(INSTALL) -m 0644 httpd-php5-cgi.conf $(DESTDIR)$(PREFIX)/share/doc/DaPortal/apache/httpd-php5-cgi.conf

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/doc/DaPortal/apache/httpd-php5.conf
	$(RM) -- $(DESTDIR)$(PREFIX)/share/doc/DaPortal/apache/httpd-php5-cgi.conf

.PHONY: all clean distclean install uninstall
