SUBDIRS	= Rodent Tango generic gnome
MKDIR	= mkdir -m 0755 -p
INSTALL	= install
RM	= rm -f
TARGETS	= $(OBJDIR)DeforaOS/16x16.css $(OBJDIR)DeforaOS/24x24.css $(OBJDIR)DeforaOS/32x32.css $(OBJDIR)DeforaOS/48x48.css
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

$(OBJDIR)DeforaOS/16x16.css: icontheme.sh
	./icontheme.sh -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/16x16.css"

$(OBJDIR)DeforaOS/24x24.css: icontheme.sh
	./icontheme.sh -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/24x24.css"

$(OBJDIR)DeforaOS/32x32.css: icontheme.sh
	./icontheme.sh -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/32x32.css"

$(OBJDIR)DeforaOS/48x48.css: icontheme.sh
	./icontheme.sh -P "$(PREFIX)" -- "$(OBJDIR)DeforaOS/48x48.css"

clean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) clean) || exit; done
	$(RM) -- $(DeforaOS/16x16.css_OBJS) $(DeforaOS/24x24.css_OBJS) $(DeforaOS/32x32.css_OBJS) $(DeforaOS/48x48.css_OBJS)
	./icontheme.sh -c -P "$(PREFIX)" -- "DeforaOS/16x16.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "DeforaOS/24x24.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "DeforaOS/32x32.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "DeforaOS/48x48.css"

distclean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) distclean) || exit; done
	$(RM) -- $(DeforaOS/16x16.css_OBJS) $(DeforaOS/24x24.css_OBJS) $(DeforaOS/32x32.css_OBJS) $(DeforaOS/48x48.css_OBJS)
	./icontheme.sh -c -P "$(PREFIX)" -- "DeforaOS/16x16.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "DeforaOS/24x24.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "DeforaOS/32x32.css"
	./icontheme.sh -c -P "$(PREFIX)" -- "DeforaOS/48x48.css"
	$(RM) -- $(TARGETS)

install: $(TARGETS)
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) install) || exit; done
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/daportal/data/icons" -i -- "$(OBJDIR)DeforaOS/16x16.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/daportal/data/icons" -i -- "$(OBJDIR)DeforaOS/24x24.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/daportal/data/icons" -i -- "$(OBJDIR)DeforaOS/32x32.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/daportal/data/icons" -i -- "$(OBJDIR)DeforaOS/48x48.css"
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/icons
	$(INSTALL) -m 0644 DeforaOS.css $(DESTDIR)$(PREFIX)/daportal/data/icons/DeforaOS.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/icons
	$(INSTALL) -m 0644 Rodent.css $(DESTDIR)$(PREFIX)/daportal/data/icons/Rodent.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/icons
	$(INSTALL) -m 0644 Tango.css $(DESTDIR)$(PREFIX)/daportal/data/icons/Tango.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/icons
	$(INSTALL) -m 0644 generic.css $(DESTDIR)$(PREFIX)/daportal/data/icons/generic.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/icons
	$(INSTALL) -m 0644 gnome.css $(DESTDIR)$(PREFIX)/daportal/data/icons/gnome.css
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/data/icons
	$(INSTALL) -m 0644 index.php $(DESTDIR)$(PREFIX)/daportal/data/icons/index.php

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) uninstall) || exit; done
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/daportal/data/icons" -u -- "DeforaOS/16x16.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/daportal/data/icons" -u -- "DeforaOS/24x24.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/daportal/data/icons" -u -- "DeforaOS/32x32.css"
	./icontheme.sh -P "$(DESTDIR)$(PREFIX)/daportal/data/icons" -u -- "DeforaOS/48x48.css"
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/icons/DeforaOS.css
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/icons/Rodent.css
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/icons/Tango.css
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/icons/generic.css
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/icons/gnome.css
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/data/icons/index.php

.PHONY: all subdirs clean distclean install uninstall
