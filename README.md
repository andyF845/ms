MemoStore
===
This is small demo project of http short message storage system.
This server allows to store, create, edit and delete short messages (255 chars max).
By default, server stores last 10 messages and show last 5 messages.
DO NOT USE IT IN YOUR PROJECTS! (Just in case, i don't think someone would :) )
Also see https://github.com/andyF845/amf

=Usage=
  0. Setup and configure your server environment (apache >= 2.2, php >= 5.3, mySQL >= 5.x)
  1. Download this project source code.
  2. Download https://github.com/andyF845/amf (place ./amf/* in the same directory)
  3. Done! You may now run /index.php for normal use or /testMemoStore.php to run test.

=Files=

- index.php - Project main file
- MemoStore.php - Project main class, contains business logic
- README.md - This file
- TestMemoStore.php - functional test of this project (unit tests see https://github.com/andyF845/amf)
- ./css/
  - main.css - Styles for this project
- ./memo/
  - .htaccess - Apache configuration file
  - config.php - Project configuration file
  - item.htm - Template for item display
  - list.htm - Template for items list display
  - new_item.htm - New item dialog
  - page.htm - Page template
  - ./msg/ - Server response templates
    - action_unknown.htm
    - delete_fail.htm
    - delete_ok.htm
    - item_not_found.htm
    - list_empty.htm
    - update_fail.htm
    - update_ok.htm