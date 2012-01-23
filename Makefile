PACKAGE	= DaPortal
VERSION	= 1.9.0
SUBDIRS	= css data doc html icons js images modules src system templates themes
RM	?= rm -f
LN	?= ln -f
TAR	?= tar -czvf


all: subdirs

subdirs:
	@for i in $(SUBDIRS); do (cd $$i && $(MAKE)) || exit; done

clean:
	@for i in $(SUBDIRS); do (cd $$i && $(MAKE) clean) || exit; done

distclean:
	@for i in $(SUBDIRS); do (cd $$i && $(MAKE) distclean) || exit; done

dist:
	$(RM) -r -- $(PACKAGE)-$(VERSION)
	$(LN) -s -- . $(PACKAGE)-$(VERSION)
	@$(TAR) $(PACKAGE)-$(VERSION).tar.gz -- \
		$(PACKAGE)-$(VERSION)/css/Makefile \
		$(PACKAGE)-$(VERSION)/css/debug.css \
		$(PACKAGE)-$(VERSION)/css/explorer.css \
		$(PACKAGE)-$(VERSION)/css/icons.css \
		$(PACKAGE)-$(VERSION)/css/syntax.css \
		$(PACKAGE)-$(VERSION)/css/project.conf \
		$(PACKAGE)-$(VERSION)/data/Makefile \
		$(PACKAGE)-$(VERSION)/data/index.php \
		$(PACKAGE)-$(VERSION)/data/project.conf \
		$(PACKAGE)-$(VERSION)/data/css/DaPortal.css \
		$(PACKAGE)-$(VERSION)/data/css/Makefile \
		$(PACKAGE)-$(VERSION)/data/css/index.php \
		$(PACKAGE)-$(VERSION)/data/css/project.conf \
		$(PACKAGE)-$(VERSION)/data/js/Makefile \
		$(PACKAGE)-$(VERSION)/data/js/index.php \
		$(PACKAGE)-$(VERSION)/data/js/project.conf \
		$(PACKAGE)-$(VERSION)/data/themes/DaPortal.css \
		$(PACKAGE)-$(VERSION)/data/themes/DeforaOS.css \
		$(PACKAGE)-$(VERSION)/data/themes/Makefile \
		$(PACKAGE)-$(VERSION)/data/themes/index.php \
		$(PACKAGE)-$(VERSION)/data/themes/project.conf \
		$(PACKAGE)-$(VERSION)/doc/Makefile \
		$(PACKAGE)-$(VERSION)/doc/project.conf \
		$(PACKAGE)-$(VERSION)/doc/sql/Makefile \
		$(PACKAGE)-$(VERSION)/doc/sql/mysql.sql \
		$(PACKAGE)-$(VERSION)/doc/sql/postgresql.sql \
		$(PACKAGE)-$(VERSION)/doc/sql/sqlite.sql \
		$(PACKAGE)-$(VERSION)/doc/sql/project.conf \
		$(PACKAGE)-$(VERSION)/html/Makefile \
		$(PACKAGE)-$(VERSION)/html/404.html \
		$(PACKAGE)-$(VERSION)/html/doctype.html \
		$(PACKAGE)-$(VERSION)/html/index.php \
		$(PACKAGE)-$(VERSION)/html/install.html \
		$(PACKAGE)-$(VERSION)/html/project.conf \
		$(PACKAGE)-$(VERSION)/icons/Makefile \
		$(PACKAGE)-$(VERSION)/icons/index.php \
		$(PACKAGE)-$(VERSION)/icons/project.conf \
		$(PACKAGE)-$(VERSION)/icons/16x16/Makefile \
		$(PACKAGE)-$(VERSION)/icons/16x16/admin.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/article.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/blog.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/bookmark.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/ca.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/caclient.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/caserver.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/category.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/comment.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/contact.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/content.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/default.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/disabled.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/down.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/enabled.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/host.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/index.php \
		$(PACKAGE)-$(VERSION)/icons/16x16/justify-center.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/justify-fill.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/justify-left.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/justify-right.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/logout.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/news.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/pki.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/project.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/reply.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/rss.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/save.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/search.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/top.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/translate.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/up.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/user.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/users.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/webmail.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/wiki.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/project.conf \
		$(PACKAGE)-$(VERSION)/icons/16x16/mime/Makefile \
		$(PACKAGE)-$(VERSION)/icons/16x16/mime/default.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/mime/folder.png \
		$(PACKAGE)-$(VERSION)/icons/16x16/mime/index.php \
		$(PACKAGE)-$(VERSION)/icons/16x16/mime/project.conf \
		$(PACKAGE)-$(VERSION)/icons/48x48/Makefile \
		$(PACKAGE)-$(VERSION)/icons/48x48/admin.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/appearance.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/article.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/blog.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/bookmark.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/browser.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/bug-assigned.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/bug-closed.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/bug-fixed.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/bug-new.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/bug.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/category.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/comment.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/contact.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/content.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/cvs-added.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/cvs-modified.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/cvs-removed.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/download.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/explorer.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/files.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/host.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/logout.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/menu.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/news.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/papadam.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/pki.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/project.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/search.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/top.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/translate.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/user.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/users.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/webmail.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/wiki.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/project.conf \
		$(PACKAGE)-$(VERSION)/icons/48x48/mime/Makefile \
		$(PACKAGE)-$(VERSION)/icons/48x48/mime/default.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/mime/folder.png \
		$(PACKAGE)-$(VERSION)/icons/48x48/mime/index.php \
		$(PACKAGE)-$(VERSION)/icons/48x48/mime/project.conf \
		$(PACKAGE)-$(VERSION)/icons/gnome/Makefile \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16.css \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32.css \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48.css \
		$(PACKAGE)-$(VERSION)/icons/gnome/gnome.php \
		$(PACKAGE)-$(VERSION)/icons/gnome/icons.css \
		$(PACKAGE)-$(VERSION)/icons/gnome/index.php \
		$(PACKAGE)-$(VERSION)/icons/gnome/project.conf \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/Makefile \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/add.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/admin.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/align_center.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/align_justify.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/align_left.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/align_right.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/back.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/bold.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/bug.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/bullet.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/copy.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/cut.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/delete.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/disabled.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/download.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/edit.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/enabled.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/enum.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/forward.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/home.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/hrule.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/index.php \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/indent.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/insert-image.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/italic.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/language.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/listing_details.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/listing_list.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/listing_thumbnails.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/new.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/new_directory.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/parent_directory.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/paste.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/redo.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/refresh.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/remove.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/reset.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/select-all.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/strikethrough.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/superscript.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/underline.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/undo.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/unindent.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/upload_file.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/16x16/project.conf \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32/Makefile \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32/bug.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32/ca.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32/comment.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32/download.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32/host.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32/index.php \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32/language.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32/project.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32/settings.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32/stats.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32/users.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/32x32/project.conf \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/Makefile \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/addressbook.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/admin.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/appearance.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/article.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/blog.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/bookmark.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/browser.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/bug.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/category.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/comment.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/contact.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/content.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/directory.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/download.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/error.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/home.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/info.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/logout.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/news.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/package.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/pki.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/probe.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/project.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/search.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/top.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/user.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/users.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/warning.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/webmail.png \
		$(PACKAGE)-$(VERSION)/icons/gnome/48x48/project.conf \
		$(PACKAGE)-$(VERSION)/js/Makefile \
		$(PACKAGE)-$(VERSION)/js/editor.js \
		$(PACKAGE)-$(VERSION)/js/explorer.js \
		$(PACKAGE)-$(VERSION)/js/project.conf \
		$(PACKAGE)-$(VERSION)/images/Makefile \
		$(PACKAGE)-$(VERSION)/images/ccbyncsa.png \
		$(PACKAGE)-$(VERSION)/images/css.png \
		$(PACKAGE)-$(VERSION)/images/index.php \
		$(PACKAGE)-$(VERSION)/images/rss.png \
		$(PACKAGE)-$(VERSION)/images/valid-xhtml11.png \
		$(PACKAGE)-$(VERSION)/images/vim_small.png \
		$(PACKAGE)-$(VERSION)/images/xhtml.png \
		$(PACKAGE)-$(VERSION)/images/project.conf \
		$(PACKAGE)-$(VERSION)/modules/Makefile \
		$(PACKAGE)-$(VERSION)/modules/index.php \
		$(PACKAGE)-$(VERSION)/modules/project.conf \
		$(PACKAGE)-$(VERSION)/modules/admin/Makefile \
		$(PACKAGE)-$(VERSION)/modules/admin/default.tpl \
		$(PACKAGE)-$(VERSION)/modules/admin/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/admin/index.php \
		$(PACKAGE)-$(VERSION)/modules/admin/module.php \
		$(PACKAGE)-$(VERSION)/modules/admin/module_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/admin/project.conf \
		$(PACKAGE)-$(VERSION)/modules/article/Makefile \
		$(PACKAGE)-$(VERSION)/modules/article/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/article/display.tpl \
		$(PACKAGE)-$(VERSION)/modules/article/index.php \
		$(PACKAGE)-$(VERSION)/modules/article/module.php \
		$(PACKAGE)-$(VERSION)/modules/article/posted.tpl \
		$(PACKAGE)-$(VERSION)/modules/article/update.tpl \
		$(PACKAGE)-$(VERSION)/modules/article/project.conf \
		$(PACKAGE)-$(VERSION)/modules/blog/Makefile \
		$(PACKAGE)-$(VERSION)/modules/blog/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/blog/index.php \
		$(PACKAGE)-$(VERSION)/modules/blog/module.php \
		$(PACKAGE)-$(VERSION)/modules/blog/post_display.tpl \
		$(PACKAGE)-$(VERSION)/modules/blog/post_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/blog/project.conf \
		$(PACKAGE)-$(VERSION)/modules/bookmark/Makefile \
		$(PACKAGE)-$(VERSION)/modules/bookmark/default.tpl \
		$(PACKAGE)-$(VERSION)/modules/bookmark/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/bookmark/display.tpl \
		$(PACKAGE)-$(VERSION)/modules/bookmark/index.php \
		$(PACKAGE)-$(VERSION)/modules/bookmark/module.php \
		$(PACKAGE)-$(VERSION)/modules/bookmark/update.tpl \
		$(PACKAGE)-$(VERSION)/modules/bookmark/project.conf \
		$(PACKAGE)-$(VERSION)/modules/browser/Makefile \
		$(PACKAGE)-$(VERSION)/modules/browser/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/browser/display.tpl \
		$(PACKAGE)-$(VERSION)/modules/browser/index.php \
		$(PACKAGE)-$(VERSION)/modules/browser/module.php \
		$(PACKAGE)-$(VERSION)/modules/browser/project.conf \
		$(PACKAGE)-$(VERSION)/modules/category/Makefile \
		$(PACKAGE)-$(VERSION)/modules/category/choose.tpl \
		$(PACKAGE)-$(VERSION)/modules/category/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/category/display.tpl \
		$(PACKAGE)-$(VERSION)/modules/category/index.php \
		$(PACKAGE)-$(VERSION)/modules/category/module.php \
		$(PACKAGE)-$(VERSION)/modules/category/update.tpl \
		$(PACKAGE)-$(VERSION)/modules/category/project.conf \
		$(PACKAGE)-$(VERSION)/modules/comment/Makefile \
		$(PACKAGE)-$(VERSION)/modules/comment/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/comment/display.tpl \
		$(PACKAGE)-$(VERSION)/modules/comment/index.php \
		$(PACKAGE)-$(VERSION)/modules/comment/module.php \
		$(PACKAGE)-$(VERSION)/modules/comment/update.tpl \
		$(PACKAGE)-$(VERSION)/modules/comment/project.conf \
		$(PACKAGE)-$(VERSION)/modules/content/Makefile \
		$(PACKAGE)-$(VERSION)/modules/content/default.tpl \
		$(PACKAGE)-$(VERSION)/modules/content/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/content/index.php \
		$(PACKAGE)-$(VERSION)/modules/content/module.php \
		$(PACKAGE)-$(VERSION)/modules/content/update.tpl \
		$(PACKAGE)-$(VERSION)/modules/content/project.conf \
		$(PACKAGE)-$(VERSION)/modules/download/Makefile \
		$(PACKAGE)-$(VERSION)/modules/download/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/download/directory_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/download/file_display.tpl \
		$(PACKAGE)-$(VERSION)/modules/download/file_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/download/index.php \
		$(PACKAGE)-$(VERSION)/modules/download/lang.fr.php \
		$(PACKAGE)-$(VERSION)/modules/download/module.php \
		$(PACKAGE)-$(VERSION)/modules/download/project.conf \
		$(PACKAGE)-$(VERSION)/modules/explorer/Makefile \
		$(PACKAGE)-$(VERSION)/modules/explorer/bottom.tpl \
		$(PACKAGE)-$(VERSION)/modules/explorer/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/explorer/entry.tpl \
		$(PACKAGE)-$(VERSION)/modules/explorer/header.tpl \
		$(PACKAGE)-$(VERSION)/modules/explorer/index.php \
		$(PACKAGE)-$(VERSION)/modules/explorer/module.php \
		$(PACKAGE)-$(VERSION)/modules/explorer/toolbar.tpl \
		$(PACKAGE)-$(VERSION)/modules/explorer/top.tpl \
		$(PACKAGE)-$(VERSION)/modules/explorer/project.conf \
		$(PACKAGE)-$(VERSION)/modules/menu/Makefile \
		$(PACKAGE)-$(VERSION)/modules/menu/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/menu/index.php \
		$(PACKAGE)-$(VERSION)/modules/menu/module.php \
		$(PACKAGE)-$(VERSION)/modules/menu/project.conf \
		$(PACKAGE)-$(VERSION)/modules/news/Makefile \
		$(PACKAGE)-$(VERSION)/modules/news/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/news/index.php \
		$(PACKAGE)-$(VERSION)/modules/news/module.php \
		$(PACKAGE)-$(VERSION)/modules/news/news_display.tpl \
		$(PACKAGE)-$(VERSION)/modules/news/news_posted.tpl \
		$(PACKAGE)-$(VERSION)/modules/news/news_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/news/project.conf \
		$(PACKAGE)-$(VERSION)/modules/papadam/Makefile \
		$(PACKAGE)-$(VERSION)/modules/papadam/ca_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/papadam/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/papadam/download.tpl \
		$(PACKAGE)-$(VERSION)/modules/papadam/index.php \
		$(PACKAGE)-$(VERSION)/modules/papadam/module.php \
		$(PACKAGE)-$(VERSION)/modules/papadam/project.conf \
		$(PACKAGE)-$(VERSION)/modules/pki/Makefile \
		$(PACKAGE)-$(VERSION)/modules/pki/ca_display.tpl \
		$(PACKAGE)-$(VERSION)/modules/pki/ca_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/pki/caclient_display.tpl \
		$(PACKAGE)-$(VERSION)/modules/pki/caclient_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/pki/caserver_display.tpl \
		$(PACKAGE)-$(VERSION)/modules/pki/caserver_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/pki/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/pki/index.php \
		$(PACKAGE)-$(VERSION)/modules/pki/module.php \
		$(PACKAGE)-$(VERSION)/modules/pki/openssl.cnf.tpl \
		$(PACKAGE)-$(VERSION)/modules/pki/project.conf \
		$(PACKAGE)-$(VERSION)/modules/probe/Makefile \
		$(PACKAGE)-$(VERSION)/modules/probe/bottom.tpl \
		$(PACKAGE)-$(VERSION)/modules/probe/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/probe/graph.tpl \
		$(PACKAGE)-$(VERSION)/modules/probe/host_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/probe/index.php \
		$(PACKAGE)-$(VERSION)/modules/probe/lang.php \
		$(PACKAGE)-$(VERSION)/modules/probe/lang.fr.php \
		$(PACKAGE)-$(VERSION)/modules/probe/module.php \
		$(PACKAGE)-$(VERSION)/modules/probe/top.tpl \
		$(PACKAGE)-$(VERSION)/modules/probe/project.conf \
		$(PACKAGE)-$(VERSION)/modules/project/Makefile \
		$(PACKAGE)-$(VERSION)/modules/project/browse.php \
		$(PACKAGE)-$(VERSION)/modules/project/bug_display.tpl \
		$(PACKAGE)-$(VERSION)/modules/project/bug_list_filter.tpl \
		$(PACKAGE)-$(VERSION)/modules/project/bug_posted.tpl \
		$(PACKAGE)-$(VERSION)/modules/project/bug_reply_display.tpl \
		$(PACKAGE)-$(VERSION)/modules/project/bug_reply_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/project/bug_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/project/default.tpl \
		$(PACKAGE)-$(VERSION)/modules/project/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/project/download_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/project/index.php \
		$(PACKAGE)-$(VERSION)/modules/project/lang.php \
		$(PACKAGE)-$(VERSION)/modules/project/lang.de.php \
		$(PACKAGE)-$(VERSION)/modules/project/lang.fr.php \
		$(PACKAGE)-$(VERSION)/modules/project/module.php \
		$(PACKAGE)-$(VERSION)/modules/project/project_display.tpl \
		$(PACKAGE)-$(VERSION)/modules/project/project_submitted.tpl \
		$(PACKAGE)-$(VERSION)/modules/project/project_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/project/screenshot_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/project/syntax.php \
		$(PACKAGE)-$(VERSION)/modules/project/toolbar.tpl \
		$(PACKAGE)-$(VERSION)/modules/project/project.conf \
		$(PACKAGE)-$(VERSION)/modules/search/Makefile \
		$(PACKAGE)-$(VERSION)/modules/search/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/search/index.php \
		$(PACKAGE)-$(VERSION)/modules/search/module.php \
		$(PACKAGE)-$(VERSION)/modules/search/search.tpl \
		$(PACKAGE)-$(VERSION)/modules/search/search_advanced.tpl \
		$(PACKAGE)-$(VERSION)/modules/search/search_bottom.tpl \
		$(PACKAGE)-$(VERSION)/modules/search/search_entry.tpl \
		$(PACKAGE)-$(VERSION)/modules/search/search_top.tpl \
		$(PACKAGE)-$(VERSION)/modules/search/project.conf \
		$(PACKAGE)-$(VERSION)/modules/top/Makefile \
		$(PACKAGE)-$(VERSION)/modules/top/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/top/index.php \
		$(PACKAGE)-$(VERSION)/modules/top/module.php \
		$(PACKAGE)-$(VERSION)/modules/top/update.tpl \
		$(PACKAGE)-$(VERSION)/modules/top/project.conf \
		$(PACKAGE)-$(VERSION)/modules/translate/Makefile \
		$(PACKAGE)-$(VERSION)/modules/translate/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/translate/index.php \
		$(PACKAGE)-$(VERSION)/modules/translate/module.php \
		$(PACKAGE)-$(VERSION)/modules/translate/update.tpl \
		$(PACKAGE)-$(VERSION)/modules/translate/project.conf \
		$(PACKAGE)-$(VERSION)/modules/user/Makefile \
		$(PACKAGE)-$(VERSION)/modules/user/appearance.tpl \
		$(PACKAGE)-$(VERSION)/modules/user/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/user/index.php \
		$(PACKAGE)-$(VERSION)/modules/user/lang.de.php \
		$(PACKAGE)-$(VERSION)/modules/user/lang.fr.php \
		$(PACKAGE)-$(VERSION)/modules/user/module.php \
		$(PACKAGE)-$(VERSION)/modules/user/user_confirm.tpl \
		$(PACKAGE)-$(VERSION)/modules/user/user_login.tpl \
		$(PACKAGE)-$(VERSION)/modules/user/user_logout.tpl \
		$(PACKAGE)-$(VERSION)/modules/user/user_pending.tpl \
		$(PACKAGE)-$(VERSION)/modules/user/user_register.tpl \
		$(PACKAGE)-$(VERSION)/modules/user/user_update.tpl \
		$(PACKAGE)-$(VERSION)/modules/user/project.conf \
		$(PACKAGE)-$(VERSION)/modules/webmail/Makefile \
		$(PACKAGE)-$(VERSION)/modules/webmail/bottom.tpl \
		$(PACKAGE)-$(VERSION)/modules/webmail/default.tpl \
		$(PACKAGE)-$(VERSION)/modules/webmail/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/webmail/drafts.png \
		$(PACKAGE)-$(VERSION)/modules/webmail/drafts_16.png \
		$(PACKAGE)-$(VERSION)/modules/webmail/folders_bottom.tpl \
		$(PACKAGE)-$(VERSION)/modules/webmail/folders_top.tpl \
		$(PACKAGE)-$(VERSION)/modules/webmail/inbox.png \
		$(PACKAGE)-$(VERSION)/modules/webmail/inbox_16.png \
		$(PACKAGE)-$(VERSION)/modules/webmail/index.php \
		$(PACKAGE)-$(VERSION)/modules/webmail/login.tpl \
		$(PACKAGE)-$(VERSION)/modules/webmail/logout.tpl \
		$(PACKAGE)-$(VERSION)/modules/webmail/mail_read.png \
		$(PACKAGE)-$(VERSION)/modules/webmail/mail_replied.png \
		$(PACKAGE)-$(VERSION)/modules/webmail/mail_unread.png \
		$(PACKAGE)-$(VERSION)/modules/webmail/module.php \
		$(PACKAGE)-$(VERSION)/modules/webmail/outbox.png \
		$(PACKAGE)-$(VERSION)/modules/webmail/outbox_16.png \
		$(PACKAGE)-$(VERSION)/modules/webmail/read.tpl \
		$(PACKAGE)-$(VERSION)/modules/webmail/trash.png \
		$(PACKAGE)-$(VERSION)/modules/webmail/tree.js \
		$(PACKAGE)-$(VERSION)/modules/webmail/tree.tpl \
		$(PACKAGE)-$(VERSION)/modules/webmail/project.conf \
		$(PACKAGE)-$(VERSION)/modules/wiki/Makefile \
		$(PACKAGE)-$(VERSION)/modules/wiki/default.tpl \
		$(PACKAGE)-$(VERSION)/modules/wiki/desktop.php \
		$(PACKAGE)-$(VERSION)/modules/wiki/display.tpl \
		$(PACKAGE)-$(VERSION)/modules/wiki/index.php \
		$(PACKAGE)-$(VERSION)/modules/wiki/lang.fr.php \
		$(PACKAGE)-$(VERSION)/modules/wiki/module.php \
		$(PACKAGE)-$(VERSION)/modules/wiki/update.tpl \
		$(PACKAGE)-$(VERSION)/modules/wiki/project.conf \
		$(PACKAGE)-$(VERSION)/src/Makefile \
		$(PACKAGE)-$(VERSION)/src/daportal.php \
		$(PACKAGE)-$(VERSION)/src/project.conf \
		$(PACKAGE)-$(VERSION)/src/auth/Makefile \
		$(PACKAGE)-$(VERSION)/src/auth/http.php \
		$(PACKAGE)-$(VERSION)/src/auth/session.php \
		$(PACKAGE)-$(VERSION)/src/auth/unix.php \
		$(PACKAGE)-$(VERSION)/src/auth/project.conf \
		$(PACKAGE)-$(VERSION)/src/database/Makefile \
		$(PACKAGE)-$(VERSION)/src/database/pdo.php \
		$(PACKAGE)-$(VERSION)/src/database/sqlite2.php \
		$(PACKAGE)-$(VERSION)/src/database/sqlite3.php \
		$(PACKAGE)-$(VERSION)/src/database/project.conf \
		$(PACKAGE)-$(VERSION)/src/engines/Makefile \
		$(PACKAGE)-$(VERSION)/src/engines/cli.php \
		$(PACKAGE)-$(VERSION)/src/engines/daportal.php \
		$(PACKAGE)-$(VERSION)/src/engines/gtk.php \
		$(PACKAGE)-$(VERSION)/src/engines/http.php \
		$(PACKAGE)-$(VERSION)/src/engines/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/search/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/search/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/search/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/top/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/top/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/top/project.conf \
		$(PACKAGE)-$(VERSION)/src/modules/user/Makefile \
		$(PACKAGE)-$(VERSION)/src/modules/user/module.php \
		$(PACKAGE)-$(VERSION)/src/modules/user/project.conf \
		$(PACKAGE)-$(VERSION)/src/system/Makefile \
		$(PACKAGE)-$(VERSION)/src/system/auth.php \
		$(PACKAGE)-$(VERSION)/src/system/config.php \
		$(PACKAGE)-$(VERSION)/src/system/database.php \
		$(PACKAGE)-$(VERSION)/src/system/engine.php \
		$(PACKAGE)-$(VERSION)/src/system/module.php \
		$(PACKAGE)-$(VERSION)/src/system/page.php \
		$(PACKAGE)-$(VERSION)/src/system/request.php \
		$(PACKAGE)-$(VERSION)/src/system/template.php \
		$(PACKAGE)-$(VERSION)/src/system/project.conf \
		$(PACKAGE)-$(VERSION)/src/templates/Makefile \
		$(PACKAGE)-$(VERSION)/src/templates/basic.php \
		$(PACKAGE)-$(VERSION)/src/templates/desktop.php \
		$(PACKAGE)-$(VERSION)/src/templates/project.conf \
		$(PACKAGE)-$(VERSION)/system/Makefile \
		$(PACKAGE)-$(VERSION)/system/config.php \
		$(PACKAGE)-$(VERSION)/system/config.tpl \
		$(PACKAGE)-$(VERSION)/system/content.php \
		$(PACKAGE)-$(VERSION)/system/debug.php \
		$(PACKAGE)-$(VERSION)/system/html.php \
		$(PACKAGE)-$(VERSION)/system/icon.php \
		$(PACKAGE)-$(VERSION)/system/index.php \
		$(PACKAGE)-$(VERSION)/system/lang.php \
		$(PACKAGE)-$(VERSION)/system/lang.de.php \
		$(PACKAGE)-$(VERSION)/system/lang.fr.php \
		$(PACKAGE)-$(VERSION)/system/mail.php \
		$(PACKAGE)-$(VERSION)/system/mime.php \
		$(PACKAGE)-$(VERSION)/system/module.php \
		$(PACKAGE)-$(VERSION)/system/rss.php \
		$(PACKAGE)-$(VERSION)/system/sql.php \
		$(PACKAGE)-$(VERSION)/system/sql.mysql.php \
		$(PACKAGE)-$(VERSION)/system/sql.pgsql.php \
		$(PACKAGE)-$(VERSION)/system/sql.sqlite.php \
		$(PACKAGE)-$(VERSION)/system/user.php \
		$(PACKAGE)-$(VERSION)/system/xml.php \
		$(PACKAGE)-$(VERSION)/system/project.conf \
		$(PACKAGE)-$(VERSION)/templates/Makefile \
		$(PACKAGE)-$(VERSION)/templates/DaPortal.tpl \
		$(PACKAGE)-$(VERSION)/templates/DeforaOS.tpl \
		$(PACKAGE)-$(VERSION)/templates/DeforaOS-default.tpl \
		$(PACKAGE)-$(VERSION)/templates/DeforaOS-menu.tpl \
		$(PACKAGE)-$(VERSION)/templates/DeforaOS.de.tpl \
		$(PACKAGE)-$(VERSION)/templates/DeforaOS.fr.tpl \
		$(PACKAGE)-$(VERSION)/templates/index.php \
		$(PACKAGE)-$(VERSION)/templates/papadmin.tpl \
		$(PACKAGE)-$(VERSION)/templates/private.tpl \
		$(PACKAGE)-$(VERSION)/templates/Probe.tpl \
		$(PACKAGE)-$(VERSION)/templates/project.conf \
		$(PACKAGE)-$(VERSION)/themes/Makefile \
		$(PACKAGE)-$(VERSION)/themes/DaPortal.css \
		$(PACKAGE)-$(VERSION)/themes/DeforaOS.css \
		$(PACKAGE)-$(VERSION)/themes/DeforaOS-bbl.png \
		$(PACKAGE)-$(VERSION)/themes/DeforaOS-bbr.png \
		$(PACKAGE)-$(VERSION)/themes/DeforaOS-btl.png \
		$(PACKAGE)-$(VERSION)/themes/DeforaOS-btr.png \
		$(PACKAGE)-$(VERSION)/themes/DeforaOS-menusep.png \
		$(PACKAGE)-$(VERSION)/themes/DeforaOS-rbl.png \
		$(PACKAGE)-$(VERSION)/themes/DeforaOS-rbr.png \
		$(PACKAGE)-$(VERSION)/themes/DeforaOS-rtl.png \
		$(PACKAGE)-$(VERSION)/themes/DeforaOS-rtr.png \
		$(PACKAGE)-$(VERSION)/themes/Fine.css \
		$(PACKAGE)-$(VERSION)/themes/Fine-DeforaOS.png \
		$(PACKAGE)-$(VERSION)/themes/khorben.css \
		$(PACKAGE)-$(VERSION)/themes/khorben-coldfire.jpg \
		$(PACKAGE)-$(VERSION)/themes/Native.css \
		$(PACKAGE)-$(VERSION)/themes/Probe.css \
		$(PACKAGE)-$(VERSION)/themes/index.php \
		$(PACKAGE)-$(VERSION)/themes/papadam.css \
		$(PACKAGE)-$(VERSION)/themes/papadam-bg.png \
		$(PACKAGE)-$(VERSION)/themes/papadmin.css \
		$(PACKAGE)-$(VERSION)/themes/project.conf \
		$(PACKAGE)-$(VERSION)/AUTHORS \
		$(PACKAGE)-$(VERSION)/BUGS \
		$(PACKAGE)-$(VERSION)/COPYING \
		$(PACKAGE)-$(VERSION)/INSTALL \
		$(PACKAGE)-$(VERSION)/Makefile \
		$(PACKAGE)-$(VERSION)/README \
		$(PACKAGE)-$(VERSION)/config.php \
		$(PACKAGE)-$(VERSION)/daportal.conf \
		$(PACKAGE)-$(VERSION)/engine.php \
		$(PACKAGE)-$(VERSION)/index.php \
		$(PACKAGE)-$(VERSION)/install.php \
		$(PACKAGE)-$(VERSION)/project.conf
	$(RM) -- $(PACKAGE)-$(VERSION)

install:
	@for i in $(SUBDIRS); do (cd $$i && $(MAKE) install) || exit; done

uninstall:
	@for i in $(SUBDIRS); do (cd $$i && $(MAKE) uninstall) || exit; done

.PHONY: all subdirs clean distclean dist install uninstall
