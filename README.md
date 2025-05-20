The project uses cloudinary.com to handle image uploads, so the following .env entries are required:

- CLOUDINARY_CLOUD_NAME
- CLOUDINARY_API_KEY
- CLOUDINARY_API_SECRET
- RECEIVER_EMAIL

You can obtain all the cloudinary related entries by creating an account on cloudinary.com, and grabbing your API keys from the dashboard. The RECEIVER_EMAIL entry is supposed to be the email which should receive potential inquiry messages. Additionally, all email and database related entries should be properly configured

- MAIL_MAILER
- MAIL_HOST
- MAIL_PORT
- MAIL_USERNAME
- MAIL_PASSWORD
- MAIL_ENCRYPTION
- MAIL_FROM_ADDRESS
- DB_CONNECTION=pgsql
- DB_HOST=127.0.0.1
- DB_PORT=5432
- DB_DATABASE=
- DB_USERNAME=
- DB_PASSWORD=
