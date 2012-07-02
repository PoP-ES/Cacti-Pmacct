Cacti-Pmacct
============

A cacti module to display and search pmacct data.

== Install ==
* Install Plugin Architecture if cacti version < 0.8.8
* Copy pmacct dir to $CACTI_DIR/plugins
* Edit $CACTI_DIR/include/config.php file. Locate "$plugins = array();" line and add "$plugins[] = 'pmacct';" below it.
* Login as admin and go to Settings -> Pmacct to configure it.

Now you should see a new Pmacct tab.