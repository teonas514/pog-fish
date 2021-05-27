# pog-fish
Teo Näslund

Man kan specificera vilka kolumner man vill ha med &columns[] i alla endpoints (alla WeatherController endpoints)

Exempelvis:

	vaderlek-diamond-rhinos/all?columns[]=gust&columns[]=wind_dir

Ger alla *gust* och *wind_dir*. Har som standardvärde alla kolumner.

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
