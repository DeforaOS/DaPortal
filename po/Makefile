TARGETS	= $(OBJDIR)DaPortal.pot $(OBJDIR)es.mo $(OBJDIR)fr.mo
OBJDIR	=
PREFIX	= /usr/local
DESTDIR	=
RM	= rm -f
LN	= ln -f
MKDIR	= mkdir -m 0755 -p
INSTALL	= install


all: $(TARGETS)

$(OBJDIR)DaPortal.pot: POTFILES
	./gettext.sh -P "$(PREFIX)" -- "$(OBJDIR)DaPortal.pot"

$(OBJDIR)es.mo: $(OBJDIR)DaPortal.pot es.po
	./gettext.sh -P "$(PREFIX)" -- "$(OBJDIR)es.mo"

$(OBJDIR)fr.mo: $(OBJDIR)DaPortal.pot fr.po
	./gettext.sh -P "$(PREFIX)" -- "$(OBJDIR)fr.mo"

clean:
	./gettext.sh -c -P "$(PREFIX)" -- "$(OBJDIR)DaPortal.pot"
	./gettext.sh -c -P "$(PREFIX)" -- "$(OBJDIR)es.mo"
	./gettext.sh -c -P "$(PREFIX)" -- "$(OBJDIR)fr.mo"

distclean: clean
	$(RM) -- $(TARGETS)

install: all
	./gettext.sh -P "$(DESTDIR)$(PREFIX)" -i -- "$(OBJDIR)es.mo"
	./gettext.sh -P "$(DESTDIR)$(PREFIX)" -i -- "$(OBJDIR)fr.mo"

uninstall:
	./gettext.sh -P "$(DESTDIR)$(PREFIX)" -u -- "es.mo"
	./gettext.sh -P "$(DESTDIR)$(PREFIX)" -u -- "fr.mo"

.PHONY: all clean distclean install uninstall
