SUBDIRS	= content response
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

install: all
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" install; \
		else $(MAKE) install; fi) || exit; done
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 auth.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/auth.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/auth
	$(INSTALL) -m 0644 auth/credentials.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/auth/credentials.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 autoload.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/autoload.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 common.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/common.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 compat.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/compat.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 config.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/config.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/config
	$(INSTALL) -m 0644 config/section.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/config/section.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 content.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/content.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 database.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/database.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/database
	$(INSTALL) -m 0644 database/result.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/database/result.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/database
	$(INSTALL) -m 0644 database/transaction.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/database/transaction.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 date.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/date.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 engine.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/engine.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 format.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/format.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/format
	$(INSTALL) -m 0644 format/elements.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/format/elements.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 group.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/group.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 html.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/html.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 html5.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/html5.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 locale.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/locale.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/locale
	$(INSTALL) -m 0644 locale/gettext.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/locale/gettext.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 mail.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/mail.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 mime.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/mime.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 module.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/module.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 mutator.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/mutator.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 observable.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/observable.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 observer.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/observer.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 page.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/page.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system/page
	$(INSTALL) -m 0644 page/element.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/page/element.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 profile.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/profile.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 request.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/request.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 response.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/response.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 template.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/template.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/share/daportal/src/system
	$(INSTALL) -m 0644 user.php $(DESTDIR)$(PREFIX)/share/daportal/src/system/user.php

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && \
		if [ -n "$(OBJDIR)" ]; then \
		$(MAKE) OBJDIR="$(OBJDIR)$$i/" uninstall; \
		else $(MAKE) uninstall; fi) || exit; done
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/auth.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/auth/credentials.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/autoload.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/common.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/compat.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/config.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/config/section.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/content.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/database.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/database/result.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/database/transaction.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/date.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/engine.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/format.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/format/elements.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/group.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/html.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/html5.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/locale.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/locale/gettext.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/mail.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/mime.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/module.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/mutator.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/observable.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/observer.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/page.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/page/element.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/profile.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/request.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/response.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/template.php
	$(RM) -- $(DESTDIR)$(PREFIX)/share/daportal/src/system/user.php

.PHONY: all subdirs clean distclean install uninstall
