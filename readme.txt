-----------------------------------------------------------------------
|  Guestbook module for papaya CMS 5                                  |
|  Version: mk-1.4 (21.05.2010)                                       |
|  Authors: Alexander Nichau (Original), Martin Kelm (Updates)        |
-----------------------------------------------------------------------

This module offers a guestbook view and the corresponding admin page module
for use in papaya CMS 5 (www.papaya-cms.com).

This module was tested with php5 and mysql4/5. Although papayaCMS offers native
support for postre sql, I had no opportunity to test it on such a database or
on any other rdbms. Sorry, but let me know if it's working on any other system.

Update mk-1.4 (21.05.2010) [3 years revision]:
- XSL templates for papaya CMS demo template set
- Fixed input checks
- Fixed a lot notice errors
- Improved coding style
- Some minor output improvements
- Improved output escaping
- Removed PHP4 support

Update mk-1.3a (29.05.2008):
- Fixed pagination offset

Update mk-1.3 (12.05.2008):
- New class structure, added output class
- New guestbook entries teaser box
- Show / hide edit formular by configuration
- Added language dependend content captions
- Use surfer data if logged in to get user name and email
- Some smaller optimizations, commentations and so forth

Update mk-1.2 (16.04.2008):
- papaya CMS 5.0 rc1 compatibility
- Resized glyphs and module icons
- Merged templates to one file
- Corrected content page template
- Corrected page module name in modules xml

Update mk-1.1a (26.05.2007):
- Added setting for maximal text length

Update mk-1.1 (21.05.2007):
- Added spamcheck (base_spamcheck) and some other entry checks
- Added compatibility for multi-langual contents
- Using new email object for administrator notification
- Code and output optimized
- New glyphs and module icons

Installation:
- copy the folders papaya, papaya-lib and its contents to your
  papaya CMS directory
- place the contents of the folder files/templates to your
  papaya-data/templates/<your template folder>/xslt/ folder
- log into papaya CMS and activate this module by clicking on "Modules"
  and "Scan"

Now you should be able to use the guestbook.
Don´t forget to build a papaya view for this module.

-----------
| License |
-----------

This module is offered under GNU General Public Licence
(GPL). The detailed license text can be found in gpl.txt
