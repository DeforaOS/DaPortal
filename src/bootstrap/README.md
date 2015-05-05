This directory is scanned for the presence of scripts upon every invocation of
any DaPortal engine. They are then automatically executed.

There is no particular ordering; it is however possible to use the
"include_once" (or "require_once") directives from PHP to ensure some scripts
are always executed first.

Scripts must have the ".php" extension. They do not need to be executable.
