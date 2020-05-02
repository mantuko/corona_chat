# Corona Chat

This basic Web-App shall enable users to stream a live video from Twitch and have a chat that doesnt require users to log in at the same time.

This would work best as a real time chat with websockets but as our server does not support this we start with basic polling via ajax requests.

To keep this from beeing a permanent record the database shall be whiped automatically. Whether this happens every 24h or it it's configerable will be decided later.

## DB structure

- messages table
    - username
        - String 50 chars
    - posted
        - Timestamp
    - message
        - Text blob
        - Length shall be limited in code not the db
        - Preformated text displayed in a non monospaced font should be the easiest and safest here

## Management

There should be the possibility to censor certain words and block users based on their ip address.

## Security 

Input must be sanitized and db operations have to use prepared statements to prevent SQL injection.
