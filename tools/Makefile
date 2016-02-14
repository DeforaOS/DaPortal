MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f
TARGETS	= $(OBJDIR)daportal
OBJDIR	=
PREFIX	= /usr/local
DESTDIR	=
RM	= rm -f
LN	= ln -f
MKDIR	= mkdir -m 0755 -p
INSTALL	= install


all: $(TARGETS)

$(OBJDIR)daportal: daportal.in subst.sh ../config.sh
	./subst.sh -P "$(PREFIX)" -- "$(OBJDIR)daportal"

clean:
	$(RM) -- $(daportal_OBJS)
	./subst.sh -c -P "$(PREFIX)" -- "$(OBJDIR)daportal"

distclean: clean
	$(RM) -- $(TARGETS)

install: $(TARGETS)
	./subst.sh -P "$(DESTDIR)$(PREFIX)/bin" -i -- "$(OBJDIR)daportal"
	$(MKDIR) $(DESTDIR)$(PREFIX)/bin
	$(INSTALL) -m 0755 daportal-group $(DESTDIR)$(PREFIX)/bin/daportal-group
	$(MKDIR) $(DESTDIR)$(PREFIX)/bin
	$(INSTALL) -m 0755 daportal-user $(DESTDIR)$(PREFIX)/bin/daportal-user

uninstall:
	./subst.sh -P "$(DESTDIR)$(PREFIX)/bin" -u -- "daportal"
	$(RM) -- $(DESTDIR)$(PREFIX)/bin/daportal-group
	$(RM) -- $(DESTDIR)$(PREFIX)/bin/daportal-user

.PHONY: all clean distclean install uninstall
