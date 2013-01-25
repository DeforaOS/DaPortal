TARGETS	= daportal
PREFIX	= /usr/local
DESTDIR	=
RM	= rm -f
LN	= ln -f
MKDIR	= mkdir -m 0755 -p
INSTALL	= install


all: $(TARGETS)

daportal: daportal.in subst.sh ../config.sh
	./subst.sh -P "$(PREFIX)" -- "daportal"

clean:
	$(RM) -- $(daportal_OBJS)
	./subst.sh -c -P "$(PREFIX)" -- "daportal"

distclean: clean
	$(RM) -- $(TARGETS)

install: $(TARGETS)
	./subst.sh -P "$(DESTDIR)$(PREFIX)/bin" -i -- "daportal"

uninstall:
	./subst.sh -P "$(DESTDIR)$(PREFIX)/bin" -u -- "daportal"

.PHONY: all clean distclean install uninstall
