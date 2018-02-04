SUBDIRS	= DeforaOS Rodent Tango generic gnome
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f
TARGETS	= $(OBJDIR)DeforaOS/16x16.css $(OBJDIR)DeforaOS/24x24.css $(OBJDIR)DeforaOS/32x32.css $(OBJDIR)DeforaOS/48x48.css $(OBJDIR)gnome/16x16.css $(OBJDIR)gnome/24x24.css $(OBJDIR)gnome/32x32.css $(OBJDIR)gnome/48x48.css
OBJDIR	=
PREFIX	= /usr/local
DESTDIR	=
RM	= rm -f
LN	= ln -f
MKDIR	= mkdir -m 0755 -p
INSTALL	= install


all: subdirs $(TARGETS)

subdirs:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		([ -d "$(OBJDIR)$$i" ] || $(MKDIR) -- "$(OBJDIR)$$i") && \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/"; \
		else $(MAKE); fi) || exit; done

$(OBJDIR)DeforaOS/16x16.css: icontheme.sh
	./icontheme.sh -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/16x16.css"

$(OBJDIR)DeforaOS/24x24.css: icontheme.sh
	./icontheme.sh -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/24x24.css"

$(OBJDIR)DeforaOS/32x32.css: icontheme.sh
	./icontheme.sh -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/32x32.css"

$(OBJDIR)DeforaOS/48x48.css: icontheme.sh
	./icontheme.sh -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/48x48.css"

$(OBJDIR)gnome/16x16.css: icontheme.sh
	./icontheme.sh -P "$(PREFIX)" -- "$(OBJDIR)gnome/16x16.css"

$(OBJDIR)gnome/24x24.css: icontheme.sh
	./icontheme.sh -P "$(PREFIX)" -- "$(OBJDIR)gnome/24x24.css"

$(OBJDIR)gnome/32x32.css: icontheme.sh
	./icontheme.sh -P "$(PREFIX)" -- "$(OBJDIR)gnome/32x32.css"

$(OBJDIR)gnome/48x48.css: icontheme.sh
	./icontheme.sh -P "$(PREFIX)" -- "$(OBJDIR)gnome/48x48.css"

clean:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" clean; \
		else $(MAKE) clean; fi) || exit; done
	$(RM) -- $(DeforaOS/16x16.css_OBJS) $(DeforaOS/24x24.css_OBJS) $(DeforaOS/32x32.css_OBJS) $(DeforaOS/48x48.css_OBJS) $(gnome/16x16.css_OBJS) $(gnome/24x24.css_OBJS) $(gnome/32x32.css_OBJS) $(gnome/48x48.css_OBJS)
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/16x16.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/24x24.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/32x32.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/48x48.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)gnome/16x16.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)gnome/24x24.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)gnome/32x32.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)gnome/48x48.css"

distclean:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" distclean; \
		else $(MAKE) distclean; fi) || exit; done
	$(RM) -- $(DeforaOS/16x16.css_OBJS) $(DeforaOS/24x24.css_OBJS) $(DeforaOS/32x32.css_OBJS) $(DeforaOS/48x48.css_OBJS) $(gnome/16x16.css_OBJS) $(gnome/24x24.css_OBJS) $(gnome/32x32.css_OBJS) $(gnome/48x48.css_OBJS)
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/16x16.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/24x24.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/32x32.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/48x48.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)gnome/16x16.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)gnome/24x24.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)gnome/32x32.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "$(OBJDIR)gnome/48x48.css"
	$(RM) -- $(TARGETS)

install: all
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" install; \
		else $(MAKE) install; fi) || exit; done
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -i -- "$(OBJDIR)DeforaOS/16x16.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -i -- "$(OBJDIR)DeforaOS/24x24.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -i -- "$(OBJDIR)DeforaOS/32x32.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -i -- "$(OBJDIR)DeforaOS/48x48.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -i -- "$(OBJDIR)gnome/16x16.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -i -- "$(OBJDIR)gnome/24x24.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -i -- "$(OBJDIR)gnome/32x32.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -i -- "$(OBJDIR)gnome/48x48.css"
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons
	$(INSTALL) -m 0644 DeforaOS.css $(DESTDIR)$(PREFIX)/share/daportal/data/icons/DeforaOS.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons
	$(INSTALL) -m 0644 Rodent.css $(DESTDIR)$(PREFIX)/share/daportal/data/icons/Rodent.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons
	$(INSTALL) -m 0644 Tango.css $(DESTDIR)$(PREFIX)/share/daportal/data/icons/Tango.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons
	$(INSTALL) -m 0644 generic.css $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons
	$(INSTALL) -m 0644 gnome.css $(DESTDIR)$(PREFIX)/share/daportal/data/icons/gnome.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/data/icons
	$(INSTALL) -m 0644 index.php $(DESTDIR)$(PREFIX)/share/daportal/data/icons/index.php

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" uninstall; \
		else $(MAKE) uninstall; fi) || exit; done
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -u -- "DeforaOS/16x16.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -u -- "DeforaOS/24x24.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -u -- "DeforaOS/32x32.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -u -- "DeforaOS/48x48.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -u -- "gnome/16x16.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -u -- "gnome/24x24.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -u -- "gnome/32x32.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/share/daportal/data/icons" -u -- "gnome/48x48.css"
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/DeforaOS.css
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/Rodent.css
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/Tango.css
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/generic.css
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/gnome.css
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/data/icons/index.php

.PHONY: all subdirs clean distclean install uninstall
