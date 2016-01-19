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
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database
	$(INSTALL) -m 0644 dummy.php $(DESTDIR)$(PREFIX)/daportal/src/database/dummy.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database
	$(INSTALL) -m 0644 pdo.php $(DESTDIR)$(PREFIX)/daportal/src/database/pdo.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database/pdo
	$(INSTALL) -m 0644 pdo/result.php $(DESTDIR)$(PREFIX)/daportal/src/database/pdo/result.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database/pdo
	$(INSTALL) -m 0644 pdo/transaction.php $(DESTDIR)$(PREFIX)/daportal/src/database/pdo/transaction.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database
	$(INSTALL) -m 0644 pgsql.php $(DESTDIR)$(PREFIX)/daportal/src/database/pgsql.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database/pgsql
	$(INSTALL) -m 0644 pgsql/result.php $(DESTDIR)$(PREFIX)/daportal/src/database/pgsql/result.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database/pgsql
	$(INSTALL) -m 0644 pgsql/transaction.php $(DESTDIR)$(PREFIX)/daportal/src/database/pgsql/transaction.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database
	$(INSTALL) -m 0644 pgsqlpool.php $(DESTDIR)$(PREFIX)/daportal/src/database/pgsqlpool.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database
	$(INSTALL) -m 0644 sqlite2.php $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite2.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite2
	$(INSTALL) -m 0644 sqlite2/result.php $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite2/result.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite2
	$(INSTALL) -m 0644 sqlite2/transaction.php $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite2/transaction.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database
	$(INSTALL) -m 0644 sqlite3.php $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite3.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite3
	$(INSTALL) -m 0644 sqlite3/result.php $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite3/result.php
	$(MKDIR) $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite3
	$(INSTALL) -m 0644 sqlite3/transaction.php $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite3/transaction.php

uninstall:
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/dummy.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/pdo.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/pdo/result.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/pdo/transaction.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/pgsql.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/pgsql/result.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/pgsql/transaction.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/pgsqlpool.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite2.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite2/result.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite2/transaction.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite3.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite3/result.php
	$(RM) -- $(DESTDIR)$(PREFIX)/daportal/src/database/sqlite3/transaction.php

.PHONY: all clean distclean install uninstall
