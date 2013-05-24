-----------------------------------------------------------------------
|  Guestbook module for papaya CMS 5                                  |
|  Version: 1.1 (21.05.2007)                       								    |
|  Authors: Alexander Nichau, Martin Kelm                             |
-----------------------------------------------------------------------

This module offers a guestbook view and the corresponding admin page module 
for use in papaya CMS 5 (www.papaya-cms.com).

This module was tested with php5 and mysql4/5. Although papayaCMS offers native 
support for postre sql, I had no opportunity to test it on such a database or
on any other rdbms. Sorry, but let me know if it's working on any other system.

Improvements in 1.1 (21.05.2007):
- Spamcheck (base_spamcheck) and some other entry checks
- Compatibility for multi-langual contents
- Using new email object for administrator notification
- Code and output optimized

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

