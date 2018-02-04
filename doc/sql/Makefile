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
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/doc/DaPortal/sql
	$(INSTALL) -m 0644 postgresql.sql $(DESTDIR)$(PREFIX)/share/doc/DaPortal/sql/postgresql.sql
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/doc/DaPortal/sql
	$(INSTALL) -m 0644 sqlite.sql $(DESTDIR)$(PREFIX)/share/doc/DaPortal/sql/sqlite.sql

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/doc/DaPortal/sql/postgresql.sql
	$(RM) -- $(DESTDIR)$(PREFIX)/share/doc/DaPortal/sql/sqlite.sql

.PHONY: all clean distclean install uninstall
