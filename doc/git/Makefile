OBJDIR	=
PREFIX	= /usr/local
DESTDIR	=
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f


all:

clean:

distclean: clean

install:
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/doc/DaPortal/git
	$(INSTALL) -m 0644 git-hook.sh $(DESTDIR)$(PREFIX)/share/doc/DaPortal/git/git-hook.sh

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/share/doc/DaPortal/git/git-hook.sh

.PHONY: all clean distclean install uninstall
