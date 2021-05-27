# pog-fish
Teo Näslund

## Endpoints
| Endpoint | Protokoll | Beskrivning |
| --- | --- | --- |
| home | GET | to home |
| updateLayout | POST| updateLayout from home |
| /log-in | GET | to log in (on user) |
| /register | GET | to register (create new user) |
| /users/(id) | GET | show user with id (id) |
| /secrurity-check | POST | Goes to after log in or register. Validates log in credentials |
| /edit-profile | GET | Edit view to edit profile. |
| /update-user | POST | Applies changes in form from edit-profile. |
| /profile | GET | Redirects to /users/(id) where (id) is the logged in user |
| /log-out | GET | Removes the user from session (logs the user out) |
| /post | GET | To create post view. |
| /post/(id) | GET | Like /users/(id) but with posts. |
| /posts | GET | Displays all posts |
| /create-post | POST | Create posts after form have been sent from /post |
