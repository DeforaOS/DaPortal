PACKAGE	= DaPortal
VERSION	= 1.9.0
SUBDIRS	= data doc po src tests tools
RM	= rm -f
LN	= ln -f
TAR	= tar -czvf


all: subdirs

subdirs:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE)) || exit; done

clean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) clean) || exit; done

distclean:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) distclean) || exit; done

dist:
	$(RM) -r -- $(PACKAGE)-$(VERSION)
	$(LN) -s -- . $(PACKAGE)-$(VERSION)
	@$(TAR) $(PACKAGE)-$(VERSION).tar.gz -- \
		$(PACKAGE)-$(VERSION)/data/Makefile \
		$(PACKAGE)-$(VERSION)/data/index.php.in \
		$(PACKAGE)-$(VERSION)/data/project.conf \
		$(PACKAGE)-$(VERSION)/data/css/DaPortal.css \
		$(PACKAGE)-$(VERSION)/data/css/Makefile \
		$(PACKAGE)-$(VERSION)/data/css/icons.css \
		$(PACKAGE)-$(VERSION)/data/css/index.php \
		$(PACKAGE)-$(VERSION)/data/css/project.conf \
		$(PACKAGE)-$(VERSION)/data/icons/Makefile \
		$(PACKAGE)-$(VERSION)/data/icons/Rodent.css \
		$(PACKAGE)-$(VERSION)/data/icons/Tango.css \
		$(PACKAGE)-$(VERSION)/data/icons/generic.css \
		$(PACKAGE)-$(VERSION)/data/icons/gnome.css \
		$(PACKAGE)-$(VERSION)/data/icons/index.php \
		$(PACKAGE)-$(VERSION)/data/icons/project.conf \
		$(PACKAGE)-$(VERSION)/data/icons/Rodent/Makefile \
		$(PACKAGE)-$(VERSION)/data/icons/Rodent/48x48.css \
		$(PACKAGE)-$(VERSION)/data/icons/Rodent/index.php \
		$(PACKAGE)-$(VERSION)/data/icons/Rodent/scalable.css \
		$(PACKAGE)-$(VERSION)/data/icons/Rodent/project.conf \
		$(PACKAGE)-$(VERSION)/data/icons/Tango/Makefile \
		$(PACKAGE)-$(VERSION)/data/icons/Tango/16x16.css \
		$(PACKAGE)-$(VERSION)/data/icons/Tango/24x24.css \
		$(PACKAGE)-$(VERSION)/data/icons/Tango/32x32.css \
		$(PACKAGE)-$(VERSION)/data/icons/Tango/48x48.css \
		$(PACKAGE)-$(VERSION)/data/icons/Tango/index.php \
		$(PACKAGE)-$(VERSION)/data/icons/Tango/project.conf \
		$(PACKAGE)-$(VERSION)/data/icons/generic/Makefile \
		$(PACKAGE)-$(VERSION)/data/icons/generic/16x16.css \
		$(PACKAGE)-$(VERSION)/data/icons/generic/index.php \
		$(PACKAGE)-$(VERSION)/data/icons/generic/project.conf \
		$(PACKAGE)-$(VERSION)/data/icons/generic/16x16/Makefile \
		$(PACKAGE)-$(VERSION)/data/icons/generic/16x16/gtk-cancel.png \
		$(PACKAGE)-$(VERSION)/data/icons/generic/16x16/gtk-no.png \
		$(PACKAGE)-$(VERSION)/data/icons/generic/16x16/gtk-ok.png \
		$(PACKAGE)-$(VERSION)/data/icons/generic/16x16/gtk-yes.png \
		$(PACKAGE)-$(VERSION)/data/icons/generic/16x16/index.php \
		$(PACKAGE)-$(VERSION)/data/icons/generic/16x16/project.conf \
		$(PACKAGE)-$(VERSION)/data/icons/gnome/Makefile \
		$(PACKAGE)-$(VERSION)/data/icons/gnome/16x16.css \
		$(PACKAGE)-$(VERSION)/data/icons/gnome/24x24.css \
		$(PACKAGE)-$(VERSION)/data/icons/gnome/32x32.css \
		$(PACKAGE)-$(VERSION)/data/icons/gnome/48x48.css \
		$(PACKAGE)-$(VERSION)/data/icons/gnome/index.php \
		$(PACKAGE)-$(VERSION)/data/icons/gnome/project.conf \
		$(PACKAGE)-$(VERSION)/data/js/Makefile \
		$(PACKAGE)-$(VERSION)/data/js/editor.js \
		$(PACKAGE)-$(VERSION)/data/js/index.php \
		$(PACKAGE)-$(VERSION)/data/js/jquery.js \
		$(PACKAGE)-$(VERSION)/data/js/project.conf \
		$(PACKAGE)-$(VERSION)/data/themes/DaPortal.css \
		$(PACKAGE)-$(VERSION)/data/themes/DeforaOS.css \
		$(PACKAGE)-$(VERSION)/data/themes/Makefile \
		$(PACKAGE)-$(VERSION)/data/themes/index.php \
		$(PACKAGE)-$(VERSION)/data/themes/khorben.css \
		$(PACKAGE)-$(VERSION)/data/themes/project.conf \
		$(PACKAGE)-$(VERSION)/doc/Makefile \
		$(PACKAGE)-$(VERSION)/doc/daportal.conf.in \
		$(PACKAGE)-$(VERSION)/doc/daportal.conf.xml \
		$(PACKAGE)-$(VERSION)/doc/daportal.xml \
		$(PACKAGE)-$(VERSION)/doc/docbook.sh \
		$(PACKAGE)-$(VERSION)/doc/install.xml \
		$(PACKAGE)-$(VERSION)/doc/install.xsl \
		$(PACKAGE)-$(VERSION)/doc/internals.xml \
		$(PACKAGE)-$(VERSION)/doc/internals.xsl \
		$(PACKAGE)-$(VERSION)/doc/project.conf \
		$(PACKAGE)-$(VERSION)/doc/sql/Makefile \
		$(PACKAGE)-$(VERSION)/doc/sql/mysql.sql \
		$(PACKAGE)-$(VERSION)/doc/sql/postgresql.sql \
		$(PACKAGE)-$(VERSION)/doc/sql/sqlite.sql \
		$(PACKAGE)-$(VERSION)/doc/sql/project.conf \
		$(PACKAGE)-$(VERSION)/po/Makefile \
		$(PACKAGE)-$(VERSION)/po/gettext.sh \
		$(PACKAGE)-$(VERSION)/po/POTFILES \
		$(PACKAGE)-$(VERSION)/po/fr.po \
		$(PACKAGE)-$(VERSION)/po/project.conf \
		$(PACKAGE)-$(VERSION)/src/Makefile \
		$(PACKAGE)-$(VERSION)/src/daportal.php.in \
		$(PACKAGE)-$(VERSION)/src/project.conf \
		$(PACKAGE)-$(VERSION)/src/auth/Makefile \
		$(PACKAGE)-$(VERSION)/src/auth/http.php \
		$(PACKAGE)-$(VERSION)/src/auth/session.php \
		$(PACKAGE)-$(VERSION)/src/auth/unix.php \
		$(PACKAGE)-$(VERSION)/src/auth/project.conf \
		$(PACKAGE)-$(VERSION)/src/database/Makefile \
		$(PACKAGE)-$(VERSION)/src/database/dummy.php \
		$(PACKAGE)-$(VERSION)/src/database/pdo.php \
		$(PACKAGE)-$(VERSION)/src/database/pgsql.php \
		$(PACKAGE)-$(VERSION)/src/database/sqlite2.php \
		$(PACKAGE)-$(VERSION)/src/database/sqlite3.php \
		$(PACKAGE)-$(VERSION)/src/database/project.conf \
		$(PACKAGE)-$(VERSION)/src/engines/Makefile \
		$(PACKAGE)-$(VERSION)/src/engines/cli.php \
		$(PACKAGE)-$(VERSION)/src/engines/clihttp.php \
		$(PACKAGE)-$(VERSION)/src/engines/daportal.php \
		$(PACKAGE)-$(VERSION)/src/engines/dummy.php \
		$(PACKAGE)-$(VERSION)/src/engines/email.php \
		$(PACKAGE)-$(VERSION)/src/engines/gtk.php \
		$(PACKAGE)-$(VERSION)/src/engines/http.php \
		$(PACKAGE)-$(VERSION)/src/engines/httpfriendly.php \
		$(PACKAGE)-$(VERSION)/src/engines/project.conf \
		$(PACKAGE)-$(VERSION)/src/formats/Makefile \
		$(PACKAGE)-$(VERSION)/src/formats/atom.php \
		$(PACKAGE)-$(VERSION)/src/formats/csv.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf.php \
		$(PACKAGE)-$(VERSION)/src/formats/html.php \
		$(PACKAGE)-$(VERSION)/src/formats/html5.php \
		$(PACKAGE)-$(VERSION)/src/formats/pdf.php \
		$(PACKAGE)-$(VERSION)/src/formats/plain.php \
		$(PACKAGE)-$(VERSION)/src/formats/xhtml1.php \
		$(PACKAGE)-$(VERSION)/src/formats/xhtml11.php \
		$(PACKAGE)-$(VERSION)/src/formats/xml.php \
		$(PACKAGE)-$(VERSION)/src/formats/project.conf \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/Makefile \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/fpdf.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/courier.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/courierb.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/courierbi.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/courieri.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/helvetica.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/helveticab.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/helveticabi.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/helveticai.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/symbol.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/times.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/timesb.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/timesbi.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/timesi.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/font/zapfdingbats.php \
		$(PACKAGE)-$(VERSION)/src/formats/fpdf/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/admin/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/admin/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/admin/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/article/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/article/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/article/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/blog/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/blog/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/blog/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/browser/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/browser/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/browser/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/content/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/content/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/content/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/download/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/download/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/download/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/news/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/news/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/news/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/project/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/project/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/project/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/project/scm/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/project/scm/cvs.php \
		$(PACKAGE)-$(VERSION)/src/modules/project/scm/git.php \
		$(PACKAGE)-$(VERSION)/src/modules/project/scm/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/search/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/search/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/search/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/top/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/top/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/top/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/user/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/user/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/user/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/wiki/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/wiki/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/wiki/project.conf \
		$(PACKAGE)-$(VERSION)/src/system/Makefile \
		$(PACKAGE)-$(VERSION)/src/system/auth.php \
		$(PACKAGE)-$(VERSION)/src/system/common.php \
		$(PACKAGE)-$(VERSION)/src/system/compat.php \
		$(PACKAGE)-$(VERSION)/src/system/config.php \
		$(PACKAGE)-$(VERSION)/src/system/content.php \
		$(PACKAGE)-$(VERSION)/src/system/database.php \
		$(PACKAGE)-$(VERSION)/src/system/engine.php \
		$(PACKAGE)-$(VERSION)/src/system/format.php \
		$(PACKAGE)-$(VERSION)/src/system/html.php \
		$(PACKAGE)-$(VERSION)/src/system/locale.php \
		$(PACKAGE)-$(VERSION)/src/system/mail.php \
		$(PACKAGE)-$(VERSION)/src/system/mime.php \
		$(PACKAGE)-$(VERSION)/src/system/module.php \
		$(PACKAGE)-$(VERSION)/src/system/page.php \
		$(PACKAGE)-$(VERSION)/src/system/request.php \
		$(PACKAGE)-$(VERSION)/src/system/template.php \
		$(PACKAGE)-$(VERSION)/src/system/user.php \
		$(PACKAGE)-$(VERSION)/src/system/project.conf \
		$(PACKAGE)-$(VERSION)/src/system/locale/Makefile \
		$(PACKAGE)-$(VERSION)/src/system/locale/gettext.php \
		$(PACKAGE)-$(VERSION)/src/system/locale/project.conf \
		$(PACKAGE)-$(VERSION)/src/templates/Makefile \
		$(PACKAGE)-$(VERSION)/src/templates/DeforaOS.php \
		$(PACKAGE)-$(VERSION)/src/templates/basic.php \
		$(PACKAGE)-$(VERSION)/src/templates/desktop.php \
		$(PACKAGE)-$(VERSION)/src/templates/khorben.php \
		$(PACKAGE)-$(VERSION)/src/templates/project.conf \
		$(PACKAGE)-$(VERSION)/tests/Makefile \
		$(PACKAGE)-$(VERSION)/tests/database.sh \
		$(PACKAGE)-$(VERSION)/tests/phplint.sh \
		$(PACKAGE)-$(VERSION)/tests/project.conf \
		$(PACKAGE)-$(VERSION)/tools/Makefile \
		$(PACKAGE)-$(VERSION)/tools/daportal.in \
		$(PACKAGE)-$(VERSION)/tools/deploy.sh \
		$(PACKAGE)-$(VERSION)/tools/subst.sh \
		$(PACKAGE)-$(VERSION)/tools/project.conf \
		$(PACKAGE)-$(VERSION)/AUTHORS \
		$(PACKAGE)-$(VERSION)/BUGS \
		$(PACKAGE)-$(VERSION)/COPYING \
		$(PACKAGE)-$(VERSION)/INSTALL \
		$(PACKAGE)-$(VERSION)/Makefile \
		$(PACKAGE)-$(VERSION)/README.md \
		$(PACKAGE)-$(VERSION)/config.sh \
		$(PACKAGE)-$(VERSION)/project.conf
	$(RM) -- $(PACKAGE)-$(VERSION)

install:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) install) || exit; done

uninstall:
	@for i in $(SUBDIRS); do (cd "$$i" && $(MAKE) uninstall) || exit; done

.PHONY: all subdirs clean distclean dist install uninstall
